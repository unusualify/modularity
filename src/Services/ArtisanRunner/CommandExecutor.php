<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner;

use Illuminate\Console\Application;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\BridgedQuestionHelper;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\InteractiveQuestionBroker;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\StreamedConsoleOutput;
use Unusualify\Modularous\Services\ArtisanRunner\Support\AllowlistMatcher;

final class CommandExecutor
{
    public function __construct(
        private readonly ConsoleKernel $kernel,
        private readonly AllowlistMatcher $allowlistMatcher,
    ) {}

    /**
     * Whether this command should spawn a real `php artisan` CLI process.
     */
    public function shouldExecuteViaSubprocess(string $commandName): bool
    {
        $mode = mb_strtolower((string) modularousConfig('artisan_runner.execution', 'auto'));

        return match ($mode) {
            'subprocess' => true,
            'in_process' => false,
            default => $this->allowlistMatcher->matches(
                $commandName,
                array_values(array_filter(
                    (array) modularousConfig('artisan_runner.subprocess_commands', [])
                )),
            ),
        };
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @param callable(string $event, array<string, mixed> $payload): void $emit
     */
    public function execute(
        string $commandName,
        array $arguments,
        array $options,
        string $runId,
        callable $emit,
        ?int $timeout = null,
        ?int $maxOutputBytes = null,
        ?int $promptTimeout = null,
    ): int {
        $timeout ??= (int) modularousConfig('artisan_runner.timeout', 120);
        $maxOutputBytes ??= (int) modularousConfig('artisan_runner.max_output_bytes', 1_048_576);
        $promptTimeout ??= (int) modularousConfig('artisan_runner.prompt_timeout', 60);

        if ($this->shouldExecuteViaSubprocess($commandName)) {
            if ($this->resolvePhpCliBinary() !== null) {
                return $this->executeViaSubprocess(
                    $commandName,
                    $arguments,
                    $options,
                    $emit,
                    $timeout,
                    $maxOutputBytes,
                );
            }

            if ($this->requiresSubprocessExecution()) {
                throw new ArtisanRunnerException($this->missingPhpCliBinaryMessage());
            }

            $emit('output', [
                'chunk' => '[warning] ' . $this->missingPhpCliBinaryMessage() . " Falling back to in-process execution.\n",
            ]);
        }

        return $this->executeInProcess(
            $commandName,
            $arguments,
            $options,
            $runId,
            $emit,
            $timeout,
            $maxOutputBytes,
            $promptTimeout,
        );
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @param callable(string $event, array<string, mixed> $payload): void $emit
     */
    private function executeInProcess(
        string $commandName,
        array $arguments,
        array $options,
        string $runId,
        callable $emit,
        int $timeout,
        int $maxOutputBytes,
        int $promptTimeout,
    ): int {
        $previousLimit = ini_get('max_execution_time');
        if ($timeout > 0) {
            set_time_limit($timeout);
        }

        $broker = new InteractiveQuestionBroker($promptTimeout);
        $streamed = new StreamedConsoleOutput($emit, $maxOutputBytes);
        $helper = new BridgedQuestionHelper($runId, $broker, $emit);

        $parameters = $this->buildParameters($commandName, $arguments, $options);
        $input = new ArrayInput($parameters);
        $input->setInteractive(true);

        // $this->confirm()/ask()/choice() use SymfonyStyle::$questionHelper, not HelperSet.
        $output = new OutputStyle($input, $streamed);
        $this->installQuestionHelper($helper, $output);

        try {
            return $this->kernel->handle($input, $output);
        } finally {
            if ($previousLimit !== false) {
                set_time_limit((int) $previousLimit);
            }
        }
    }

    /**
     * Spawn `php artisan {command}` so PHP_SAPI is cli — true route/config cache parity.
     *
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @param callable(string $event, array<string, mixed> $payload): void $emit
     */
    private function executeViaSubprocess(
        string $commandName,
        array $arguments,
        array $options,
        callable $emit,
        int $timeout,
        int $maxOutputBytes,
    ): int {
        $parameters = $this->buildParameters($commandName, $arguments, $options);
        $commandLine = $this->buildSubprocessCommandLine($parameters);

        $process = new Process(
            $commandLine,
            base_path(),
            ['APP_RUNNING_IN_CONSOLE' => '1'],
            null,
            $timeout > 0 ? (float) $timeout : null,
        );

        $bytesWritten = 0;

        $process->run(function (string $type, string $buffer) use ($emit, $maxOutputBytes, &$bytesWritten): void {
            $length = mb_strlen($buffer);
            if ($bytesWritten + $length > $maxOutputBytes) {
                $remaining = max(0, $maxOutputBytes - $bytesWritten);
                if ($remaining > 0) {
                    $emit('output', ['chunk' => mb_substr($buffer, 0, $remaining)]);
                    $bytesWritten += $remaining;
                }

                throw new ArtisanRunnerException('Command output exceeded max_output_bytes limit.');
            }

            $bytesWritten += $length;
            $emit('output', ['chunk' => $buffer]);
        });

        return $process->getExitCode() ?? 1;
    }

    /**
     * @param array<string, mixed> $parameters ArrayInput-style parameters from buildParameters()
     * @return list<string>
     */
    public function buildSubprocessCommandLine(array $parameters): array
    {
        $php = $this->resolvePhpCliBinary();
        if ($php === null) {
            throw new ArtisanRunnerException($this->missingPhpCliBinaryMessage());
        }

        $artisan = base_path('artisan');
        if (! is_file($artisan)) {
            throw new ArtisanRunnerException('Unable to locate artisan for subprocess execution.');
        }

        $commandName = (string) ($parameters['command'] ?? '');
        if ($commandName === '') {
            throw new ArtisanRunnerException('Missing command name for subprocess execution.');
        }

        $line = [$php, $artisan, $commandName];

        foreach ($parameters as $key => $value) {
            if ($key === 'command') {
                continue;
            }

            if (is_string($key) && str_starts_with($key, '--')) {
                if ($value === true) {
                    $line[] = $key;

                    continue;
                }

                if ($value === false || $value === null) {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $item) {
                        $line[] = $key . '=' . (string) $item;
                    }

                    continue;
                }

                $line[] = $key . '=' . (string) $value;

                continue;
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    $line[] = (string) $item;
                }

                continue;
            }

            $line[] = (string) $value;
        }

        // Subprocess cannot use BridgedQuestionHelper; avoid hanging on prompts.
        $line[] = '--no-interaction';

        return $line;
    }

    /**
     * Resolve a usable PHP CLI binary for `php artisan` subprocesses.
     *
     * Preference: explicit config, then Symfony finder, then FPM-friendly siblings
     * of PHP_BINARY / PHP_BINDIR (php-fpm often has CLI at ../bin/php).
     */
    public function resolvePhpCliBinary(): ?string
    {
        $configured = trim((string) modularousConfig('artisan_runner.php_binary', ''));
        if ($configured !== '') {
            if ($this->isNonCliSapiBinary($configured)) {
                throw new ArtisanRunnerException(
                    "Configured PHP binary [{$configured}] is not a CLI executable (php-fpm/cgi). Set MODULAROUS_ARTISAN_RUNNER_PHP_BINARY to the php CLI path."
                );
            }

            if ($this->isUsablePhpCliBinary($configured)) {
                return $configured;
            }

            throw new ArtisanRunnerException(
                "Unable to use configured PHP CLI binary [{$configured}]. Set MODULAROUS_ARTISAN_RUNNER_PHP_BINARY to an executable php CLI path."
            );
        }

        foreach ($this->phpCliBinaryCandidates() as $candidate) {
            if ($this->isNonCliSapiBinary($candidate)) {
                continue;
            }

            if ($this->isUsablePhpCliBinary($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function buildParameters(string $commandName, array $arguments, array $options): array
    {
        $command = null;
        foreach (Artisan::all() as $name => $cmd) {
            if ($name === $commandName) {
                $command = $cmd;

                break;
            }
        }

        if ($command === null) {
            throw new ArtisanRunnerException("Unknown command [{$commandName}].");
        }

        $definition = $command->getDefinition();
        $parameters = ['command' => $commandName];

        foreach ($definition->getArguments() as $argument) {
            $name = $argument->getName();
            if ($name === 'command') {
                continue;
            }

            if (! array_key_exists($name, $arguments)) {
                if ($argument->isRequired()) {
                    throw new ArtisanRunnerException("Missing required argument [{$name}].");
                }

                continue;
            }

            $value = $this->normalizeArgumentValue($argument, $arguments[$name]);
            if ($value === null || $value === '') {
                if ($argument->isRequired()) {
                    throw new ArtisanRunnerException("Missing required argument [{$name}].");
                }

                continue;
            }

            $parameters[$name] = $value;
        }

        foreach ($definition->getOptions() as $option) {
            $name = $option->getName();
            if (in_array($name, ['help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'env'], true)) {
                continue;
            }

            if (! array_key_exists($name, $options)) {
                continue;
            }

            $raw = $options[$name];
            $key = '--' . $name;

            if (! $option->acceptValue()) {
                if (filter_var($raw, FILTER_VALIDATE_BOOLEAN)) {
                    $parameters[$key] = true;
                }

                continue;
            }

            $value = $this->normalizeOptionValue($option, $raw);
            if ($value === null || $value === '') {
                if ($option->isValueRequired()) {
                    throw new ArtisanRunnerException("Missing required option [--{$name}].");
                }

                continue;
            }

            $parameters[$key] = $value;
        }

        return $parameters;
    }

    /**
     * Wire BridgedQuestionHelper into both SymfonyStyle (confirm/ask/choice) and HelperSet
     * ($this->getHelper('question')).
     */
    private function installQuestionHelper(BridgedQuestionHelper $helper, OutputStyle $output): void
    {
        $property = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $property->setAccessible(true);
        $property->setValue($output, $helper);

        $artisan = $this->resolveArtisanApplication();
        $helperSet = $artisan->getHelperSet();
        if (! $helperSet instanceof HelperSet) {
            $helperSet = new HelperSet;
            $artisan->setHelperSet($helperSet);
        }

        $helperSet->set($helper);
    }

    private function resolveArtisanApplication(): Application
    {
        $method = new ReflectionMethod($this->kernel, 'getArtisan');
        $method->setAccessible(true);

        /** @var Application $artisan */
        $artisan = $method->invoke($this->kernel);

        return $artisan;
    }

    private function normalizeArgumentValue(InputArgument $argument, mixed $raw): mixed
    {
        if ($argument->isArray()) {
            return $this->splitList($raw);
        }

        if (is_array($raw)) {
            return implode(' ', array_map('strval', $raw));
        }

        return is_scalar($raw) || $raw === null ? $raw : (string) $raw;
    }

    private function normalizeOptionValue(InputOption $option, mixed $raw): mixed
    {
        if ($option->isArray()) {
            return $this->splitList($raw);
        }

        if (is_array($raw)) {
            return implode(',', array_map('strval', $raw));
        }

        return is_scalar($raw) || $raw === null ? $raw : (string) $raw;
    }

    /**
     * @return list<string>
     */
    private function splitList(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map(
                static fn ($item) => trim((string) $item),
                $raw
            ), static fn (string $item) => $item !== ''));
        }

        $text = trim((string) $raw);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[\r\n,]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $item) => $item !== ''));
    }

    private function requiresSubprocessExecution(): bool
    {
        return mb_strtolower((string) modularousConfig('artisan_runner.execution', 'auto')) === 'subprocess';
    }

    private function missingPhpCliBinaryMessage(): string
    {
        return 'Unable to locate PHP CLI binary for subprocess execution. Set MODULAROUS_ARTISAN_RUNNER_PHP_BINARY to the php CLI path (for example /usr/bin/php8.3).';
    }

    /**
     * @return list<string>
     */
    private function phpCliBinaryCandidates(): array
    {
        $candidates = [];

        $phpPath = getenv('PHP_PATH');
        if (is_string($phpPath) && $phpPath !== '') {
            $candidates[] = $phpPath;
        }

        $found = (new PhpExecutableFinder)->find(false);
        if (is_string($found) && $found !== '') {
            $candidates[] = $found;
        }

        $phpBindir = rtrim((string) \PHP_BINDIR, DIRECTORY_SEPARATOR);
        if ($phpBindir !== '') {
            $candidates[] = $phpBindir . DIRECTORY_SEPARATOR . 'php';
        }

        $phpBinary = \PHP_BINARY;
        if (is_string($phpBinary) && $phpBinary !== '') {
            $directory = dirname($phpBinary);
            $basename = basename($phpBinary);
            $cliBasename = preg_replace('/php-fpm/i', 'php', $basename);
            $versioned = 'php' . \PHP_MAJOR_VERSION . '.' . \PHP_MINOR_VERSION;
            $major = 'php' . \PHP_MAJOR_VERSION;

            $candidates[] = $phpBinary;
            $candidates[] = $directory . DIRECTORY_SEPARATOR . 'php';
            if (is_string($cliBasename) && $cliBasename !== '') {
                $candidates[] = $directory . DIRECTORY_SEPARATOR . $cliBasename;
            }

            $parentBin = dirname($directory) . DIRECTORY_SEPARATOR . 'bin';
            $candidates[] = $parentBin . DIRECTORY_SEPARATOR . 'php';
            $candidates[] = $parentBin . DIRECTORY_SEPARATOR . $versioned;
            $candidates[] = $parentBin . DIRECTORY_SEPARATOR . $major;
            if (is_string($cliBasename) && $cliBasename !== '') {
                $candidates[] = $parentBin . DIRECTORY_SEPARATOR . $cliBasename;
            }
        }

        $versioned = 'php' . \PHP_MAJOR_VERSION . '.' . \PHP_MINOR_VERSION;
        foreach (['/usr/bin', '/usr/local/bin'] as $dir) {
            $candidates[] = $dir . '/php';
            $candidates[] = $dir . '/' . $versioned;
            $candidates[] = $dir . '/php' . \PHP_MAJOR_VERSION;
        }

        $unique = [];
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '' || isset($unique[$candidate])) {
                continue;
            }
            $unique[$candidate] = $candidate;
        }

        return array_values($unique);
    }

    private function isNonCliSapiBinary(string $path): bool
    {
        $basename = mb_strtolower(basename($path));

        return str_contains($basename, 'php-fpm')
            || str_contains($basename, 'cgi-fcgi')
            || str_contains($basename, 'php-cgi');
    }

    private function isUsablePhpCliBinary(string $path): bool
    {
        if ($path === '' || @is_dir($path)) {
            return false;
        }

        return @is_file($path) && @is_executable($path);
    }
}

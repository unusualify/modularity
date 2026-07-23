<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
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
        $mode = strtolower((string) modularousConfig('artisan_runner.execution', 'auto'));

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
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $options
     * @param  callable(string $event, array<string, mixed> $payload): void  $emit
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
            return $this->executeViaSubprocess(
                $commandName,
                $arguments,
                $options,
                $emit,
                $timeout,
                $maxOutputBytes,
            );
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
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $options
     * @param  callable(string $event, array<string, mixed> $payload): void  $emit
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
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $options
     * @param  callable(string $event, array<string, mixed> $payload): void  $emit
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
            $length = strlen($buffer);
            if ($bytesWritten + $length > $maxOutputBytes) {
                $remaining = max(0, $maxOutputBytes - $bytesWritten);
                if ($remaining > 0) {
                    $emit('output', ['chunk' => substr($buffer, 0, $remaining)]);
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
     * @param  array<string, mixed>  $parameters  ArrayInput-style parameters from buildParameters()
     * @return list<string>
     */
    public function buildSubprocessCommandLine(array $parameters): array
    {
        $php = (new PhpExecutableFinder)->find(false);
        if ($php === false || $php === '') {
            throw new ArtisanRunnerException('Unable to locate PHP CLI binary for subprocess execution.');
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
                        $line[] = $key.'='.(string) $item;
                    }

                    continue;
                }

                $line[] = $key.'='.(string) $value;

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
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function buildParameters(string $commandName, array $arguments, array $options): array
    {
        $command = null;
        foreach (\Illuminate\Support\Facades\Artisan::all() as $name => $cmd) {
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
            $key = '--'.$name;

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

    private function resolveArtisanApplication(): \Illuminate\Console\Application
    {
        $method = new ReflectionMethod($this->kernel, 'getArtisan');
        $method->setAccessible(true);

        /** @var \Illuminate\Console\Application $artisan */
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
}

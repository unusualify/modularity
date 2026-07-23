<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Streaming;

use Symfony\Component\Console\Output\Output;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;

/**
 * Console output that forwards writes to an SSE emit callback.
 *
 * @phpstan-type EmitCallable callable(string $event, array<string, mixed> $payload): void
 */
final class StreamedConsoleOutput extends Output
{
    /** @var EmitCallable */
    private $emit;

    private int $bytesWritten = 0;

    /**
     * @param  EmitCallable  $emit
     */
    public function __construct(
        callable $emit,
        private readonly int $maxOutputBytes = 1_048_576,
        int $verbosity = self::VERBOSITY_NORMAL,
        bool $decorated = false,
    ) {
        parent::__construct($verbosity, $decorated);
        $this->emit = $emit;
    }

    protected function doWrite(string $message, bool $newline): void
    {
        $chunk = $newline ? $message . PHP_EOL : $message;
        $length = strlen($chunk);

        if ($this->bytesWritten + $length > $this->maxOutputBytes) {
            $remaining = max(0, $this->maxOutputBytes - $this->bytesWritten);
            if ($remaining > 0) {
                $chunk = substr($chunk, 0, $remaining);
                ($this->emit)('output', ['chunk' => $chunk]);
                $this->bytesWritten += $remaining;
            }

            throw new ArtisanRunnerException('Command output exceeded max_output_bytes limit.');
        }

        $this->bytesWritten += $length;
        ($this->emit)('output', ['chunk' => $chunk]);
    }

    public function bytesWritten(): int
    {
        return $this->bytesWritten;
    }
}

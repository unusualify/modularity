<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ArtisanRunnerInterface
{
    /**
     * @return list<array{name: string, description: string}>
     */
    public function catalogForUser(Authenticatable $user): array;

    /**
     * @return array{
     *     name: string,
     *     description: string,
     *     arguments: list<array<string, mixed>>,
     *     options: list<array<string, mixed>>
     * }
     */
    public function definitionForUser(Authenticatable $user, string $command): array;

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @param callable(string $event, array<string, mixed> $payload): void $emit
     */
    public function run(
        Authenticatable $user,
        string $command,
        array $arguments,
        array $options,
        string $runId,
        callable $emit,
    ): int;

    public function answerPrompt(string $runId, string $promptId, mixed $answer): void;

    public function userCanAccess(Authenticatable $user): bool;

    public function assertCommandAllowed(Authenticatable $user, string $command): void;

    /**
     * Session-authenticated panel API URLs (placeholders __NAME__ / __RUN_ID__).
     *
     * @return array{commands: string, definition: string, run: string, answer: string}
     */
    public function panelEndpoints(): array;

    /**
     * @return array{commands: string, definition: string, run: string, answer: string}
     */
    public function emptyPanelEndpoints(): array;
}

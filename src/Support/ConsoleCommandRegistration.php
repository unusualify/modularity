<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

/**
 * When Modularous console commands are registered.
 *
 * ArtisanRunner lists and runs commands via Artisan::all(). Under PHP-FPM,
 * runningInConsole() is false, so the console-only gate would hide package
 * and module commands from POST /artisan-runner/runs.
 */
final class ConsoleCommandRegistration
{
    public static function shouldRegister(bool $runningInConsole, ?string $requestPath): bool
    {
        if ($runningInConsole) {
            return true;
        }

        return self::isArtisanRunnerRequest($requestPath);
    }

    public static function isArtisanRunnerRequest(?string $requestPath): bool
    {
        if ($requestPath === null || $requestPath === '') {
            return false;
        }

        return str_contains($requestPath, 'artisan-runner');
    }
}

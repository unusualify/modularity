<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Process-level memo for {@see database_exists()} so a failed PDO connect
 * (often 10s+) is not repeated by every provider in the same request.
 */
final class DatabaseExists
{
    private static ?bool $cached = null;

    public static function check(): bool
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        try {
            DB::connection()->getPDO();
            self::$cached = true;
        } catch (Throwable) {
            self::$cached = false;
        }

        return self::$cached;
    }

    public static function flush(): void
    {
        self::$cached = null;
    }
}

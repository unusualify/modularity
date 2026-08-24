<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Process-level memo for {@see database_exists()} so a failed PDO connect
 * (often 10s+) is not repeated by every provider in the same request.
 *
 * Memoization is disabled during unit tests: ParaTest workers reuse the same
 * PHP process across many TestCase apps, and Mockery DB stubs must not poison
 * later tests via a sticky {@see $cached} false.
 */
final class DatabaseExists
{
    private static ?bool $cached = null;

    public static function check(): bool
    {
        if (self::shouldMemoize() && self::$cached !== null) {
            return self::$cached;
        }

        try {
            DB::connection()->getPDO();
            $exists = true;
        } catch (Throwable) {
            $exists = false;
        }

        if (self::shouldMemoize()) {
            self::$cached = $exists;
        }

        return $exists;
    }

    public static function flush(): void
    {
        self::$cached = null;
    }

    private static function shouldMemoize(): bool
    {
        return ! function_exists('app')
            || ! app()->bound('env')
            || ! app()->runningUnitTests();
    }
}

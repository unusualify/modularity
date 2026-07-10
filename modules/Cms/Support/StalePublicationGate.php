<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Carbon\Carbon;

/**
 * Evaluates whether URL-keyed stale HTML may be served for the current request time.
 */
final class StalePublicationGate
{
    /**
     * @param array<string, mixed> $meta
     */
    public static function isVisible(array $meta, ?Carbon $now = null): bool
    {
        $now ??= Carbon::now();

        if (($meta['published'] ?? false) !== true) {
            return false;
        }

        $profile = (string) ($meta['visibility_profile'] ?? StalePublicationMeta::PROFILE_STANDARD);

        return $profile === StalePublicationMeta::PROFILE_SINGULAR
            ? self::isSingularVisible($meta, $now)
            : self::isStandardVisible($meta, $now);
    }

    /**
     * @param array<string, mixed> $meta
     */
    protected static function isSingularVisible(array $meta, Carbon $now): bool
    {
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        $start = self::parseDate($meta['publish_start_date'] ?? null);
        if ($start !== null && $start->gt($startOfDay)) {
            return false;
        }

        $end = self::parseDate($meta['publish_end_date'] ?? null);
        if ($end !== null && $end->lt($endOfDay)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $meta
     */
    protected static function isStandardVisible(array $meta, Carbon $now): bool
    {
        $start = self::parseStartDateTime($meta['publish_start_date'] ?? null);
        if ($start !== null && $start->gt($now)) {
            return false;
        }

        $end = self::parseEndDateTime($meta['publish_end_date'] ?? null);
        if ($end !== null && $end->lt($now)) {
            return false;
        }

        return true;
    }

    protected static function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function parseStartDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $parsed = Carbon::parse((string) $value);

            if (mb_strlen(trim((string) $value)) <= 10) {
                return $parsed->startOfDay();
            }

            return $parsed;
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function parseEndDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $parsed = Carbon::parse((string) $value);

            if (mb_strlen(trim((string) $value)) <= 10) {
                return $parsed->endOfDay();
            }

            return $parsed;
        } catch (\Throwable) {
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

use Illuminate\Support\Facades\Session;

/**
 * Single queue for non-blocking warning strings shown after redirect (Inertia {@see flash.warnings}).
 * Call {@see merge()} from any controller; messages stack for the next request then {@see pull} in middleware.
 */
final class ModularousFlashWarnings
{
    public const SESSION_KEY = 'modularous.flash_warnings';

    /**
     * @param list<string>|string $messages
     */
    public static function merge(array|string $messages): void
    {
        $incoming = self::normalizeIncoming($messages);
        if ($incoming === []) {
            return;
        }

        $current = Session::get(self::SESSION_KEY, []);
        $currentList = is_array($current) ? self::normalizeIncoming($current) : [];

        Session::flash(self::SESSION_KEY, array_values(array_unique([...$currentList, ...$incoming])));
    }

    /**
     * @param mixed $messages
     * @return list<string>
     */
    private static function normalizeIncoming(array|string $messages): array
    {
        if (is_string($messages)) {
            $t = trim($messages);

            return $t === '' ? [] : [$t];
        }

        $out = [];
        foreach ($messages as $m) {
            if ($m === null || $m === '') {
                continue;
            }
            $s = trim(is_scalar($m) ? (string) $m : '');
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return $out;
    }
}

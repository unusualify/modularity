<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Http\Request;

/**
 * Replaces stale CSRF tokens baked into URL-keyed presentation HTML at serve time.
 *
 * Matches Laravel defaults: shell meta tag and {@see @csrf} / csrf_field() hidden inputs.
 */
final class CachedPresentationHtmlCsrfRefresher
{
    public static function refresh(string $html, ?Request $request = null): string
    {
        if ($html === '') {
            return $html;
        }

        if (! self::containsCsrfMarkers($html)) {
            return $html;
        }

        self::ensureSessionStarted($request);

        $token = csrf_token();
        if (! is_string($token) || $token === '') {
            return $html;
        }

        $escaped = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = (string) preg_replace(
            '/<meta\s+name=(["\'])csrf-token\1\s+content=(["\'])[^"\']*\2\s*\/?>/i',
            '<meta name="csrf-token" content="' . $escaped . '">',
            $html,
            1,
        );

        return (string) preg_replace(
            '/<input\s+type=(["\'])hidden\1\s+name=(["\'])_token\2\s+value=(["\'])[^"\']*\3(?:\s+autocomplete=(["\'])off\4)?\s*\/?>/i',
            '<input type="hidden" name="_token" value="' . $escaped . '" autocomplete="off">',
            $html,
        );
    }

    private static function containsCsrfMarkers(string $html): bool
    {
        return str_contains($html, 'csrf-token')
            || str_contains($html, 'name="_token"')
            || str_contains($html, "name='_token'");
    }

    private static function ensureSessionStarted(?Request $request): void
    {
        if ($request !== null) {
            if (! $request->hasSession()) {
                return;
            }

            if (! $request->session()->isStarted()) {
                $request->session()->start();
            }

            return;
        }

        if (function_exists('session') && session()->isStarted()) {
            return;
        }

        if (function_exists('session')) {
            session()->start();
        }
    }
}

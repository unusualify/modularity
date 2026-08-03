<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimezoneMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $this->resolveTimezone($request);
        if ($timezone !== null) {
            date_default_timezone_set($timezone);
            session()->put('modularous_timezone', $timezone);
        }

        return $next($request);
    }

    /**
     * Resolve a valid IANA timezone from header, cookie, input, or session.
     */
    protected function resolveTimezone(Request $request): ?string
    {
        $candidates = [
            session('modularous_timezone'),
            session('timezone'),
            $request->header('X-Timezone'),
            $request->input('_timezone'),
            // JS-set cookie is not Laravel-encrypted; read raw $_COOKIE.
            isset($_COOKIE['timezone']) ? urldecode((string) $_COOKIE['timezone']) : null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }

            if (in_array($candidate, timezone_identifiers_list(), true)) {
                return $candidate;
            }
        }

        dd($candidates);
        return null;
    }
}

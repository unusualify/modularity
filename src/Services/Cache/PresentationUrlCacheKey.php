<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Illuminate\Http\Request;

/**
 * Normalizes URL path + allowlisted query parameters for URL-keyed presentation cache.
 */
final class PresentationUrlCacheKey
{
    public const STRATEGY_PATH_ONLY = 'path_only';

    public const STRATEGY_PATH_AND_QUERY = 'path_and_query';

    public const STRATEGY_PATH_AND_QUERY_ALLOWLIST = 'path_and_query_allowlist';

    /**
     * @param array<string, mixed> $query
     */
    public static function normalizeQuerySuffix(array $query, array $allowlist, string $strategy): string
    {
        if ($strategy === self::STRATEGY_PATH_ONLY) {
            return '';
        }

        $keys = $strategy === self::STRATEGY_PATH_AND_QUERY
            ? array_keys($query)
            : $allowlist;

        $filtered = [];

        foreach ($keys as $key) {
            if (! is_string($key) || $key === '' || ! array_key_exists($key, $query)) {
                continue;
            }

            $value = $query[$key];
            if (is_array($value)) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if ($key === 'page' && ($value === '1' || $value === '0')) {
                continue;
            }

            $filtered[$key] = $value;
        }

        if ($filtered === []) {
            return '';
        }

        ksort($filtered);

        return http_build_query($filtered);
    }

    /**
     * @param array<string, mixed> $query
     */
    public static function hasDisallowedQueryParams(array $query, array $allowlist, string $strategy): bool
    {
        if ($strategy === self::STRATEGY_PATH_ONLY || $strategy === self::STRATEGY_PATH_AND_QUERY) {
            return false;
        }

        $allowed = array_fill_keys($allowlist, true);

        foreach (self::nonEmptyQueryParams($query) as $key => $value) {
            if (! isset($allowed[$key])) {
                return true;
            }
        }

        return false;
    }

    public static function shouldBypassRequest(Request $request, array $allowlist, string $strategy): bool
    {
        return self::hasDisallowedQueryParams($request->query->all(), $allowlist, $strategy);
    }

    public static function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string
    {
        $path = '/' . trim($normalizedPath, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        $querySuffix = trim($querySuffix);

        if ($querySuffix === '') {
            return $path;
        }

        return $path . '?' . ltrim($querySuffix, '?');
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, string>
     */
    protected static function nonEmptyQueryParams(array $query): array
    {
        $params = [];

        foreach ($query as $key => $value) {
            if (! is_string($key) || $key === '' || is_array($value)) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if ($key === 'page' && ($value === '1' || $value === '0')) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }
}

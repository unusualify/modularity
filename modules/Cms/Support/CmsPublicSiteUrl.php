<?php

namespace Modules\Cms\Support;

use Illuminate\Support\Facades\URL;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;

/**
 * Absolute URLs for the public CMS front (admin-safe): uses the same host resolution as
 * {@see CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain()} plus {@see modularousConfig('cms_routing.canonical_host')},
 * so panel requests do not rewrite permalinks to the admin {@code APP_URL} host.
 */
final class CmsPublicSiteUrl
{
    /**
     * Hostname for public page links (no scheme, no path). Empty string means caller should fall back to {@see URL()}.
     */
    public static function resolvePublicSiteHost(): string
    {
        $explicit = trim((string) modularousConfig('cms_routing.public_front_route_domain', ''));
        if ($explicit !== '') {
            return $explicit;
        }

        $canonical = trim((string) modularousConfig('cms_routing.canonical_host', ''));
        if ($canonical !== '') {
            return $canonical;
        }

        $fromRoute = CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain();
        if (is_string($fromRoute) && $fromRoute !== '') {
            return $fromRoute;
        }

        return '';
    }

    /**
     * Scheme for absolute public links (defaults from {@code config('app.url')}).
     */
    public static function resolvePublicSiteScheme(): string
    {
        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        if ($scheme === 'https' || $scheme === 'http') {
            return $scheme;
        }

        return 'https';
    }

    /**
     * Root URL (scheme + host, no trailing slash) for public CMS link generation.
     */
    public static function resolvePublicSiteRootUrl(): string
    {
        $host = self::resolvePublicSiteHost();
        if ($host !== '') {
            return self::resolvePublicSiteScheme() . '://' . $host;
        }

        return rtrim((string) config('app.url', 'http://localhost'), '/');
    }

    /**
     * Force Laravel's URL generator to the public front base for the duration of {@code $callback}.
     *
     * Used when rendering {@see CmsPublicPresentationItemCache} HTML during admin warmup or cache miss
     * so {@code asset()}, {@code url()}, and {@code route()} do not inherit the admin request host.
     *
     * @template T
     *
     * @param callable(): T $callback
     * @return T
     */
    public static function runWithForcedPublicRootUrl(callable $callback): mixed
    {
        $rootUrl = self::resolvePublicSiteRootUrl();
        $scheme = parse_url($rootUrl, PHP_URL_SCHEME);
        $scheme = is_string($scheme) && $scheme !== '' ? $scheme : self::resolvePublicSiteScheme();

        URL::forceRootUrl($rootUrl);
        URL::forceScheme($scheme);

        try {
            return $callback();
        } finally {
            URL::forceRootUrl(null);
            URL::forceScheme(null);
        }
    }

    /**
     * Absolute URL for a path that {@see CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath()} would produce.
     */
    public static function absoluteUrlForPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            $normalized = '/';
        } else {
            $normalized = $path[0] === '/' ? $path : '/' . $path;
        }

        $host = self::resolvePublicSiteHost();
        if ($host === '') {
            return URL::to($normalized);
        }

        $scheme = self::resolvePublicSiteScheme();

        return $scheme . '://' . $host . $normalized;
    }
}

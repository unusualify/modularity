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

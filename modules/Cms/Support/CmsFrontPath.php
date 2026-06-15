<?php

namespace Modules\Cms\Support;

use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Routing\CmsFrontRouteLocalizationBinding;
use Modules\Cms\Services\CmsVisitorRedirectResolver;

/**
 * Strips the CMS public route prefix (e.g. {@see modularousConfig('cms_routing.front_route_prefix')})
 * so {@see UrlRoute} rows (stored without that segment) match the request.
 */
final class CmsFrontPath
{
    /**
     * Normalized path after the CMS front prefix, suitable for locale parsing and registry lookup.
     *
     * When the public route is registered with a dedicated `{locale}` parameter (see
     * {@see CmsFrontRouteLocalizationBinding}), prefer
     * {@see CmsVisitorRedirectResolver::resolveLocalePathKeyAndExplicitFlag()} — the `{path}`
     * parameter is then the tail after locale (ParentSegment + slug segments), matching UrlRoute storage.
     */
    public static function innerNormalizedPath(Request $request, CanonicalUrlResolverInterface $canonical): string
    {
        $full = $canonical->normalizePath($request->path());
        $segment = trim((string) modularousConfig('cms_routing.front_route_prefix', 'cms'), '/');

        if ($segment === '') {
            return $full;
        }

        $prefix = '/' . $segment;

        if ($full === $prefix) {
            return '/';
        }

        if (str_starts_with($full, $prefix . '/')) {
            $rest = mb_substr($full, mb_strlen($prefix));

            return $canonical->normalizePath($rest);
        }

        return $full;
    }

    /**
     * Inverse of {@see innerNormalizedPath()}: builds the browser path for a {@see UrlRoute}
     * `normalized_path` + locale (same rules as admin slug preview / public routing).
     *
     * @todo Consolidate CMS URL surface with a shared trait (HasParentSegment + HasSlug + UrlRoute) for non-Page entities.
     */
    public static function publicBrowserPathForLocaleAndRegistryPath(
        string $locale,
        string $normalizedPath,
        ?CanonicalUrlResolverInterface $canonical = null,
    ): string {
        $canonical ??= app(CanonicalUrlResolverInterface::class);

        $front = trim((string) modularousConfig('cms_routing.front_route_prefix', 'cms'), '/');
        $defaultLocale = (string) modularousConfig('cms_routing.default_locale', config('app.locale'));
        $hideDefaultLocale = (bool) modularousConfig('cms_routing.hide_default_locale_segment', false);

        $locale = trim($locale, '/');
        $segments = [];

        if ($front !== '') {
            $segments[] = $front;
        }

        $appendLocaleSegment = true;

        if (CmsSluglessFallbackLocale::shouldOmitLocaleSegmentFromPublicUrlsFor($locale)) {
            $appendLocaleSegment = false;
        } elseif ($hideDefaultLocale && $locale === $defaultLocale) {
            $appendLocaleSegment = false;
        }

        if ($appendLocaleSegment && $locale !== '') {
            $segments[] = $locale;
        }

        $inner = trim($normalizedPath, '/');
        if ($inner !== '') {
            foreach (explode('/', $inner) as $part) {
                if ($part !== '') {
                    $segments[] = $part;
                }
            }
        }

        $raw = '/' . implode('/', array_filter($segments, fn ($s) => $s !== ''));

        return $canonical->normalizePath($raw === '//' ? '/' : $raw);
    }
}

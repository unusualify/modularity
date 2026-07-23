<?php

namespace Modules\Cms\Routing;

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\CmsSignedPublicPreviewController;
use Modules\Cms\Http\Controllers\Front\PublicSitemapController;
use Modules\Cms\Http\Controllers\Front\PublicSitemapXslController;
use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Modules\Cms\Http\Controllers\PublicStyleSheetAssetController;

/**
 * Catalog of built-in CMS public system endpoints (not UrlRoute pages).
 *
 * Add new built-ins here. Host-app paths belong in {@code routes/web.php} (registered before the
 * CMS catch-all) or {@see modularousConfig('cms_routing.public_front_catch_all_exclude_path_prefixes')}.
 *
 * @see CmsPublicSystemRouteRegistrar
 * @see CmsFrontRouteRegistrar::catchAllPathParameterPattern()
 */
final class CmsPublicSystemRoutes
{
    /**
     * @return list<array{
     *     key: non-empty-string,
     *     enabled: bool,
     *     exclude_prefix: string,
     *     bind_public_domain: bool,
     *     register: callable(): void
     * }>
     */
    public static function definitions(): array
    {
        $previewPrefix = trim((string) modularousConfig('cms_routing.signed_preview.path_prefix', 'cms/preview'), '/');
        $stylesheetPrefix = trim((string) modularousConfig('cms_stylesheets.public_route.path_prefix', 'cms/stylesheets'), '/');

        return [
            [
                'key' => 'robots_txt',
                'enabled' => (bool) modularousConfig('cms_seo.robots.route_enabled', true),
                'exclude_prefix' => 'robots.txt',
                'bind_public_domain' => true,
                'register' => static function (): void {
                    Route::middleware('web')
                        ->get('/robots.txt', RobotsTxtController::class)
                        ->name('cms.robots_txt');
                },
            ],
            [
                'key' => 'sitemap',
                'enabled' => (bool) modularousConfig('cms_sitemap.route_enabled', true),
                'exclude_prefix' => 'sitemap.xml',
                'bind_public_domain' => true,
                'register' => static function (): void {
                    Route::middleware('web')
                        ->get('/sitemap.xml', PublicSitemapController::class)
                        ->name('cms.sitemap');
                },
            ],
            [
                'key' => 'sitemap_xsl',
                'enabled' => (bool) modularousConfig('cms_sitemap.route_enabled', true),
                'exclude_prefix' => 'sitemap.xsl',
                'bind_public_domain' => true,
                'register' => static function (): void {
                    Route::middleware('web')
                        ->get('/sitemap.xsl', PublicSitemapXslController::class)
                        ->name('cms.sitemap_xsl');
                },
            ],
            [
                'key' => 'signed_preview',
                'enabled' => (bool) modularousConfig('cms_routing.signed_preview.enabled', true) && $previewPrefix !== '',
                'exclude_prefix' => $previewPrefix,
                'bind_public_domain' => true,
                'register' => static function () use ($previewPrefix): void {
                    $max = (int) modularousConfig('cms_routing.signed_preview.throttle_max_attempts', 120);
                    $decay = (int) modularousConfig('cms_routing.signed_preview.throttle_decay_minutes', 1);
                    $throttle = 'throttle:' . max(1, $max) . ',' . max(1, $decay);

                    Route::middleware(['web', 'signed', $throttle])
                        ->get($previewPrefix . '/{module}/{route}/{id}/{locale?}', CmsSignedPublicPreviewController::class)
                        ->where([
                            'module' => '[A-Za-z][A-Za-z0-9]*',
                            'route' => '[A-Za-z][A-Za-z0-9]*',
                            'id' => '[0-9]+',
                        ])
                        ->name('cms.signed_preview.show');
                },
            ],
            [
                'key' => 'stylesheet',
                'enabled' => (bool) modularousConfig('cms_stylesheets.public_route.enabled', true) && $stylesheetPrefix !== '',
                'exclude_prefix' => $stylesheetPrefix,
                'bind_public_domain' => true,
                'register' => static function () use ($stylesheetPrefix): void {
                    Route::middleware('web')
                        ->get($stylesheetPrefix . '/{slug}.css', [PublicStyleSheetAssetController::class, 'show'])
                        ->where('slug', '[A-Za-z0-9_-]+')
                        ->name('cms.public.stylesheet');
                },
            ],
        ];
    }

    /**
     * Slash-trimmed path prefixes the CMS public catch-all (and related middleware) must ignore.
     *
     * @return list<string>
     */
    public static function reservedPathPrefixes(): array
    {
        $out = [];

        foreach (self::definitions() as $def) {
            if (! $def['enabled']) {
                continue;
            }
            $prefix = trim((string) $def['exclude_prefix'], '/');
            if ($prefix !== '') {
                $out[] = $prefix;
            }
        }

        foreach ((array) modularousConfig('cms_routing.public_front_catch_all_exclude_path_prefixes', []) as $raw) {
            if (! is_string($raw)) {
                continue;
            }
            $p = trim($raw, '/');
            if ($p !== '') {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }
}

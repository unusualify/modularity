<?php

namespace Modules\Cms\Support;

use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;

/**
 * Artisan + runtime entry point for CMS public URL registry caches (ParentSegment revisions,
 * front route registration snapshots, UrlRoute revision, committed sitemap XML).
 *
 * Separate from Modularous resource caches ({@code modularous:cache:*} index/record/count).
 */
final class CmsPublicUrlRegistryCacheManager
{
    /**
     * @return list<string> Cleared layers (human-readable labels for console output).
     */
    public function clear(bool $revisions = true, bool $routes = true, bool $sitemap = true): array
    {
        $cleared = [];

        if ($revisions) {
            CmsPublicUrlRegistryCoordinator::resetRevisionCounters();
            $cleared[] = 'revisions';
        }

        if ($routes) {
            CmsFrontRouteRegistrationCache::clearPersistent();
            $cleared[] = 'front_routes';
        }

        if ($sitemap) {
            app(CmsSitemapCacheService::class)->forget();
            $cleared[] = 'sitemap';
        }

        return $cleared;
    }

    /**
     * @return list<string> Warmed layers.
     */
    public function cache(bool $routes = true, bool $sitemap = true): array
    {
        $warmed = [];

        if ($routes) {
            CmsFrontRouteRegistrationCache::warm();
            $warmed[] = 'front_routes';
        }

        if ($sitemap) {
            $xml = app(CmsSitemapBuildService::class)->buildXml();
            app(CmsSitemapCacheService::class)->commit($xml);
            $warmed[] = 'sitemap';
        }

        return $warmed;
    }

    /**
     * ParentSegment registry changed (shape, enabled targets, prefixes).
     */
    public function invalidateParentSegmentRegistry(bool $warmRoutes = false): void
    {
        CmsPublicUrlRegistryCoordinator::invalidateParentSegmentRegistry();

        if ((bool) modularousConfig('cms_routing.forget_sitemap_on_parent_segment_registry_invalidate', true)) {
            app(CmsSitemapCacheService::class)->forget();
        }

        if ($warmRoutes) {
            CmsFrontRouteRegistrationCache::warm();
        }
    }

    /**
     * UrlRoute path index changed (entity save, bulk resync after ParentSegment edits).
     */
    public function touchUrlRouteRegistry(bool $forgetSitemap = false): void
    {
        CmsPublicUrlRegistryCoordinator::invalidateUrlRouteRegistry();

        if ($forgetSitemap || (bool) modularousConfig('cms_routing.forget_sitemap_on_url_route_registry_touch', false)) {
            app(CmsSitemapCacheService::class)->forget();
        }
    }
}

<?php

namespace Modules\Cms\Support;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\ParentSegment;

/**
 * Central revision tokens for the CMS public URL stack ({@see ParentSegment} registry + {@see UrlRoute} path index).
 *
 * {@see ParentSegment} rows define which model classes may be served publicly and their locale path prefixes.
 * {@see UrlRoute} rows ({@code KIND_PAGE_PUBLIC}) index concrete {@code normalized_path} → urlable for request resolution
 * ({@see CmsPublicModelResolver}). Parent-prefix edits resync UrlRoute via {@see ParentSegmentUrlRouteObserver};
 * entity saves resync via {@see UrlRouteRegistrySyncTrait}.
 *
 * Front catch-all **registration** ({@see CmsFrontRouteRegistrar}) depends only on ParentSegment + module front controllers,
 * not on individual UrlRoute lines. Bump {@see invalidateParentSegmentRegistry()} when ParentSegment registry changes.
 * UrlRoute content sync does not require front route re-registration.
 */
final class CmsPublicUrlRegistryCoordinator
{
    private const PARENT_SEGMENT_REVISION_KEY = 'modularous_cms.public_url_registry.parent_segment_revision_v1';

    private const URL_ROUTE_REVISION_KEY = 'modularous_cms.public_url_registry.url_route_revision_v1';

    public static function parentSegmentRegistryRevision(): string
    {
        $revision = self::store()->get(self::PARENT_SEGMENT_REVISION_KEY);

        return is_string($revision) && $revision !== '' ? $revision : '0';
    }

    public static function urlRouteRegistryRevision(): string
    {
        $revision = self::store()->get(self::URL_ROUTE_REVISION_KEY);

        return is_string($revision) && $revision !== '' ? $revision : '0';
    }

    /**
     * ParentSegment create/update/delete (registry shape, enabled targets, prefixes).
     */
    public static function invalidateParentSegmentRegistry(): void
    {
        self::bumpRevision(self::PARENT_SEGMENT_REVISION_KEY);
        CmsFrontRouteRegistrationCache::forgetRuntime();
    }

    /**
     * UrlRoute registry sync (per-model path rows). Runtime resolution caches may use this later;
     * front route registration ignores UrlRoute content.
     */
    public static function invalidateUrlRouteRegistry(): void
    {
        self::bumpRevision(self::URL_ROUTE_REVISION_KEY);
    }

    /**
     * Reset revision counters (artisan clear). Next request rebuilds against revision {@code 0}.
     */
    public static function resetRevisionCounters(): void
    {
        self::store()->forget(self::PARENT_SEGMENT_REVISION_KEY);
        self::store()->forget(self::URL_ROUTE_REVISION_KEY);
        CmsFrontRouteRegistrationCache::forgetRuntime();
    }

    /**
     * Cheap checksum of the enabled ParentSegment registry (fallback when revision store is cold).
     */
    public static function parentSegmentRegistryChecksum(): string
    {
        if (! database_exists() || ! Schema::hasTable((new ParentSegment)->getTable())) {
            return '0';
        }

        $row = ParentSegment::query()
            ->where('enabled', true)
            ->selectRaw('COUNT(*) as aggregate_count, MAX(updated_at) as aggregate_updated_at')
            ->first();

        $count = (int) ($row->aggregate_count ?? 0);
        $updatedAt = (string) ($row->aggregate_updated_at ?? '');

        $targets = ParentSegment::query()
            ->where('enabled', true)
            ->orderBy('target_model_class')
            ->pluck('target_model_class')
            ->filter(static fn ($class) => is_string($class) && $class !== '')
            ->values()
            ->all();

        return hash('xxh128', $count . "\0" . $updatedAt . "\0" . implode("\0", $targets));
    }

    private static function bumpRevision(string $key): void
    {
        $next = (int) self::store()->get($key, 0) + 1;
        self::store()->forever($key, (string) $next);
    }

    private static function store(): CacheRepository
    {
        return Cache::store(self::storeName());
    }

    private static function storeName(): string
    {
        return (string) modularousConfig(
            'cms_routing.public_url_registry_cache_store',
            'file'
        );
    }
}

<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Contracts\Cache;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;

/**
 * Storage contract for URL-keyed public presentation HTML (locale + normalized path + optional query).
 *
 * Default driver: {@see FileUrlPresentationCacheDriver} (local filesystem).
 * Future drivers: shared_file (EFS/NFS), s3 (object storage sync), redis (presentationItem replay).
 *
 * @see config('modularous.cache.presentationItem.url.driver')
 */
interface UrlPresentationCacheStoreInterface
{
    public const FRESHNESS_HIT = 'HIT';

    public const FRESHNESS_STALE = 'STALE';

    /**
     * @param array<string, mixed> $meta
     */
    public function put(
        string $locale,
        string $cacheLookupKey,
        string $html,
        array $meta,
        ?int $freshTtl = null,
        ?int $staleTtl = null,
    ): bool;

    /**
     * @return array{html: string, meta: array<string, mixed>, freshness: string}|null
     */
    public function get(string $locale, string $cacheLookupKey): ?array;

    public function forget(string $locale, string $cacheLookupKey): bool;

    /**
     * Deletes all cached variants for a locale + normalized path (any query suffix).
     */
    public function forgetPathVariants(string $locale, string $normalizedPath): int;

    /**
     * Deletes all URL stale entries tied to a model (all locales, paths, and query variants).
     *
     * @param class-string<Model>|string $modelClass
     */
    public function forgetByRelation(string $modelClass, int|string $id): int;

    /**
     * Deletes all URL stale entries for a module route (all records, paths, and query variants).
     */
    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int;

    public function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string;

    public function normalizePath(string $path): string;

    /**
     * @return array{0: string, 1: string}
     */
    public function splitLookupKey(string $cacheLookupKey): array;
}

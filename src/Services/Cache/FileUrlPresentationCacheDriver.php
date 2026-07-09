<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;

/**
 * Default single-node driver: local filesystem via {@see UrlKeyedStaleCache}.
 *
 * For multi-node scale, mount a shared volume at `presentationItem.url.base_path` and keep
 * `driver=file`, or implement {@see UrlPresentationCacheStoreInterface} for s3/redis/shared_file.
 */
final class FileUrlPresentationCacheDriver implements UrlPresentationCacheStoreInterface
{
    public function __construct(
        private readonly UrlKeyedStaleCache $cache,
    ) {}

    public function underlyingFileCache(): UrlKeyedStaleCache
    {
        return $this->cache;
    }

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
    ): bool {
        return $this->cache->put($locale, $cacheLookupKey, $html, $meta, $freshTtl, $staleTtl);
    }

    /**
     * @return array{html: string, meta: array<string, mixed>, freshness: string}|null
     */
    public function get(string $locale, string $cacheLookupKey): ?array
    {
        return $this->cache->get($locale, $cacheLookupKey);
    }

    public function forget(string $locale, string $cacheLookupKey): bool
    {
        return $this->cache->forget($locale, $cacheLookupKey);
    }

    public function forgetPathVariants(string $locale, string $normalizedPath): int
    {
        return $this->cache->forgetPathVariants($locale, $normalizedPath);
    }

    public function forgetByRelation(string $modelClass, int|string $id): int
    {
        return $this->cache->forgetByRelation($modelClass, $id);
    }

    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        return $this->cache->forgetByModuleRoute($moduleName, $moduleRouteName);
    }

    public function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string
    {
        return $this->cache->composeLookupKey($normalizedPath, $querySuffix);
    }

    public function normalizePath(string $path): string
    {
        return $this->cache->normalizePath($path);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function splitLookupKey(string $cacheLookupKey): array
    {
        return $this->cache->splitLookupKey($cacheLookupKey);
    }
}

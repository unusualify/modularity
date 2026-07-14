<?php

namespace Unusualify\Modularous\Services\Concerns;

use Closure;
use Illuminate\Cache\Repository;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * Cache helper methods.
 *
 * @requires property $store - Cache store instance
 * @requires method usesTags() - Check if tags are supported
 * @requires method isEnabled() - Check if caching is enabled
 * @requires method getPrefix() - Get cache prefix
 */
trait CacheHelpers
{
    use CacheTags, CacheInvalidation;

    /**
     * Get the cache store instance.
     */
    abstract protected function getStore(): Repository;

    /**
     * Get the cache prefix.
     */
    abstract protected function getPrefix(): string;

    /**
     * Check if cache tags are supported.
     */
    abstract protected function usesTags(): bool;

    /**
     * Check if caching is enabled.
     */
    abstract protected function isEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool;

    /**
     * Remember a value in cache with relationship tags for granular invalidation.
     *
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param Closure $callback Value generator
     * @param string|null $module Module name
     * @param string|null $routeName Route name (submodule)
     * @param array $relations Related models ['ModelClass' => id, ...]
     */
    public function rememberWithRelations(string $key, int $ttl, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null, array $relations = [], ?string $type = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            // Add relationship tags for granular invalidation
            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($this->filterStaleContextFromRelations($relations)));
            }

            $value = $this->getStore()
                ->tags($tags)
                ->remember($key, $ttl, $callback);

            if (is_string($value) && $value !== '') {
                $this->mirrorPresentationItemStaleIfEnabled($key, $value, $moduleName, $moduleRouteName, $relations, $type);
            }

            return $value;
        }

        $value = $this->getStore()->remember($key, $ttl, $callback);

        if (is_string($value) && $value !== '') {
            $this->mirrorPresentationItemStaleIfEnabled($key, $value, $moduleName, $moduleRouteName, $relations, $type);
        }

        return $value;
    }

    /**
     * Remember a value in cache.
     */
    public function remember(string $key, int $ttl, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true)
                : $this->getModuleTags($moduleName, onlyModule: false);

            return $this->getStore()
                ->tags($tags)
                ->remember($key, $ttl, $callback);
        }

        return $this->getStore()->remember($key, $ttl, $callback);
    }

    /**
     * Remember a value in cache forever.
     */
    public function rememberForever(string $key, Closure $callback, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        // Skip cache if disabled
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return $callback();
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->rememberForever($key, $callback);
        }

        return $this->getStore()->rememberForever($key, $callback);
    }

    /**
     * Get a value from cache.
     */
    public function get(string $key, $default = null, ?string $moduleName = null, ?string $moduleRouteName = null)
    {
        return $this->getWithRelations($key, $default, $moduleName, $moduleRouteName);
    }

    /**
     * Get a value from cache with relationship tags (must match {@see putWithRelations()} / {@see rememberWithRelations()}).
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function getWithRelations(
        string $key,
        $default = null,
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
        array $relations = [],
        ?string $type = null,
    ) {
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return $default;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($this->filterStaleContextFromRelations($relations)));
            }

            return $this->getStore()
                ->tags($tags)
                ->get($key, $default);
        }

        return $this->getStore()->get($key, $default);
    }

    /**
     * Put a value in cache.
     */
    public function put(string $key, $value, int $ttl, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->put($key, $value, $ttl);
        }

        return $this->getStore()->put($key, $value, $ttl);
    }

    /**
     * Put a value in cache with relationship tags for granular invalidation.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @param string|null $module Module name
     * @param string|null $routeName Route name (submodule)
     * @param array $relations Related models ['ModelClass' => id, ...]
     */
    public function putWithRelations(string $key, $value, int $ttl, ?string $moduleName = null, ?string $moduleRouteName = null, array $relations = [], ?string $type = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName, $type)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            // Add relationship tags for granular invalidation
            if (! empty($relations)) {
                $tags = array_merge($tags, $this->generateRelationTags($this->filterStaleContextFromRelations($relations)));
            }

            $put = $this->getStore()
                ->tags($tags)
                ->put($key, $value, $ttl);

            if ($put && is_string($value) && $value !== '') {
                $this->mirrorPresentationItemStaleIfEnabled($key, $value, $moduleName, $moduleRouteName, $relations, $type);
            }

            return $put;
        }

        $put = $this->getStore()->put($key, $value, $ttl);

        if ($put && is_string($value) && $value !== '') {
            $this->mirrorPresentationItemStaleIfEnabled($key, $value, $moduleName, $moduleRouteName, $relations, $type);
        }

        return $put;
    }

    /**
     * Check if a key exists in cache.
     */
    public function has(string $key, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return false;
        }

        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName)
                : $this->getModuleTags($moduleName);

            return $this->getStore()
                ->tags($tags)
                ->has($key);
        }

        return $this->getStore()->has($key);
    }

    /**
     * Stale cache key suffix for SWR dual-key storage (fresh Redis key reference).
     */
    public function staleCacheKey(string $key): string
    {
        return "{$key}:stale";
    }

    /**
     * Put a stale value on the filesystem (never Redis).
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function putStaleWithRelations(
        string $key,
        $value,
        int $ttl,
        array $relations = [],
        ?string $type = null,
    ): bool {
        if (! $this->isStaleStorageEnabled($type) || ! is_string($value) || $value === '') {
            return false;
        }

        return $this->getStaleFileCache()->put($key, $value, $ttl, $relations);
    }

    /**
     * Get a stale value from the filesystem — does not require Redis.
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function getStale(
        string $key,
        $default = null,
        array $relations = [],
        ?string $type = null,
    ) {
        if (! $this->usesFileStaleStore($type)) {
            return $default;
        }

        return $this->getStaleFileCache()->get($key, $default, $relations);
    }

    /**
     * Forget a stale cache file.
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    public function forgetStale(string $key, array $relations = []): bool
    {
        return $this->getStaleFileCache()->forget($key);
    }

    abstract protected function getStaleFileCache(): StaleFileCache;

    /**
     * @param array<string, int|string|array<int|string>> $relations
     * @return array<string, int|string|array<int|string>>
     */
    protected function filterStaleContextFromRelations(array $relations): array
    {
        unset($relations[StaleFileCache::LOCALE_RELATION_KEY]);

        return $relations;
    }

    /**
     * Whether filesystem stale storage is available for writes/reads.
     */
    protected function isStaleStorageEnabled(?string $type = null): bool
    {
        return $this->usesFileStaleStore($type) && $this->isEnabled(null, null, $type);
    }

    /**
     * Mirror fresh presentationItem HTML to the filesystem stale store when SWR is enabled.
     *
     * @param array<string, int|string|array<int|string>> $relations
     */
    protected function mirrorPresentationItemStaleIfEnabled(
        string $key,
        string $value,
        ?string $moduleName,
        ?string $moduleRouteName,
        array $relations,
        ?string $type,
    ): void {
        if ($type !== 'presentationItem'
            || $this->getPresentationCacheStore() !== 'model'
            || ! $this->isSwrEnabled($moduleName, $moduleRouteName, $type)) {
            return;
        }

        $written = $this->putStaleWithRelations(
            $key,
            $value,
            $this->getStaleTtl($type),
            $relations,
            $type,
        );

        if (! $written) {
            ModularousCacheLogger::warning('cache.stale.put_failed', [
                'key' => $key,
                'type' => $type,
                'module' => $moduleName,
                'route' => $moduleRouteName,
                'relations' => $relations,
            ]);
        }
    }

    /**
     * Forget a specific cache key.
     */
    public function forget(string $key, ?string $moduleName = null, ?string $moduleRouteName = null): bool
    {
        if ($this->usesTags() && $moduleName !== null) {
            $tags = $moduleRouteName !== null
                ? $this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true)
                : $this->getModuleTags($moduleName, onlyModule: true);

            return $this->getStore()
                ->tags($tags)
                ->forget($key);
        }

        return $this->getStore()->forget($key);
    }

    /**
     * Flush all modularous caches.
     */
    public function flush(): bool
    {
        if ($this->usesTags()) {
            // Flush the main modularous tag
            $this->getStore()->tags([$this->getPrefix()])->flush();

            return true;
        }

        // Fallback: invalidate by pattern
        return $this->invalidateByPattern("{$this->getPrefix()}:*") > 0;
    }
}

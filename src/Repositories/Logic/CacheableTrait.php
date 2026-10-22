<?php

namespace Unusualify\Modularous\Repositories\Logic;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Traits\Cache\Cacheable;
use Unusualify\Modularous\Traits\Cache\HasUserAwareCache;

trait CacheableTrait
{
    use Cacheable, HasUserAwareCache;

    /**
     * Whether to track relationships for granular cache invalidation.
     * When enabled, caches are tagged with related model IDs.
     */
    protected bool $trackCacheRelations = true;

    /**
     * Generic method to handle count caching with a consistent pattern.
     *
     * @param string $slug The cache key slug (e.g., 'all', 'published', 'draft')
     * @param callable $callback The callback to execute if cache misses
     * @param array $additionalParams Additional parameters for cache key generation
     */
    protected function cacheableCount(string $slug, callable $callback, array $additionalParams = []): int
    {
        return $this->rememberCache(
            callback: $callback,
            type: 'counts',
            data: array_merge($this->countScope, $additionalParams) + ['slug' => $slug]
        );
    }

    /**
     * Enable or disable relationship tracking for granular cache invalidation.
     */
    public function withRelationTracking(bool $enabled = true): static
    {
        $this->trackCacheRelations = $enabled;

        return $this;
    }

    /**
     * Disable relationship tracking for caching.
     */
    public function withoutRelationTracking(): static
    {
        return $this->withRelationTracking(false);
    }

    /**
     * Extract relationship IDs from a model for cache tagging.
     * Returns array of ['ModelClass' => id] for foreign keys.
     */
    protected function extractRelationIds(Model $model): array
    {
        $relations = [];

        // Get all attributes that look like foreign keys (*_id)
        $attributes = $model->getAttributes();

        foreach ($attributes as $key => $value) {
            if ($value !== null && Str::endsWith($key, '_id')) {
                // Convert foreign key to model class name
                // e.g., company_id -> Company
                $relatedName = Str::studly(Str::beforeLast($key, '_id'));

                // Try to find the actual model class from the relationship
                $relationMethod = Str::camel(Str::beforeLast($key, '_id'));

                if (method_exists($model, $relationMethod)) {
                    try {
                        $relation = $model->$relationMethod();
                        if ($relation) {
                            $relatedClass = get_class($relation->getRelated());
                            $relations[$relatedClass] = $value;

                            continue;
                        }
                    } catch (\Exception $e) {
                        // Relationship method exists but threw an error
                    }
                }

                // Fallback: use a generic class name based on the foreign key
                $relations[$relatedName] = $value;
            }
        }

        return $relations;
    }

    /**
     * Extract relationship IDs from a collection of models.
     * Returns array of ['ModelClass' => [id1, id2, ...]] for foreign keys.
     */
    protected function extractRelationIdsFromCollection(Collection $collection): array
    {
        $relations = [];

        foreach ($collection as $model) {
            if ($model instanceof Model) {
                $modelRelations = $this->extractRelationIds($model);

                foreach ($modelRelations as $relatedClass => $id) {
                    if (! isset($relations[$relatedClass])) {
                        $relations[$relatedClass] = [];
                    }
                    if (! in_array($id, $relations[$relatedClass])) {
                        $relations[$relatedClass][] = $id;
                    }
                }
            }
        }

        return $relations;
    }

    /**
     * Remember index results with relationship tags.
     * Fetches data once and caches with extracted relationship IDs.
     */
    protected function rememberIndexWithRelations(
        string $cacheKey,
        int $ttl,
        string $module,
        string $routeName,
        $with,
        $scopes,
        $orders,
        $perPage,
        $appends,
        $forcePagination,
        $id,
        $exceptIds
    ) {
        // Check if already cached (simple check without relation tags)
        $cached = ModularousCache::get($cacheKey, null, $module, $routeName);

        if ($cached !== null) {
            return $cached;
        }

        // Cache miss - fetch data and cache with relationship tags
        $result = $this->get($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds);

        // Extract relationship IDs from the result
        $collection = $result instanceof LengthAwarePaginator
            ? collect($result->items())
            : $result;

        $relations = $this->extractRelationIdsFromCollection($collection);

        // Cache with relationship tags
        ModularousCache::putWithRelations($cacheKey, $result, $ttl, $module, $routeName, $relations);

        return $result;
    }

    /**
     * Remember a record with relationship tags.
     * Fetches data once and caches with extracted relationship IDs.
     */
    protected function rememberRecordWithRelations(
        string $cacheKey,
        int $ttl,
        string $module,
        string $routeName,
        $id,
        $with,
        $withCount,
        $lazy,
        $scopes,
        $useDefaultScopes
    ) {
        // Check if already cached
        $cached = ModularousCache::get($cacheKey, null, $module, $routeName);

        if ($cached !== null) {
            return $cached;
        }

        // Cache miss - fetch data
        $record = $this->getById($id, $with, $withCount, $lazy, $scopes, $useDefaultScopes);

        if ($record) {
            // Extract relationship IDs from the record
            $relations = $this->extractRelationIds($record);

            // Cache with relationship tags
            ModularousCache::putWithRelations($cacheKey, $record, $ttl, $module, $routeName, $relations);
        }

        return $record;
    }

    /**
     * Get a single record by ID with caching.
     * With relationship tracking enabled, the cache is tagged with the record's
     * related model IDs for granular invalidation.
     *
     * @param mixed $id
     * @param array $with
     * @param array $withCount
     * @param array $lazy
     * @param array $scopes
     * @param bool $useDefaultScopes
     * @return \Unusualify\Modularous\Entities\Model
     */
    public function getByIdCached($id, $with = [], $withCount = [], $lazy = [], $scopes = [], $useDefaultScopes = false)
    {
        if (! $this->shouldUseCache('record')) {
            return $this->getById($id, $with, $withCount, $lazy, $scopes, $useDefaultScopes);
        }

        $module = $this->getCacheModuleName();
        $routeName = $this->getCacheModuleRouteName();
        $ttl = ModularousCache::getTtl('record', $module, $routeName);

        // For records with scopes, include user context
        $needsUserContext = ! empty($scopes) && $this->shouldUseUserAwareCache();

        if (! empty($with) || ! empty($withCount) || ! empty($lazy) || ! empty($scopes)) {
            $params = [
                'id' => $id,
                'with' => $with,
                'withCount' => $withCount,
                'lazy' => $lazy,
                'scopes' => $scopes,
                'useDefaultScopes' => $useDefaultScopes,
            ];

            if ($needsUserContext) {
                $params = $this->addUserContext($params);
            }

            $cacheKey = ModularousCache::generateCacheKey($module, $routeName, 'record', $params);
        } else {
            $cacheKey = $this->generateTypeCacheKey('record', ['id' => $id]);
        }

        // Use relationship tracking for granular invalidation
        if ($this->trackCacheRelations) {
            return $this->rememberRecordWithRelations($cacheKey, $ttl, $module, $routeName, $id, $with, $withCount, $lazy, $scopes, $useDefaultScopes);
        }

        return ModularousCache::remember(
            $cacheKey,
            $ttl,
            fn () => $this->getById($id, $with, $withCount, $lazy, $scopes, $useDefaultScopes),
            $module,
            $routeName
        );
    }

    // /**
    //  * Invalidate all caches for this repository's route (submodule).
    //  */
    // public function invalidateCache(): void
    // {
    //     ModularousCache::invalidateModuleRoute($this->getCacheModuleName(), $this->getCacheModuleRouteName());
    // }

    // /**
    //  * Invalidate count caches for this repository's route.
    //  */
    // public function invalidateCountCache(): void
    // {
    //     ModularousCache::invalidateCountCaches($this->getCacheModuleName(), $this->getCacheModuleRouteName());
    // }

    // /**
    //  * Invalidate index/list caches for this repository's route.
    //  */
    // public function invalidateIndexCache(): void
    // {
    //     ModularousCache::invalidateIndexCaches($this->getCacheModuleName(), $this->getCacheModuleRouteName());
    // }

    // /**
    //  * Invalidate cache for a specific record.
    //  */
    // public function invalidateRecordCache($id): void
    // {
    //     $module = $this->getCacheModuleName();
    //     $routeName = $this->getCacheModuleRouteName();
    //     $cacheKey = $this->generateTypeCacheKey('record', ['id' => $id]);
    //     ModularousCache::forget($cacheKey, $module, $routeName);
    // }
}

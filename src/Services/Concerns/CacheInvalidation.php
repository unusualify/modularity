<?php

namespace Unusualify\Modularous\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Support\ModularousCacheLogger;
use Unusualify\Modularous\Traits\Cache\WarmupCache;
use Unusualify\Modularous\Traits\ModularModel;

/**
 * Cache helper methods.
 *
 * @requires property $store - Cache store instance
 * @requires method usesTags() - Check if tags are supported
 * @requires method isEnabled() - Check if caching is enabled
 * @requires method getPrefix() - Get cache prefix
 */
trait CacheInvalidation
{
    use CacheTags, ModularModel, WarmupCache;

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
     * Invalidate all caches for a module using tags.
     */
    public function invalidateModule(string $moduleName): bool
    {
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleTags($moduleName, onlyModule: true))->flush();

            return true;
        }

        $moduleName = Str::studly($moduleName);

        // Fallback: invalidate by pattern using Redis (only works without tags)
        return $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:*") > 0;
    }

    /**
     * Invalidate all caches for a module and route using tags.
     */
    public function invalidateModuleRoute(string $moduleName, string $moduleRouteName): bool
    {
        $urlStaleDeleted = $this->forgetUrlStaleForModuleRoute($moduleName, $moduleRouteName);

        if ($this->usesTags()) {
            // Route-scoped flush only — including the root modularous tag would wipe unrelated routes
            // (e.g. dependent PackageCountry warmup entries cleared by CountryPackagesHub invalidation).
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();

            return true;
        }

        // Fallback: invalidate by pattern using Redis (only works without tags)
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        return $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:*") > 0
            || $urlStaleDeleted > 0;
    }

    /**
     * Invalidate caches related to a specific model and ID.
     * This provides granular invalidation - only caches that reference this model:id are cleared.
     *
     * Example: When Company:5 is updated, only invalidate caches tagged with 'rel:Company:5'
     *
     * @param string $modelClass Full model class or basename (e.g., 'Company')
     * @param mixed $id The model ID
     * @return bool Whether invalidation was successful
     */
    public function invalidateByRelatedModel(string $modelClass, $id): bool
    {
        if (! $this->usesTags()) {
            // Without tags, we can't do granular invalidation
            logger()->warning('invalidateByRelatedModel() requires tag support. Falling back to full module invalidation.');

            return false;
        }

        $tag = $this->generateRelationTag($modelClass, $id);

        try {
            $this->getStore()->tags([$tag])->flush();
            $this->forgetStaleFilesByRelation($modelClass, $id);

            return true;
        } catch (\Exception $e) {
            logger()->error("Failed to invalidate caches by relation tag {$tag}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Invalidate caches for multiple related models.
     *
     * @param array $relations Array of ['ModelClass' => id] or ['ModelClass' => [id1, id2]]
     * @return int Number of tags flushed
     */
    public function invalidateByRelatedModels(array $relations): int
    {
        if (! $this->usesTags()) {
            return 0;
        }

        $count = 0;

        foreach ($relations as $modelClass => $ids) {
            $ids = is_array($ids) ? $ids : [$ids];
            foreach ($ids as $id) {
                if ($id !== null && $this->invalidateByRelatedModel($modelClass, $id)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Invalidate caches by pattern using Redis SCAN.
     *
     * WARNING: This only works for non-tagged caches. When using tags,
     * Laravel prefixes keys with a tag namespace hash, so pattern matching
     * on your key names won't find them. Use tag-based invalidation instead.
     */
    public function invalidateByPattern(string $pattern): int
    {
        if ($this->usesTags()) {
            logger()->warning('invalidateByPattern() called with tags enabled. This will not work for tagged keys. Use tag-based invalidation instead.');

            return 0;
        }

        // Redis dışı driver'larda pattern invalidation desteklenmiyor
        $driver = config('modularous.cache.driver', config('cache.default'));
        if (! in_array($driver, ['redis', 'predis'])) {
            logger()->warning("invalidateByPattern() is only supported with Redis. Current driver: {$driver}");

            return 0;
        }

        $count = 0;
        $storePrefix = $this->getStore()->getStore()->getPrefix();
        $prefix = config('database.redis.options.prefix', '') . $storePrefix;

        try {
            $redis = Redis::connection('cache');
            $cursor = config('database.redis.client') == 'predis' ? '0' : null;

            do {
                [$cursor, $keys] = $redis->scan($cursor, [
                    'match' => $prefix . $pattern,
                    'count' => 100,
                ]);

                if (! empty($keys)) {
                    foreach ($keys as $key) {
                        $cacheKey = str_replace($prefix, '', $key);
                        $this->getStore()->forget($cacheKey);
                        $count++;
                    }
                }
            } while ($cursor != 0);
        } catch (\Exception $e) {
            logger()->warning('Cache pattern invalidation failed: ' . $e->getMessage());
        }

        return $count;
    }

    /**
     * Invalidate count caches for a specific route (submodule).
     */
    public function invalidateCountCaches(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): void
    {
        if ($this->usesTags()) {
            // With tags, flush the route tag
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: $onlyRoute))->flush();

            return;
        }

        // Fallback to pattern-based invalidation (only works without tags)
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);
        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:count:*");
    }

    /**
     * Invalidate index/list caches for a specific route (submodule).
     */
    public function invalidateIndexCaches(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): void
    {
        if ($this->usesTags()) {
            // With tags, flush the route tag
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, $onlyRoute))->flush();

            return;
        }

        // Fallback to pattern-based invalidation (only works without tags)
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);
        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:index:*");
    }

    public function invalidateFormattedItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();

            return;
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:formattedItem:{$id}:*");
    }

    public function invalidateFormItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();

            return;
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:formItem:{$id}:*");
    }

    public function invalidatePresentationItemCache(string $moduleName, string $moduleRouteName, $id, ?string $modelClass = null): void
    {
        $store = $this->getPresentationCacheStore();

        if ($modelClass !== null && $id !== null) {
            if ($store === 'model') {
                $this->getStaleFileCache()->forgetByRelation($modelClass, $id);
            }
            if ($store === 'url') {
                $this->forgetUrlStaleForModel($modelClass, $id);
            }
        } elseif ($id !== null && $store === 'model') {
            $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
        }

        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();

            return;
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:presentationItem:{$id}:*");
        if ($store === 'model' && $id !== null) {
            $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
        }
    }

    public function invalidateRecordCache(string $moduleName, string $moduleRouteName, $id): void
    {
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();

            return;
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:record:{$id}:*");
    }

    /**
     * Invalidate all caches related to a model.
     */
    public function invalidateForModel(Model $model, $types = [], $options = []): void
    {
        $warmup = isset($options['warmup']) ? $options['warmup'] : true;
        $skipInvalidation = (bool) ($options['skipInvalidation'] ?? false);

        $moduleName = $options['moduleName'] ?? $this->getModuleNameFromModel($model);
        $moduleRouteName = $options['moduleRouteName'] ?? $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        $newlyCreated = $model->wasRecentlyCreated;

        $shouldForgetPresentationStale = ! $newlyCreated
            && ($types['presentationItem'] ?? true)
            && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem');

        if ($shouldForgetPresentationStale) {
            $this->forgetPresentationStaleForModel($model);
        }

        $tagFlushResult = null;

        if (! $skipInvalidation) {
            if ($this->usesTags()) {
                $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: true))->flush();
                $tagFlushResult = true;
            } else {
                if (((isset($types['counts']) ? $types['counts'] : true)) && $this->isEnabled($moduleName, $moduleRouteName, 'counts')) {
                    $this->invalidateCountCaches($moduleName, $moduleRouteName, onlyRoute: false);
                }

                if (((isset($types['index']) ? $types['index'] : true)) && $this->isEnabled($moduleName, $moduleRouteName, 'index')) {
                    $this->invalidateIndexCaches($moduleName, $moduleRouteName, onlyRoute: false);
                }

                if (((isset($types['formattedItem']) ? $types['formattedItem'] : true)) && $this->isEnabled($moduleName, $moduleRouteName, 'formattedItem')) {
                    if (! $newlyCreated) {
                        $this->invalidateFormattedItemCache($moduleName, $moduleRouteName, $model->getKey());
                    }
                }

                if (((isset($types['formItem']) ? $types['formItem'] : true)) && $this->isEnabled($moduleName, $moduleRouteName, 'formItem')) {
                    if (! $newlyCreated) {
                        $this->invalidateFormItemCache($moduleName, $moduleRouteName, $model->getKey());
                    }
                }

                if (((isset($types['presentationItem']) ? $types['presentationItem'] : true)) && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem')) {
                    if (! $newlyCreated) {
                        $this->invalidatePresentationItemCache($moduleName, $moduleRouteName, $model->getKey(), $model::class);
                    }
                }
            }
        }

        ModularousCacheLogger::info('cache.invalidation.invalidate_for_model', [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'types' => $types,
            'warmup' => $warmup,
            'skipInvalidation' => $skipInvalidation,
            'newlyCreated' => $newlyCreated,
            'usesTags' => $this->usesTags(),
            'tagFlushResult' => $tagFlushResult,
        ]);

        try {
            if (! $newlyCreated && $warmup) {
                $this->warmupForModel($model, $types, $moduleName, $moduleRouteName);
            }
        } catch (\Exception $e) {
            logger()->error("Failed to warm up caches for model {$model->getKey()}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
        }
    }

    /**
     * Warm caches for a model without flushing tags first.
     *
     * @param array<string, mixed> $options
     */
    public function refreshModelCaches(Model $model, array $types = [], array $options = []): void
    {
        $moduleName = $options['moduleName'] ?? $this->getModuleNameFromModel($model);
        $moduleRouteName = $options['moduleRouteName'] ?? $this->getModuleRouteNameFromModel($model);

        ModularousCacheLogger::info('cache.invalidation.refresh_model_caches', [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'types' => $types,
        ]);

        if (
            ! $model->wasRecentlyCreated
            && ($types['presentationItem'] ?? false)
            && $moduleName
            && $moduleRouteName
            && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem')
        ) {
            $this->forgetPresentationStaleForModel($model);
        }

        $locale = isset($options['locale']) && is_string($options['locale']) && $options['locale'] !== ''
            ? $options['locale']
            : null;

        $this->warmupForModel($model, $types, $moduleName, $moduleRouteName, $locale);
    }

    /**
     * Purge selected cache types for a single model (no warmup).
     *
     * @param array<string, bool> $types
     */
    public function purgeModelCacheTypes(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null): void
    {
        $moduleName ??= $this->getModuleNameFromModel($model);
        $moduleRouteName ??= $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        $id = $model->getKey();

        ModularousCacheLogger::info('cache.invalidation.purge_model_cache_types', [
            'model' => get_class($model),
            'id' => $id,
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'types' => $types,
        ]);

        if ($this->usesTags() && (
            ($types['counts'] ?? false)
            || ($types['index'] ?? false)
            || ($types['record'] ?? false)
            || ($types['formItem'] ?? false)
            || ($types['formattedItem'] ?? false)
            || ($types['presentationItem'] ?? false)
        )) {
            $this->invalidateModuleRoute($moduleName, $moduleRouteName);

            if (($types['presentationItem'] ?? false) && $id !== null) {
                if ($this->getPresentationCacheStore() === 'model') {
                    $this->forgetStaleFilesByRelation($model::class, $id);
                    $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
                }
                if ($this->getPresentationCacheStore() === 'url') {
                    $this->forgetUrlStaleForModel($model::class, $id);
                }
            }

            return;
        }

        $this->invalidateRouteLevelCaches($moduleName, $moduleRouteName, $types);

        if ($id !== null) {
            $this->invalidatePerIdCachesForRoute($moduleName, $moduleRouteName, $id, $types, $model);
        }
    }

    /**
     * Warm all records for a module route (chunked).
     *
     * @param array<string, bool> $types
     */
    public function warmModuleRouteCaches(string $moduleName, string $moduleRouteName, array $types = []): void
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $module = Modularous::find($moduleName);
        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        if ($types === []) {
            $types = [
                'counts' => true,
                'formItem' => true,
                'formattedItem' => true,
                'presentationItem' => true,
            ];
        }

        if (($types['counts'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'counts')) {
            $controller = $module->getController($moduleRouteName);
            if ($controller) {
                try {
                    $this->warmupControllerCounts($controller);
                } catch (\Exception $e) {
                    logger()->error("Failed to warm count caches for {$moduleName}/{$moduleRouteName}: " . $e->getMessage());
                }
            }
        }

        $modelClass = get_class($module->getModel($moduleRouteName));
        $this->warmupAllItemCachesForRoute($module, $moduleName, $moduleRouteName, $modelClass, $types);
    }

    /**
     * Warm caches for a single model without invalidating first.
     */
    public function warmupModelCaches(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null): void
    {
        $this->warmupForModel($model, $types, $moduleName, $moduleRouteName);
    }

    /**
     * Warm caches for a single model, respecting the requested cache types.
     */
    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null, ?string $locale = null): void
    {
        $moduleName ??= $this->getModuleNameFromModel($model);
        $moduleRouteName ??= $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        $module = Modularous::find($moduleName);

        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        $warmCounts = ($types['counts'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'counts');
        $warmFormItem = ($types['formItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'formItem');
        $warmFormattedItem = ($types['formattedItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'formattedItem');
        $warmPresentationItem = ($types['presentationItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem');

        if (! $warmCounts && ! $warmFormItem && ! $warmFormattedItem && ! $warmPresentationItem) {
            return;
        }

        $controller = $module->getController($moduleRouteName);

        if ($warmCounts && $controller) {
            try {
                $this->warmupControllerCounts($controller);
            } catch (\Exception $e) {
                logger()->error("Failed to warm up count caches for model {$model->getKey()}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            }
        }

        if (($warmFormItem || $warmFormattedItem) && $controller) {
            try {
                $this->warmupControllerItem($controller, $model, $warmFormItem, $warmFormattedItem);
            } catch (\Exception $e) {
                logger()->error("Failed to warm up controller item caches for model {$model->getKey()}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            }
        }

        if ($warmPresentationItem) {
            try {
                $this->warmupPresentationItem($moduleName, $moduleRouteName, $model, $locale);
            } catch (\Exception $e) {
                logger()->error("Failed to warm up presentation item cache for model {$model->getKey()}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            }
        }
    }

    /**
     * Invalidate item caches for every record in a module route.
     */
    public function invalidateAllItemCaches(string $moduleName, string $moduleRouteName, array $types, bool $shouldWarmDependentModules = true): void
    {
        if (! $this->isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $module = Modularous::find($moduleName);
        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        $model = $module->getModel($moduleRouteName);
        $modelClass = get_class($model);

        if ($this->usesTags()) {
            $this->invalidateAllItemRouteCachesWithTags($moduleName, $moduleRouteName, $types);

            if ($shouldWarmDependentModules) {
                $this->warmupAllItemCachesForRoute($module, $moduleName, $moduleRouteName, $modelClass, $types);
            }

            return;
        }

        $this->invalidateRouteLevelCaches($moduleName, $moduleRouteName, $types);

        $this->eachRouteRecordId($module, $moduleRouteName, $modelClass, function ($id) use ($module, $moduleName, $moduleRouteName, $modelClass, $types, $shouldWarmDependentModules) {
            $this->invalidatePerIdCachesForRoute($moduleName, $moduleRouteName, $id, $types);

            if ($shouldWarmDependentModules) {
                try {
                    $this->warmupItemCachesForRouteId($module, $moduleName, $moduleRouteName, $modelClass, $id, $types);
                } catch (\Exception $e) {
                    logger()->error("Failed to warm up caches for model {$id}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
                }
            }
        });
    }

    protected function invalidateRouteLevelCaches(string $moduleName, string $moduleRouteName, array $types): void
    {
        if (($types['counts'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'counts')) {
            $this->invalidateCountCaches($moduleName, $moduleRouteName, onlyRoute: false);
        }

        if (($types['index'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'index')) {
            $this->invalidateIndexCaches($moduleName, $moduleRouteName, onlyRoute: false);
        }
    }

    protected function invalidatePerIdCachesForRoute(string $moduleName, string $moduleRouteName, $id, array $types, ?Model $model = null): void
    {
        if (($types['record'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'record')) {
            $this->invalidateRecordCache($moduleName, $moduleRouteName, $id);
        }

        if (($types['formattedItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'formattedItem')) {
            $this->invalidateFormattedItemCache($moduleName, $moduleRouteName, $id);
        }

        if (($types['formItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'formItem')) {
            $this->invalidateFormItemCache($moduleName, $moduleRouteName, $id);
        }

        if (($types['presentationItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem')) {
            $modelClass = $model !== null ? $model::class : null;
            $this->invalidatePresentationItemCache($moduleName, $moduleRouteName, $id, $modelClass);
        }
    }

    protected function invalidateAllItemRouteCachesWithTags(string $moduleName, string $moduleRouteName, array $types): void
    {
        $needsRouteFlush = ($types['counts'] ?? false)
            || ($types['index'] ?? false)
            || ($types['record'] ?? false)
            || ($types['formItem'] ?? false)
            || ($types['formattedItem'] ?? false)
            || ($types['presentationItem'] ?? false);

        if ($needsRouteFlush) {
            $this->invalidateModuleRoute($moduleName, $moduleRouteName);
        }
    }

    protected function eachRouteRecordId($module, string $moduleRouteName, string $modelClass, callable $callback, int $chunkSize = 100): void
    {
        if ($module->isSingleton($moduleRouteName)) {
            $record = $modelClass::query()->select('id')->first();

            if ($record !== null && $record->getKey() !== null) {
                $callback($record->getKey());
            }

            return;
        }

        $modelClass::query()->select('id')->chunk($chunkSize, function ($records) use ($callback) {
            foreach ($records as $record) {
                if ($record->getKey() !== null) {
                    $callback($record->getKey());
                }
            }
        });
    }

    protected function warmupAllItemCachesForRoute($module, string $moduleName, string $moduleRouteName, string $modelClass, array $types): void
    {
        $this->eachRouteRecordId($module, $moduleRouteName, $modelClass, function ($id) use ($module, $moduleName, $moduleRouteName, $modelClass, $types) {
            try {
                $this->warmupItemCachesForRouteId($module, $moduleName, $moduleRouteName, $modelClass, $id, $types);
            } catch (\Exception $e) {
                logger()->error("Failed to warm up caches for model {$id}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            }
        });
    }

    protected function warmupItemCachesForRouteId($module, string $moduleName, string $moduleRouteName, string $modelClass, $id, array $types): void
    {
        $model = $modelClass::find($id);

        if (! $model instanceof Model) {
            return;
        }

        $warmFormItem = ($types['formItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'formItem');
        $warmFormattedItem = ($types['formattedItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'formattedItem');
        $warmPresentationItem = ($types['presentationItem'] ?? false) && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem');

        if (! $warmFormItem && ! $warmFormattedItem && ! $warmPresentationItem) {
            return;
        }

        if ($warmFormItem || $warmFormattedItem) {
            $controller = $module->getController($moduleRouteName);
            $this->warmupControllerItem($controller, $model, $warmFormItem, $warmFormattedItem);
        }

        if ($warmPresentationItem) {
            $this->warmupPresentationItem($moduleName, $moduleRouteName, $model);
        }
    }

    abstract protected function getStaleFileCache(): \Unusualify\Modularous\Services\Cache\StaleFileCache;

    abstract protected function getUrlPresentationCacheStore(): \Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;

    /**
     * @deprecated Implement getUrlPresentationCacheStore() instead.
     */
    protected function getUrlKeyedStaleCache(): \Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache
    {
        $store = $this->getUrlPresentationCacheStore();
        if ($store instanceof \Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver) {
            return $store->underlyingFileCache();
        }

        throw new \RuntimeException('getUrlKeyedStaleCache() requires file driver.');
    }

    abstract protected function getPresentationCacheStore(): string;

    /**
     * Purge all presentationItem filesystem caches for a model (model-id stale + URL store).
     *
     * Clears both {@see StaleFileCache} and {@see UrlPresentationCacheStoreInterface} regardless of
     * the active `presentationItem.store` config (safe when migrating store modes).
     */
    public function purgePresentationItemForModel(
        Model $model,
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
        ?string $locale = null,
    ): int {
        if ($model->getKey() === null) {
            return 0;
        }

        $deleted = $this->getStaleFileCache()->forgetByRelation($model::class, $model->getKey());

        $moduleName ??= $this->getModuleNameFromModel($model);
        $moduleRouteName ??= $this->getModuleRouteNameFromModel($model);

        if ($moduleName && $moduleRouteName) {
            $deleted += $this->getStaleFileCache()->forgetByModuleRouteId(
                $moduleName,
                $moduleRouteName,
                $model->getKey(),
            );
        }

        $deleted += $this->purgeAllUrlPresentationForModel($model::class, $model->getKey(), $locale);

        ModularousCacheLogger::info('cache.invalidation.purge_presentation_item_for_model', [
            'model' => $model::class,
            'id' => $model->getKey(),
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'locale' => $locale,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Purge all presentationItem filesystem caches for a module route (no per-record iteration).
     */
    public function purgePresentationItemForModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        $deleted = $this->getUrlPresentationCacheStore()->forgetByModuleRoute($moduleName, $moduleRouteName);
        $deleted += $this->getStaleFileCache()->forgetByModuleRoute($moduleName, $moduleRouteName);

        ModularousCacheLogger::info('cache.invalidation.purge_presentation_item_for_module_route', [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    protected function forgetPresentationStaleForModel(Model $model): void
    {
        if ($model->getKey() === null) {
            return;
        }

        $store = $this->getPresentationCacheStore();

        if ($store === 'model') {
            $this->getStaleFileCache()->forgetByRelation($model::class, $model->getKey());
            $moduleName = $this->getModuleNameFromModel($model);
            $moduleRouteName = $this->getModuleRouteNameFromModel($model);
            if ($moduleName && $moduleRouteName) {
                $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $model->getKey());
            }
        }

        if ($store === 'url') {
            $this->forgetUrlStaleForModel($model::class, $model->getKey());
        }
    }

    protected function forgetStaleFilesByRelation(string $modelClass, int|string $id): void
    {
        if ($this->getPresentationCacheStore() === 'model') {
            $this->getStaleFileCache()->forgetByRelation($modelClass, $id);
        }

        if ($this->getPresentationCacheStore() === 'url') {
            $this->forgetUrlStaleForModel($modelClass, $id);
        }
    }

    protected function forgetStaleFilesByModuleRouteId(string $moduleName, string $moduleRouteName, int|string $id): void
    {
        $this->getStaleFileCache()->forgetByModuleRouteId($moduleName, $moduleRouteName, $id);
    }

    protected function forgetUrlStaleByLocalePath(string $locale, string $normalizedPath): int
    {
        return $this->getUrlPresentationCacheStore()->forgetPathVariants($locale, $normalizedPath);
    }

    protected function forgetUrlStaleForModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        if ($this->getPresentationCacheStore() !== 'url') {
            return 0;
        }

        return $this->getUrlPresentationCacheStore()->forgetByModuleRoute($moduleName, $moduleRouteName);
    }

    protected function forgetUrlStaleForModel(string $modelClass, int|string $id): int
    {
        if ($this->getPresentationCacheStore() !== 'url') {
            return 0;
        }

        return $this->purgeAllUrlPresentationForModel($modelClass, $id);
    }

    protected function purgeAllUrlPresentationForModel(string $modelClass, int|string $id, ?string $locale = null): int
    {
        if ($locale !== null && $locale !== '') {
            return $this->purgeUrlPresentationPathVariantsForModel($modelClass, $id, $locale);
        }

        $deleted = $this->getUrlPresentationCacheStore()->forgetByRelation($modelClass, $id);

        $deleted += $this->purgeUrlPresentationPathVariantsForModel($modelClass, $id);

        return $deleted;
    }

    protected function purgeUrlPresentationPathVariantsForModel(
        string $modelClass,
        int|string $id,
        ?string $locale = null,
    ): int {
        if (! class_exists(\Modules\Cms\Entities\UrlRoute::class)) {
            return 0;
        }

        try {
            $model = new $modelClass;
            $query = \Modules\Cms\Entities\UrlRoute::query()
                ->where('urlable_type', $model->getMorphClass())
                ->where('urlable_id', $id)
                ->where('kind', \Modules\Cms\Entities\UrlRoute::KIND_PAGE_PUBLIC);

            if ($locale !== null && $locale !== '') {
                $query->where('locale', $locale);
            }

            $rows = $query->get(['locale', 'normalized_path']);
        } catch (\Throwable) {
            return 0;
        }

        $deleted = 0;

        foreach ($rows as $row) {
            $deleted += $this->forgetUrlStaleByLocalePath((string) $row->locale, (string) $row->normalized_path);
        }

        return $deleted;
    }
}

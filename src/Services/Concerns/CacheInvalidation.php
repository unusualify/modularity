<?php

namespace Unusualify\Modularous\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
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
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: false))->flush();

            return true;
        }

        // Fallback: invalidate by pattern using Redis (only works without tags)
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        return $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:*") > 0;
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

        // dd($tag);

        try {
            $this->getStore()->tags([$tag])->flush();

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
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: false))->flush();

            return;
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:formattedItem:{$id}:*");
    }

    public function invalidateFormItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        if ($this->usesTags()) {
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: false))->flush();
        }

        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        $this->invalidateByPattern("{$this->getPrefix()}:{$moduleName}:{$moduleRouteName}:formItem:{$id}:*");
    }

    /**
     * Invalidate all caches related to a model.
     */
    public function invalidateForModel(Model $model, $types = [], $options = []): void
    {
        $warmup = isset($options['warmup']) ? $options['warmup'] : true;

        $moduleName = $this->getModuleNameFromModel($model);
        $moduleRouteName = $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        if ($this->usesTags()) {
            // With tags, flush the route-specific tag
            $this->getStore()->tags($this->getModuleRouteTags($moduleName, $moduleRouteName, onlyRoute: false))->flush();

            return;
        }

        $newlyCreated = $model->wasRecentlyCreated;

        // // Without tags, invalidate specific patterns
        // $recordKey = $this->generateRecordKey($module, $routeName, $model->getKey());
        // $this->forget($recordKey, $module, $routeName);

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

        try {
            // if laravel model is not new created, warmUp cache
            if (! $newlyCreated && $warmup) {
                $this->warmupByModel($model);
            }
        } catch (\Exception $e) {
            logger()->error("Failed to warm up caches for model {$model->getKey()}: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
        }

        // if(($rewarmItemEnabled = $this->isEnabled($module, $routeName, 'rewarmItem')) && $rewarmItemEnabled) {
        //     $this->rewarmItemCache($module, $routeName, $model->getKey());
        // }
    }
}

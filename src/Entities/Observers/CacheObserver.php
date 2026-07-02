<?php

namespace Unusualify\Modularity\Entities\Observers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;
use Unusualify\Modularity\Facades\RelationshipGraph;
use Unusualify\Modularity\Traits\ModularModel;

class CacheObserver
{
    use ModularModel;

    /**
     * Track models being invalidated to prevent infinite loops.
     */
    protected static array $invalidating = [];

    /**
     * Handle the Model "created" event.
     *
     * When a new model is created, we need to invalidate:
     * - All count caches (totals have changed)
     * - All index/list caches (new item in lists)
     * - Dependent module caches
     */
    public function created(Model $model): void
    {
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        if (ModularityCache::isEnabled($this->getModuleNameFromModel($model), $this->getModuleRouteNameFromModel($model))) {
            ModularityCache::invalidateForModel($model, options: ['warmup' => false]);
        }

        $this->invalidateDependentModules($model);
    }

    /**
     * Handle the Model "updated" event.
     *
     * When a model is updated, we need to invalidate:
     * - The specific record cache
     * - All index/list caches (item data may have changed)
     * - All count caches (counts may depend on any field: status, type, category, etc.)
     * - Dependent module caches
     */
    public function saved(Model $model): void
    {
        // dump('CacheObserver: saved');
        if ($model->newlyCreated) {
            return;
        }
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        if (ModularityCache::isEnabled($this->getModuleNameFromModel($model), $this->getModuleRouteNameFromModel($model))) {
            $clone = clone $model;
            $clone->refresh();
            ModularityCache::invalidateForModel($clone);
        }

        // Invalidate caches of modules that depend on this model
        $this->invalidateDependentModules($model);
    }

    /**
     * Handle the Model "deleted" event.
     *
     * When a model is soft-deleted, we need to invalidate:
     * - The specific record cache
     * - All count caches (totals and trash count changed)
     * - All index/list caches
     * - Dependent module caches
     */
    public function deleted(Model $model): void
    {
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        if (ModularityCache::isEnabled($this->getModuleNameFromModel($model), $this->getModuleRouteNameFromModel($model))) {
            ModularityCache::invalidateForModel($model);
        }

        $this->invalidateDependentModules($model);
    }

    /**
     * Handle the Model "restored" event.
     *
     * When a model is restored from trash, we need to invalidate:
     * - The specific record cache
     * - All count caches (totals and trash count changed)
     * - All index/list caches
     * - Dependent module caches
     */
    public function restored(Model $model): void
    {
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        if (ModularityCache::isEnabled($this->getModuleNameFromModel($model), $this->getModuleRouteNameFromModel($model))) {
            ModularityCache::invalidateForModel($model);
        }
        $this->invalidateDependentModules($model);
    }

    /**
     * Handle the Model "forceDeleted" event.
     *
     * When a model is permanently deleted, we need to invalidate:
     * - All caches for the module
     * - Dependent module caches
     */
    public function forceDeleted(Model $model): void
    {
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        if (ModularityCache::isEnabled($this->getModuleNameFromModel($model), $this->getModuleRouteNameFromModel($model))) {
            ModularityCache::invalidateForModel($model);
        }
        $this->invalidateDependentModules($model);
    }

    /**
     * Invalidate caches for submodules that depend on this model.
     * Uses granular tag-based invalidation when possible.
     *
     * Instead of invalidating ALL caches for a submodule, this method
     * only invalidates caches that are tagged with this specific model:id.
     */
    protected function invalidateDependentModules(Model $model): void
    {
        // Prevent infinite loops
        $modelKey = get_class($model) . ':' . $model->getKey();
        if (isset(static::$invalidating[$modelKey])) {
            return;
        }

        static::$invalidating[$modelKey] = true;

        try {
            // Use granular invalidation by relationship tag
            // This only clears caches that reference this specific model:id
            $invalidated = ModularityCache::invalidateByRelatedModel(
                get_class($model),
                $model->getKey()
            );

            $shouldWarmDependentModules = method_exists($model, 'shouldWarmDependentModules') ? $model->shouldWarmDependentModules() : true;

            // If granular invalidation failed (tags not supported), fall back to full invalidation
            if (! $invalidated) {
                $dependents = $this->getCacheDependents($model);

                foreach ($dependents as $moduleData) {
                    $moduleName = null;
                    $moduleRouteName = null;
                    $types = [
                        'counts' => false,
                        'index' => false,
                        'record' => false,
                        'formItem' => true,
                        'formattedItem' => true,
                    ];
                    $selfModelClass = get_class($model);
                    $targetRelationshipName = null;
                    $isSelf = false;

                    if (Arr::isAssoc($moduleData)) {
                        $moduleName = $moduleData['moduleName'];
                        $moduleRouteName = $moduleData['moduleRouteName'];
                        $types = array_merge($types, $moduleData['types'] ?? []);
                        $selfModelClass = $moduleData['selfModelClass'] ?? $selfModelClass;
                        $targetRelationshipName = $moduleData['targetRelationshipName'] ?? $targetRelationshipName;
                        $isSelf = (bool) $moduleData['isSelf'] ?? false;
                    } else {
                        $moduleName = $moduleData[0];
                        $moduleRouteName = $moduleData[1];
                    }

                    $module = null;
                    if (! $moduleName || ! $moduleRouteName) {
                        continue;
                    } elseif (! Modularity::hasModule($moduleName) || ! ($module = Modularity::find($moduleName)) || ! $module->hasRoute($moduleRouteName) || ! $module->isEnabledRoute($moduleRouteName)) {
                        continue;
                    }

                    if (ModularityCache::isEnabled($moduleName, $moduleRouteName)) {
                        // if($types['counts']) {
                        //     ModularityCache::invalidateCount($moduleName, $moduleRouteName);
                        // }
                        // if($types['index']) {
                        //     ModularityCache::invalidateIndex($moduleName, $moduleRouteName);
                        // }

                        if ($targetRelationshipName
                            && $selfModelClass
                            && @class_exists($selfModelClass)
                            && ($selfModel = new $selfModelClass)
                            && method_exists($selfModel, 'getEloquentRelationships')
                            && ($availableRelationships = $selfModel->getEloquentRelationships())
                            && isset($availableRelationships[$targetRelationshipName])
                            && ($selfModel = $selfModelClass::find($model->getKey()))
                            && $selfModel->getKey() == $model->getKey()
                        ) {
                            $targetRelationshipConfig = $availableRelationships[$targetRelationshipName];
                            $targetRelationshipTypeClass = $targetRelationshipConfig['relationship_class'];
                            $targetModelInstance = $module->getModel($moduleRouteName);

                            $target = $selfModel->{$targetRelationshipName};

                            if ($target instanceof Model && get_class($target) === get_class($targetModelInstance)) {

                                $this->invalidateItemCache($target, $moduleName, $moduleRouteName, $types, $shouldWarmDependentModules);
                            } elseif ($target instanceof Collection) {
                                foreach ($target as $item) {
                                    if ($item instanceof Model && get_class($item) === get_class($targetModelInstance)) {
                                        $this->invalidateItemCache($item, $moduleName, $moduleRouteName, $types, $shouldWarmDependentModules);
                                    }
                                }
                            }
                        } elseif ($isSelf) {
                            $targetModelInstance = $module->getModel($moduleRouteName);
                            $target = $targetModelInstance::find($model->getKey());

                            if ($target instanceof Model && get_class($target) === get_class($targetModelInstance)) {
                                $this->invalidateItemCache($target, $moduleName, $moduleRouteName, $types, $shouldWarmDependentModules);
                            }
                        }
                        // ModularityCache::invalidateModuleRoute($moduleName, $moduleRouteName);
                    }
                }
            }
        } finally {
            unset(static::$invalidating[$modelKey]);
        }
    }

    protected function invalidateItemCache(Model $item, string $moduleName, string $moduleRouteName, array $types, bool $warmup = true): void
    {
        ModularityCache::invalidateForModel($item, $types, options: ['warmup' => $warmup]);
    }

    /**
     * Get the list of submodules that depend on this model's cached data.
     * Uses the relationship graph for automatic discovery.
     * Returns submodule names (which correspond to model class basenames).
     */
    protected function getCacheDependents(Model $model): array
    {
        $dependents = [];
        $modelClass = get_class($model);

        // 1. Get dependents from the relationship graph (automatic discovery)
        // Returns submodule names that have relationships to this model
        $graphDependents = RelationshipGraph::getAffectedModuleRoutes($modelClass);
        // $dependents = array_merge($dependents, $graphDependents);

        // 2. Check if model defines its dependents via method (manual override)
        if (method_exists($model, 'getCacheDependents')) {
            $dependents = array_merge($dependents, $model->getCacheDependents());
        }
        // 3. Check if model has dependents property
        elseif (property_exists($model, 'cacheDependents') && is_array($model->cacheDependents)) {
            $dependents = array_merge($dependents, $model->cacheDependents);
        }

        // 4. Check global config for this model class (explicit config)
        $configDependents = $this->getConfigDependents($modelClass);
        $dependents = array_merge($dependents, $configDependents);

        // 5. Get dependents by table name (for pivot tables without model classes)
        $tableName = $model->getTable();
        $tableDependents = RelationshipGraph::getAffectedModuleRoutesByTable($tableName);

        // $dependents = array_merge($dependents, $tableDependents);

        // Remove the current model's own submodule from dependents to avoid double invalidation
        // Submodule name = model class basename
        $ownModuleName = $this->getModuleNameFromModel($model);
        $ownModuleRouteName = $this->getModuleRouteNameFromModel($model);

        // $dependents = array_filter($dependents, fn ($moduleData) => !($moduleData[0] == $ownModuleName && $moduleData[1] == $ownModuleRouteName));
        $dependents = array_filter($dependents, fn ($moduleData) => ! ($moduleData['moduleName'] == $ownModuleName && $moduleData['moduleRouteName'] == $ownModuleRouteName));

        return array_unique($dependents, SORT_REGULAR);
    }

    /**
     * Get dependents from global config for a model class.
     * Uses full class names only.
     *
     * Config example:
     * - 'Modules\Company\Entities\Company' => ['press_release']
     */
    protected function getConfigDependents(string $modelClass): array
    {
        $dependencies = config('modularity.cache.dependencies', []);

        if (empty($dependencies) || ! isset($dependencies[$modelClass])) {
            return [];
        }

        $configDependents = $dependencies[$modelClass] ?? [];
        $dependents = [];

        foreach ($configDependents as $dependent) {
            $moduleName = null;
            $moduleRouteName = null;
            $selfModelClass = null;
            $targetRelationshipName = null;
            $isSelf = false;
            $types = [
                'counts' => true,
                'index' => true,
                'record' => true,
                'formItem' => true,
                'formattedItem' => true,
            ];
            if (Arr::isAssoc($dependent)) {
                $moduleName = $dependent['moduleName'];
                $moduleRouteName = $dependent['moduleRouteName'] ?? $dependent['moduleName'];
                $types = array_merge($types, Arr::only($dependent['types'], ['counts', 'index']));
                $selfModelClass = $dependent['selfModelClass'] ?? $modelClass;
                $targetRelationshipName = $dependent['targetRelationshipName'] ?? null;
                $isSelf = (bool) ($dependent['isSelf'] ?? false);
            } else {
                $moduleName = $dependent[0] ?? null;
                $moduleRouteName = $dependent[1] ?? $moduleName ?? null;
            }

            if ($moduleName
                && $moduleRouteName
                && ($moduleRouteName = Str::studly($moduleRouteName))
                && Modularity::hasModule($moduleName)
                && ($module = Modularity::find($moduleName))
                && $module->hasRoute($moduleRouteName)
                && $module->isEnabledRoute($moduleRouteName)
            ) {
                $dependents[] = [
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                    'types' => $types,
                    'selfModelClass' => $selfModelClass,
                    'targetRelationshipName' => $targetRelationshipName,
                    'isSelf' => $isSelf,
                ];
            }
        }

        return $dependents;
    }

    /**
     * Check if cache invalidation should be performed for this model.
     */
    protected function shouldInvalidate(Model $model): bool
    {
        // Check if caching is enabled for this module
        $moduleName = $this->getModuleNameFromModel($model);
        $moduleRouteName = $this->getModuleRouteNameFromModel($model);

        // Check if caching is enabled globally
        if (! ModularityCache::isEnabled()) {
            return false;
        }

        // Check if the model has caching disabled
        if (method_exists($model, 'shouldCacheInvalidate') && ! $model->shouldCacheInvalidate()) {
            return false;
        }

        // Even if this module doesn't have caching, we should still check for dependents
        // So return true if either this module has caching OR there are dependents
        $hasDependents = ! empty($this->getCacheDependents($model));

        return ModularityCache::isEnabled($moduleName, $moduleRouteName) || $hasDependents;
    }
}

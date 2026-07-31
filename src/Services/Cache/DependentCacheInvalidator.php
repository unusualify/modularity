<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;
use Unusualify\Modularous\Traits\ModularModel;

class DependentCacheInvalidator
{
    use ModularModel;

    /**
     * @var array<string, true>
     */
    protected static array $invalidating = [];

    public function invalidateForModel(Model $model): void
    {
        $modelKey = get_class($model) . ':' . $model->getKey();
        if (isset(static::$invalidating[$modelKey])) {
            return;
        }

        static::$invalidating[$modelKey] = true;

        try {
            $this->runInvalidation($model);
        } finally {
            unset(static::$invalidating[$modelKey]);
        }
    }

    public function hasDependents(Model $model): bool
    {
        return $this->getCacheDependents($model) !== [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getConfigDependentsForModelClass(string $modelClass): array
    {
        return $this->getConfigDependents($modelClass);
    }

    protected function runInvalidation(Model $model): void
    {
        $shouldWarmDependentModules = method_exists($model, 'shouldWarmDependentModules')
            ? $model->shouldWarmDependentModules()
            : true;

        $dependents = $this->getCacheDependents($model);

        ModularousCacheLogger::info('cache.observer.invalidate_dependents.start', [
            'sourceModel' => get_class($model),
            'sourceId' => $model->getKey(),
            'shouldWarmDependentModules' => $shouldWarmDependentModules,
            'dependentCount' => count($dependents),
        ]);

        foreach ($dependents as $moduleData) {
            $this->processDependent($model, $moduleData, $shouldWarmDependentModules);
        }
    }

    /**
     * @param array<string, mixed>|list<mixed> $moduleData
     */
    protected function processDependent(Model $model, array $moduleData, bool $shouldWarmDependentModules): void
    {
        $types = [
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => false,
        ];
        $selfModelClass = get_class($model);
        $targetRelationshipName = null;
        $isSelf = false;
        $shouldWarmDependent = true;
        $moduleName = null;
        $moduleRouteName = null;

        if (Arr::isAssoc($moduleData)) {
            $moduleName = $moduleData['moduleName'];
            $moduleRouteName = $moduleData['moduleRouteName'];
            $types = array_merge($types, $moduleData['types'] ?? []);
            $selfModelClass = $moduleData['selfModelClass'] ?? $selfModelClass;
            $targetRelationshipName = $moduleData['targetRelationshipName'] ?? $targetRelationshipName;
            $isSelf = (bool) ($moduleData['isSelf'] ?? false);
            $shouldWarmDependent = ($moduleData['shouldWarm'] ?? true) !== false;
        } else {
            $moduleName = $moduleData[0];
            $moduleRouteName = $moduleData[1];
        }

        $shouldWarm = $shouldWarmDependentModules && $shouldWarmDependent;

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        if (
            ! Modularous::hasModule($moduleName)
            || ! ($module = Modularous::find($moduleName))
            || ! $module->hasRoute($moduleRouteName)
            || ! $module->isEnabledRoute($moduleRouteName)
        ) {
            return;
        }

        if (! ModularousCache::isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $types = $this->filterTypesForAutoInvalidation($moduleName, $moduleRouteName, $types, $shouldWarm);

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
            $targetModelInstance = $module->getModel($moduleRouteName);
            $target = $selfModel->{$targetRelationshipName};
            $items = [];

            if ($target instanceof Model) {
                if ($target::class === $targetModelInstance::class) {
                    $items[] = $target;
                }
            } elseif ($target instanceof EloquentCollection || $target instanceof Collection) {
                foreach ($target as $item) {
                    if ($item instanceof Model && $item::class === $targetModelInstance::class) {
                        $items[] = $item;
                    }
                }
            }

            ModularousCacheLogger::info('cache.observer.invalidate_dependent.relationship', [
                'sourceModel' => get_class($model),
                'sourceId' => $model->getKey(),
                'dependentModule' => $moduleName,
                'dependentRoute' => $moduleRouteName,
                'targetRelationshipName' => $targetRelationshipName,
                'types' => $types,
                'shouldWarm' => $shouldWarm,
                'itemsFound' => count($items),
            ]);

            $this->invalidateTargetRelationshipItemCaches(
                $items,
                $moduleName,
                $moduleRouteName,
                $types,
                $shouldWarm,
            );
        } elseif ($isSelf) {
            $targetModelInstance = $module->getModel($moduleRouteName);
            $target = $targetModelInstance::find($model->getKey());

            if ($target instanceof Model && get_class($target) === get_class($targetModelInstance)) {
                $this->invalidateItemCache($target, $moduleName, $moduleRouteName, $types, $shouldWarm);
            }
        } elseif (! $targetRelationshipName) {
            $this->invalidateAllItemCaches($moduleName, $moduleRouteName, $types, $shouldWarm);
        }
    }

    /**
     * @param list<Model> $items
     * @param array<string, bool> $types
     */
    protected function invalidateTargetRelationshipItemCaches(
        array $items,
        string $moduleName,
        string $moduleRouteName,
        array $types,
        bool $shouldWarm,
    ): void {
        if ($items === []) {
            ModularousCacheLogger::info('cache.observer.warmup_relationship_items.skip', [
                'dependentModule' => $moduleName,
                'dependentRoute' => $moduleRouteName,
                'reason' => 'no_items',
                'shouldWarm' => $shouldWarm,
            ]);

            return;
        }

        ModularousCacheLogger::info('cache.observer.warmup_relationship_items.start', [
            'dependentModule' => $moduleName,
            'dependentRoute' => $moduleRouteName,
            'types' => $types,
            'shouldWarm' => $shouldWarm,
            'itemCount' => count($items),
        ]);

        if (! $shouldWarm) {
            ModularousCacheLogger::info('cache.observer.warmup_relationship_items.skip', [
                'dependentModule' => $moduleName,
                'dependentRoute' => $moduleRouteName,
                'reason' => 'should_warm_false',
                'itemCount' => count($items),
            ]);

            return;
        }

        foreach ($items as $item) {
            ModularousCacheLogger::info('cache.observer.warmup_relationship_item', [
                'dependentModule' => $moduleName,
                'dependentRoute' => $moduleRouteName,
                'targetModel' => get_class($item),
                'targetId' => $item->getKey(),
                'types' => $types,
            ]);

            $this->warmItemCaches($item, $moduleName, $moduleRouteName, $types);
        }
    }

    /**
     * @param array<string, bool> $types
     */
    protected function invalidateItemCache(
        Model $item,
        string $moduleName,
        string $moduleRouteName,
        array $types,
        bool $warmup = true,
    ): void {
        if ($warmup && $this->shouldRefreshOnly($types)) {
            ModularousCache::refreshModelCaches($item, $types, [
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ]);

            return;
        }

        ModularousCache::invalidateForModel($item, $types, [
            'warmup' => $warmup,
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'skipInvalidation' => $warmup && $this->shouldRefreshOnly($types),
        ]);
    }

    /**
     * @param array<string, bool> $types
     */
    protected function warmItemCaches(Model $item, string $moduleName, string $moduleRouteName, array $types): void
    {
        if (count(array_filter($types)) === 0) {
            return;
        }

        if ($this->shouldDispatchPresentationWarmupAsync($moduleName, $moduleRouteName, $types)) {
            WarmPresentationItemJob::dispatch($item, $moduleName, $moduleRouteName);

            return;
        }

        if ($this->shouldRefreshOnly($types)) {
            ModularousCache::refreshModelCaches($item, $types, [
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ]);

            return;
        }

        ModularousCache::warmupModelCaches($item, $types, $moduleName, $moduleRouteName);
    }

    /**
     * @param array<string, bool> $types
     */
    protected function invalidateAllItemCaches(
        string $moduleName,
        string $moduleRouteName,
        array $types,
        bool $shouldWarmDependentModules = true,
    ): void {
        ModularousCache::invalidateAllItemCaches(
            $moduleName,
            $moduleRouteName,
            $types,
            $shouldWarmDependentModules,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function getCacheDependents(Model $model): array
    {
        $dependents = [];
        $modelClass = get_class($model);

        if (method_exists($model, 'getCacheDependents')) {
            $dependents = array_merge($dependents, $model->getCacheDependents());
        } elseif (property_exists($model, 'cacheDependents') && is_array($model->cacheDependents)) {
            $dependents = array_merge($dependents, $model->cacheDependents);
        }

        $dependents = array_merge($dependents, $this->getConfigDependents($modelClass));

        $ownModuleName = $this->getModuleNameFromModel($model);
        $ownModuleRouteName = $this->getModuleRouteNameFromModel($model);

        $dependents = array_filter(
            $dependents,
            fn ($moduleData) => ! ($moduleData['moduleName'] == $ownModuleName && $moduleData['moduleRouteName'] == $ownModuleRouteName),
        );

        return array_values(array_unique($dependents, SORT_REGULAR));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function getConfigDependents(string $modelClass): array
    {
        $dependencies = config('modularous.cache.dependencies', []);

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
            $shouldWarm = true;
            $types = [
                'counts' => false,
                'index' => false,
                'record' => false,
                'formItem' => false,
                'formattedItem' => false,
                'presentationItem' => false,
            ];

            if (Arr::isAssoc($dependent)) {
                $moduleName = $dependent['moduleName'];
                $moduleRouteName = $dependent['moduleRouteName'] ?? $dependent['moduleName'];
                if (isset($dependent['types']) && is_array($dependent['types'])) {
                    $types = array_merge($types, $dependent['types']);
                }
                $selfModelClass = $dependent['selfModelClass'] ?? $modelClass;
                $targetRelationshipName = $dependent['targetRelationshipName'] ?? null;
                $isSelf = (bool) ($dependent['isSelf'] ?? false);
                $shouldWarm = ($dependent['shouldWarm'] ?? true) !== false;
            } else {
                $moduleName = $dependent[0] ?? null;
                $moduleRouteName = $dependent[1] ?? $moduleName ?? null;
            }

            if ($moduleName
                && $moduleRouteName
                && ($moduleRouteName = Str::studly($moduleRouteName))
                && Modularous::hasModule($moduleName)
                && ($module = Modularous::find($moduleName))
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
                    'shouldWarm' => $shouldWarm,
                ];
            }
        }

        return $dependents;
    }

    /**
     * @param array<string, bool> $types
     * @return array<string, bool>
     */
    protected function filterTypesForAutoInvalidation(
        string $moduleName,
        string $moduleRouteName,
        array $types,
        bool $shouldWarm,
    ): array {
        if (! $shouldWarm) {
            return array_map(fn () => false, $types);
        }

        foreach ($types as $type => $enabled) {
            if ($enabled && ! ModularousCache::shouldAutoInvalidate($moduleName, $moduleRouteName, $type)) {
                $types[$type] = false;
            }
        }

        return $types;
    }

    /**
     * @param array<string, bool> $types
     */
    protected function shouldRefreshOnly(array $types): bool
    {
        $presentationOnly = ($types['presentationItem'] ?? false)
            && ! ($types['counts'] ?? false)
            && ! ($types['index'] ?? false)
            && ! ($types['record'] ?? false)
            && ! ($types['formItem'] ?? false)
            && ! ($types['formattedItem'] ?? false);

        return $presentationOnly;
    }

    /**
     * @param array<string, bool> $types
     */
    protected function shouldDispatchPresentationWarmupAsync(
        string $moduleName,
        string $moduleRouteName,
        array $types,
    ): bool {
        if (! ($types['presentationItem'] ?? false)) {
            return false;
        }

        return $this->shouldUseAsyncQueue();
    }

    protected function shouldUseAsyncQueue(): bool
    {
        if (! (bool) config('modularous.cache.observer.queue', true)) {
            return false;
        }

        $connection = config('modularous.cache.queue.connection')
            ?? config('queue.default', 'sync');

        return $connection !== 'sync';
    }
}

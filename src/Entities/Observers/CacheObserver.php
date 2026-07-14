<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\InvalidateDependentCachesJob;
use Unusualify\Modularous\Jobs\Cache\InvalidateModelCacheJob;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;
use Unusualify\Modularous\Support\ModularousCacheLogger;
use Unusualify\Modularous\Traits\ModularModel;

class CacheObserver
{
    use ModularModel;

    public function created(Model $model): void
    {
        $this->handleModelEvent($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->handleModelEvent($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->handleModelEvent($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->handleModelEvent($model, 'restored');
    }

    public function forceDeleted(Model $model): void
    {
        $this->handleModelEvent($model, 'forceDeleted');
    }

    protected function handleModelEvent(Model $model, string $event): void
    {
        if (! $this->shouldInvalidate($model)) {
            return;
        }

        ModularousCacheLogger::info('cache.observer.event', [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'event' => $event,
            'async' => $this->shouldDispatchAsync(),
        ]);

        if ($this->shouldDispatchAsync()) {
            InvalidateModelCacheJob::dispatch($model, $event);
            InvalidateDependentCachesJob::dispatch($model);

            return;
        }

        $this->processModelInvalidationSync($model, $event);
        $this->invalidateDependentModules($model);
    }

    protected function invalidateDependentModules(Model $model): void
    {
        app(DependentCacheInvalidator::class)->invalidateForModel($model);
    }

    protected function processModelInvalidationSync(Model $model, string $event): void
    {
        ModularousCache::invalidateByRelatedModel(get_class($model), $model->getKey());

        $moduleName = $this->getModuleNameFromModel($model);
        $moduleRouteName = $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName || ! ModularousCache::isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $types = $this->resolveAutoInvalidationTypes($moduleName, $moduleRouteName, $event);

        if ($types === []) {
            return;
        }

        $workingModel = $model;
        if ($event === 'updated' && method_exists($model, 'refresh')) {
            $workingModel = clone $model;
            $workingModel->refresh();
        }

        $warmup = ! in_array($event, ['deleted', 'forceDeleted', 'created'], true);
        $options = [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'warmup' => $warmup,
        ];

        if ($warmup && $this->shouldRefreshOnly($types)) {
            ModularousCache::refreshModelCaches($workingModel, $types, $options);

            return;
        }

        if ($warmup && ($types['presentationItem'] ?? false) && $this->countEnabledTypes($types) === 1) {
            $options['skipInvalidation'] = true;
        }

        ModularousCache::invalidateForModel($workingModel, $types, $options);
    }

    /**
     * @return array<string, bool>
     */
    protected function resolveAutoInvalidationTypes(string $moduleName, string $moduleRouteName, string $event): array
    {
        $defaults = [
            'counts' => true,
            'index' => true,
            'record' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ];

        if (in_array($event, ['deleted', 'forceDeleted'], true)) {
            $resolved = [];
            foreach ($defaults as $type => $_) {
                if (ModularousCache::isEnabled($moduleName, $moduleRouteName, $type)) {
                    $resolved[$type] = true;
                }
            }

            return $resolved;
        }

        foreach ($defaults as $type => $enabled) {
            if ($enabled && ! ModularousCache::shouldAutoInvalidate($moduleName, $moduleRouteName, $type)) {
                $defaults[$type] = false;
            }
        }

        return array_filter($defaults);
    }

    /**
     * @param array<string, bool> $types
     */
    protected function shouldRefreshOnly(array $types): bool
    {
        return ($types['presentationItem'] ?? false)
            && ! ($types['counts'] ?? false)
            && ! ($types['index'] ?? false)
            && ! ($types['record'] ?? false)
            && ! ($types['formItem'] ?? false)
            && ! ($types['formattedItem'] ?? false);
    }

    /**
     * @param array<string, bool> $types
     */
    protected function countEnabledTypes(array $types): int
    {
        return count(array_filter($types));
    }

    protected function shouldDispatchAsync(): bool
    {
        if (! (bool) config('modularous.cache.observer.queue', true)) {
            return false;
        }

        $connection = config('modularous.cache.queue.connection')
            ?? config('modularous.cache.observer.queue_connection')
            ?? config('queue.default', 'sync');

        return $connection !== 'sync';
    }

    protected function shouldInvalidate(Model $model): bool
    {
        $moduleName = $this->getModuleNameFromModel($model);
        $moduleRouteName = $this->getModuleRouteNameFromModel($model);

        if (! ModularousCache::isEnabled()) {
            return false;
        }

        if (method_exists($model, 'shouldCacheInvalidate') && ! $model->shouldCacheInvalidate()) {
            return false;
        }

        $hasDependents = app(DependentCacheInvalidator::class)->hasDependents($model);

        return ModularousCache::isEnabled($moduleName, $moduleRouteName) || $hasDependents;
    }
}

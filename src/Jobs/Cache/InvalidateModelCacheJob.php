<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;
use Unusualify\Modularous\Traits\ModularModel;

final class InvalidateModelCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, ModularModel, Queueable, SerializesModels;

    public function __construct(
        public Model $model,
        public string $event = 'updated',
        public array $types = [],
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(): void
    {
        if (! $this->model->exists && ! in_array($this->event, ['deleted', 'forceDeleted'], true)) {
            $fresh = $this->model::query()->find($this->model->getKey());
            if ($fresh instanceof Model) {
                $this->model = $fresh;
            }
        }

        ModularousCacheLogger::info('cache.job.invalidate_model.start', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'event' => $this->event,
            'types' => $this->types,
        ]);

        ModularousCache::invalidateByRelatedModel(get_class($this->model), $this->model->getKey());

        $moduleName = $this->getModuleNameFromModel($this->model);
        $moduleRouteName = $this->getModuleRouteNameFromModel($this->model);

        if (! $moduleName || ! $moduleRouteName || ! ModularousCache::isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $types = $this->resolveTypes($moduleName, $moduleRouteName);

        if ($types === []) {
            return;
        }

        $warmup = ! in_array($this->event, ['deleted', 'forceDeleted', 'created'], true);
        $options = [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'warmup' => $warmup,
        ];

        if ($warmup && $this->shouldRefreshOnly($types)) {
            ModularousCache::refreshModelCaches($this->model, $types, $options);

            return;
        }

        if ($warmup && ($types['presentationItem'] ?? false) && $this->countEnabledTypes($types) === 1) {
            $options['skipInvalidation'] = true;
        }

        ModularousCache::invalidateForModel($this->model, $types, $options);
    }

    protected function modularousCacheOverlapKey(): string
    {
        $moduleName = $this->getModuleNameFromModel($this->model) ?? 'unknown';
        $moduleRouteName = $this->getModuleRouteNameFromModel($this->model) ?? 'unknown';

        return sprintf(
            'cache:invalidate:%s:%s:%s:%s',
            $moduleName,
            $moduleRouteName,
            get_class($this->model),
            (string) $this->model->getKey(),
        );
    }

    /**
     * @return array<string, bool>
     */
    protected function resolveTypes(string $moduleName, string $moduleRouteName): array
    {
        $defaults = [
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => false,
        ];

        $types = $this->types !== [] ? $this->types : $defaults;

        if (in_array($this->event, ['deleted', 'forceDeleted'], true)) {
            $resolved = [];
            foreach ($defaults as $type => $_) {
                if (ModularousCache::isEnabled($moduleName, $moduleRouteName, $type)) {
                    $resolved[$type] = true;
                }
            }

            return $resolved;
        }

        foreach ($types as $type => $enabled) {
            if ($enabled && ! ModularousCache::shouldAutoInvalidate($moduleName, $moduleRouteName, $type)) {
                $types[$type] = false;
            }
        }

        return array_filter($types);
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
}

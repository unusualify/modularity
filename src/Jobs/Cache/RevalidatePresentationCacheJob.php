<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class RevalidatePresentationCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    /**
     * @param array<int, string>|null $types
     * @param array<int, string>|null $locales
     */
    public function __construct(
        public string $moduleName,
        public string $moduleRouteName,
        public string $action,
        public ?int $recordId = null,
        public ?array $types = null,
        public ?array $locales = null,
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(): void
    {
        $moduleName = Str::studly($this->moduleName);
        $moduleRouteName = Str::studly($this->moduleRouteName);

        ModularousCacheLogger::info('cache.job.revalidate_presentation.start', [
            'module' => $moduleName,
            'route' => $moduleRouteName,
            'action' => $this->action,
            'recordId' => $this->recordId,
            'types' => $this->types,
            'locales' => $this->locales,
        ]);

        $types = $this->resolveTypes($moduleName, $moduleRouteName);

        if ($this->recordId !== null) {
            $model = $this->resolveModel($moduleName, $moduleRouteName, $this->recordId);
            if ($model === null) {
                ModularousCacheLogger::info('cache.job.revalidate_presentation.skip', [
                    'reason' => 'model_not_found',
                    'recordId' => $this->recordId,
                ]);

                return;
            }

            $this->revalidateForModel($model, $moduleName, $moduleRouteName, $types);

            return;
        }

        $this->revalidateForRoute($moduleName, $moduleRouteName, $types);
    }

    /**
     * @param array<string, bool> $types
     */
    protected function revalidateForModel(Model $model, string $moduleName, string $moduleRouteName, array $types): void
    {
        if (in_array($this->action, ['purge', 'both'], true)) {
            ModularousCache::purgeModelCacheTypes($model, $types, $moduleName, $moduleRouteName);
            ModularousCache::invalidateByRelatedModel($model::class, $model->getKey());
        }

        if (in_array($this->action, ['warm', 'both'], true)) {
            if ($types['presentationItem'] ?? false) {
                WarmPresentationItemJob::dispatch($model, $moduleName, $moduleRouteName);
            } else {
                ModularousCache::warmupModelCaches($model, $types, $moduleName, $moduleRouteName);
            }
        }
    }

    /**
     * @param array<string, bool> $types
     */
    protected function revalidateForRoute(string $moduleName, string $moduleRouteName, array $types): void
    {
        if (in_array($this->action, ['purge', 'both'], true)) {
            ModularousCache::invalidateAllItemCaches($moduleName, $moduleRouteName, $types, shouldWarmDependentModules: false);
        }

        if (in_array($this->action, ['warm', 'both'], true)) {
            ModularousCache::warmModuleRouteCaches($moduleName, $moduleRouteName, $types);
        }
    }

    /**
     * @return array<string, bool>
     */
    protected function resolveTypes(string $moduleName, string $moduleRouteName): array
    {
        if ($this->types !== null && $this->types !== []) {
            $resolved = array_fill_keys(
                ['counts', 'index', 'record', 'formItem', 'formattedItem', 'presentationItem'],
                false,
            );
            foreach ($this->types as $type) {
                if (isset($resolved[$type])) {
                    $resolved[$type] = true;
                }
            }

            return $resolved;
        }

        return [
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => true,
        ];
    }

    protected function resolveModel(string $moduleName, string $moduleRouteName, int $recordId): ?Model
    {
        $module = Modularous::find($moduleName);
        if ($module === null || ! $module->hasRoute($moduleRouteName)) {
            return null;
        }

        $modelClass = get_class($module->getModel($moduleRouteName));

        $model = $modelClass::query()->find($recordId);

        return $model instanceof Model ? $model : null;
    }

    protected function modularousCacheOverlapKey(): string
    {
        return sprintf(
            'cache:revalidate:%s:%s:%s',
            Str::studly($this->moduleName),
            Str::studly($this->moduleRouteName),
            $this->recordId ?? 'all',
        );
    }
}

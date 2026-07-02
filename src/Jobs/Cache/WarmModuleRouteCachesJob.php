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

final class WarmModuleRouteCachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    /**
     * @param array<string, bool> $types
     */
    public function __construct(
        public string $moduleName,
        public string $moduleRouteName,
        public array $types = [],
        public int $chunkSize = 100,
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(): void
    {
        $moduleName = Str::studly($this->moduleName);
        $moduleRouteName = Str::studly($this->moduleRouteName);

        ModularousCacheLogger::info('cache.job.warm_module_route.start', [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'types' => $this->types,
            'chunkSize' => $this->chunkSize,
        ]);

        if (! ModularousCache::isEnabled($moduleName, $moduleRouteName)) {
            return;
        }

        $module = Modularous::find($moduleName);
        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        $types = $this->resolveTypes($moduleName, $moduleRouteName);

        if (($types['counts'] ?? false) && ModularousCache::isEnabled($moduleName, $moduleRouteName, 'counts')) {
            ModularousCache::warmupModuleRouteCacheCounts($moduleName, $moduleRouteName);
        }

        $model = $module->getModel($moduleRouteName);
        $modelClass = get_class($model);

        if ($module->isSingleton($moduleRouteName)) {
            $record = $modelClass::query()->select('id')->first();
            if ($record !== null && $record->getKey() !== null) {
                $this->warmRecord($modelClass, $record->getKey(), $moduleName, $moduleRouteName, $types);
            }

            return;
        }

        $modelClass::query()
            ->select('id')
            ->orderBy('id')
            ->chunk($this->chunkSize, function ($records) use ($modelClass, $moduleName, $moduleRouteName, $types) {
                foreach ($records as $record) {
                    if ($record->getKey() === null) {
                        continue;
                    }

                    if (($types['presentationItem'] ?? false) && $this->shouldDispatchPresentationAsync()) {
                        $item = $modelClass::find($record->getKey());
                        if ($item instanceof Model) {
                            WarmPresentationItemJob::dispatch($item, $moduleName, $moduleRouteName);
                        }

                        continue;
                    }

                    $this->warmRecord($modelClass, $record->getKey(), $moduleName, $moduleRouteName, $types);
                }
            });
    }

    protected function modularousCacheOverlapKey(): string
    {
        return sprintf(
            'cache:warm-route:%s:%s',
            Str::studly($this->moduleName),
            Str::studly($this->moduleRouteName),
        );
    }

    /**
     * @param array<string, bool> $types
     */
    protected function warmRecord(
        string $modelClass,
        mixed $id,
        string $moduleName,
        string $moduleRouteName,
        array $types,
    ): void {
        $model = $modelClass::find($id);
        if (! $model instanceof Model) {
            return;
        }

        if ($this->shouldRefreshOnly($types)) {
            ModularousCache::refreshModelCaches($model, $types, [
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
            ]);

            return;
        }

        ModularousCache::warmupModelCaches($model, $types, $moduleName, $moduleRouteName);
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
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ];

        $types = $this->types !== [] ? $this->types : $defaults;

        foreach ($types as $type => $enabled) {
            if ($enabled && ! ModularousCache::isEnabled($moduleName, $moduleRouteName, $type)) {
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
        return ($types['presentationItem'] ?? false)
            && ! ($types['counts'] ?? false)
            && ! ($types['index'] ?? false)
            && ! ($types['record'] ?? false)
            && ! ($types['formItem'] ?? false)
            && ! ($types['formattedItem'] ?? false);
    }

    protected function shouldDispatchPresentationAsync(): bool
    {
        if (! (bool) config('modularous.cache.observer.queue', true)) {
            return false;
        }

        $connection = config('modularous.cache.queue.connection')
            ?? config('queue.default', 'sync');

        return $connection !== 'sync';
    }
}

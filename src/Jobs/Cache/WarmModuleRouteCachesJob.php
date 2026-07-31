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
use Throwable;
use Unusualify\Modularous\Events\Cache\CacheWarmProgress;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\BuildsCacheWarmBroadcastToast;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class WarmModuleRouteCachesJob implements ShouldQueue
{
    use BuildsCacheWarmBroadcastToast, Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    /**
     * @param array<string, bool> $types
     */
    public function __construct(
        public string $moduleName,
        public string $moduleRouteName,
        public array $types = [],
        public int $chunkSize = 100,
        public ?int $initiatorUserId = null,
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
            'initiatorUserId' => $this->initiatorUserId,
        ]);

        $detail = $this->cacheWarmDetail($moduleName, $moduleRouteName);

        try {
            if (! ModularousCache::isEnabled($moduleName, $moduleRouteName)) {
                $this->broadcastWarm(CacheWarmProgress::STATUS_SKIPPED, [
                    'job' => 'WarmModuleRouteCachesJob',
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                    'skipped' => true,
                    'reason' => 'cache_disabled',
                    'toast' => $this->cacheWarmToast(
                        CacheWarmProgress::STATUS_SKIPPED,
                        __('messages.resource-cache.warm-toast.module-route.title'),
                        __('messages.resource-cache.warm-toast.module-route.completed-skipped', [
                            'reason' => __('messages.resource-cache.warm-toast.reason.cache_disabled'),
                        ]),
                        $detail,
                    ),
                ]);

                return;
            }

            $module = Modularous::find($moduleName);
            if (! $module || ! $module->hasRoute($moduleRouteName)) {
                $this->broadcastWarm(CacheWarmProgress::STATUS_SKIPPED, [
                    'job' => 'WarmModuleRouteCachesJob',
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                    'skipped' => true,
                    'reason' => 'module_or_route_missing',
                    'toast' => $this->cacheWarmToast(
                        CacheWarmProgress::STATUS_SKIPPED,
                        __('messages.resource-cache.warm-toast.module-route.title'),
                        __('messages.resource-cache.warm-toast.module-route.completed-skipped', [
                            'reason' => __('messages.resource-cache.warm-toast.reason.module_or_route_missing'),
                        ]),
                        $detail,
                    ),
                ]);

                return;
            }

            $this->broadcastWarm(CacheWarmProgress::STATUS_STARTED, [
                'job' => 'WarmModuleRouteCachesJob',
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
                'toast' => $this->cacheWarmToast(
                    CacheWarmProgress::STATUS_STARTED,
                    __('messages.resource-cache.warm-toast.module-route.title'),
                    __('messages.resource-cache.warm-toast.module-route.started'),
                    $detail,
                ),
            ]);

            $types = $this->resolveTypes($moduleName, $moduleRouteName);

            if (($types['counts'] ?? false) && ModularousCache::isEnabled($moduleName, $moduleRouteName, 'counts')) {
                ModularousCache::warmupModuleRouteCacheCounts($moduleName, $moduleRouteName);
            }

            $model = $module->getModel($moduleRouteName);
            $modelClass = get_class($model);
            $processed = 0;

            if ($module->isSingleton($moduleRouteName)) {
                $record = $modelClass::query()->select('id')->first();
                if ($record !== null && $record->getKey() !== null) {
                    $this->warmRecord($modelClass, $record->getKey(), $moduleName, $moduleRouteName, $types);
                    $processed = 1;
                }

                $this->broadcastWarm(CacheWarmProgress::STATUS_COMPLETED, [
                    'job' => 'WarmModuleRouteCachesJob',
                    'moduleName' => $moduleName,
                    'moduleRouteName' => $moduleRouteName,
                    'processed' => $processed,
                    'toast' => $this->cacheWarmToast(
                        CacheWarmProgress::STATUS_COMPLETED,
                        __('messages.resource-cache.warm-toast.module-route.title'),
                        __('messages.resource-cache.warm-toast.module-route.completed', [
                            'processed' => $processed,
                        ]),
                        $detail,
                    ),
                ]);

                return;
            }

            $modelClass::query()
                ->select('id')
                ->orderBy('id')
                ->chunk($this->chunkSize, function ($records) use ($modelClass, $moduleName, $moduleRouteName, $types, $detail, &$processed) {
                    foreach ($records as $record) {
                        if ($record->getKey() === null) {
                            continue;
                        }

                        if (($types['presentationItem'] ?? false) && $this->shouldDispatchPresentationAsync()) {
                            $item = $modelClass::find($record->getKey());
                            if ($item instanceof Model) {
                                WarmPresentationItemJob::dispatch(
                                    $item,
                                    $moduleName,
                                    $moduleRouteName,
                                    null,
                                    $this->initiatorUserId,
                                );
                            }

                            $processed++;

                            continue;
                        }

                        $this->warmRecord($modelClass, $record->getKey(), $moduleName, $moduleRouteName, $types);
                        $processed++;
                    }

                    $this->broadcastWarm(CacheWarmProgress::STATUS_PROGRESS, [
                        'job' => 'WarmModuleRouteCachesJob',
                        'moduleName' => $moduleName,
                        'moduleRouteName' => $moduleRouteName,
                        'processed' => $processed,
                        'toast' => $this->cacheWarmToast(
                            CacheWarmProgress::STATUS_PROGRESS,
                            __('messages.resource-cache.warm-toast.module-route.title'),
                            __('messages.resource-cache.warm-toast.module-route.progress', [
                                'processed' => $processed,
                            ]),
                            $detail,
                        ),
                    ]);
                });

            $this->broadcastWarm(CacheWarmProgress::STATUS_COMPLETED, [
                'job' => 'WarmModuleRouteCachesJob',
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
                'processed' => $processed,
                'toast' => $this->cacheWarmToast(
                    CacheWarmProgress::STATUS_COMPLETED,
                    __('messages.resource-cache.warm-toast.module-route.title'),
                    __('messages.resource-cache.warm-toast.module-route.completed', [
                        'processed' => $processed,
                    ]),
                    $detail,
                ),
            ]);
        } catch (Throwable $e) {
            $this->broadcastWarm(CacheWarmProgress::STATUS_FAILED, [
                'job' => 'WarmModuleRouteCachesJob',
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
                'message' => $e->getMessage(),
                'toast' => $this->cacheWarmToast(
                    CacheWarmProgress::STATUS_FAILED,
                    __('messages.resource-cache.warm-toast.module-route.title'),
                    __('messages.resource-cache.warm-toast.module-route.failed', [
                        'message' => $e->getMessage(),
                    ]),
                    $detail,
                ),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function broadcastWarm(string $status, array $payload = []): void
    {
        event(new CacheWarmProgress($status, $this->initiatorUserId, $payload));
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
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => false,
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

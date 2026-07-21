<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Throwable;
use Unusualify\Modularous\Events\Cache\CacheWarmProgress;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\BuildsCacheWarmBroadcastToast;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class WarmPresentationItemJob implements ShouldQueue
{
    use BuildsCacheWarmBroadcastToast, Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    public function __construct(
        public Model $model,
        public ?string $moduleName = null,
        public ?string $moduleRouteName = null,
        public ?string $locale = null,
        public ?int $initiatorUserId = null,
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(): void
    {
        if (! $this->model->exists) {
            $fresh = $this->model::query()->find($this->model->getKey());
            if ($fresh instanceof Model) {
                $this->model = $fresh;
            }
        }

        ModularousCacheLogger::info('cache.job.warm_presentation_item.start', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'moduleName' => $this->moduleName,
            'moduleRouteName' => $this->moduleRouteName,
            'initiatorUserId' => $this->initiatorUserId,
        ]);

        $detail = $this->cacheWarmDetail(
            $this->moduleName,
            $this->moduleRouteName,
            $this->model->getKey(),
        );

        try {
            if (
                $this->moduleName !== null && $this->moduleName !== ''
                && $this->moduleRouteName !== null && $this->moduleRouteName !== ''
                && ! ModularousCache::isEnabled($this->moduleName, $this->moduleRouteName, 'presentationItem')
            ) {
                $this->broadcastWarm(CacheWarmProgress::STATUS_SKIPPED, [
                    'job' => 'WarmPresentationItemJob',
                    'model_type' => get_class($this->model),
                    'model_id' => $this->model->getKey(),
                    'moduleName' => $this->moduleName,
                    'moduleRouteName' => $this->moduleRouteName,
                    'skipped' => true,
                    'reason' => 'cache_disabled',
                    'toast' => $this->cacheWarmToast(
                        CacheWarmProgress::STATUS_SKIPPED,
                        __('messages.resource-cache.warm-toast.presentation-item.title'),
                        __('messages.resource-cache.warm-toast.presentation-item.completed-skipped', [
                            'reason' => __('messages.resource-cache.warm-toast.reason.cache_disabled'),
                        ]),
                        $detail,
                    ),
                ]);

                return;
            }

            ModularousCache::refreshModelCaches($this->model, [
                'presentationItem' => true,
                'counts' => false,
                'index' => false,
                'record' => false,
                'formItem' => false,
                'formattedItem' => false,
            ], [
                'moduleName' => $this->moduleName,
                'moduleRouteName' => $this->moduleRouteName,
                'locale' => $this->locale,
            ]);

            $this->broadcastWarm(CacheWarmProgress::STATUS_COMPLETED, [
                'job' => 'WarmPresentationItemJob',
                'model_type' => get_class($this->model),
                'model_id' => $this->model->getKey(),
                'moduleName' => $this->moduleName,
                'moduleRouteName' => $this->moduleRouteName,
                'toast' => $this->cacheWarmToast(
                    CacheWarmProgress::STATUS_COMPLETED,
                    __('messages.resource-cache.warm-toast.presentation-item.title'),
                    __('messages.resource-cache.warm-toast.presentation-item.completed'),
                    $detail,
                ),
            ]);
        } catch (Throwable $e) {
            $this->broadcastWarm(CacheWarmProgress::STATUS_FAILED, [
                'job' => 'WarmPresentationItemJob',
                'model_type' => get_class($this->model),
                'model_id' => $this->model->getKey(),
                'moduleName' => $this->moduleName,
                'moduleRouteName' => $this->moduleRouteName,
                'message' => $e->getMessage(),
                'toast' => $this->cacheWarmToast(
                    CacheWarmProgress::STATUS_FAILED,
                    __('messages.resource-cache.warm-toast.presentation-item.title'),
                    __('messages.resource-cache.warm-toast.presentation-item.failed', [
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
        if (class_exists(CmsPublicPresentationItemCache::class)) {
            return CmsPublicPresentationItemCache::warmPresentationOverlapKey(
                $this->model,
                $this->moduleName ?? 'auto',
                $this->moduleRouteName ?? 'auto',
            );
        }

        return sprintf(
            'cache:warm-presentation:%s:%s:%s:%s',
            $this->moduleName ?? 'auto',
            $this->moduleRouteName ?? 'auto',
            get_class($this->model),
            (string) $this->model->getKey(),
        );
    }
}

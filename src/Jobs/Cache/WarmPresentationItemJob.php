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
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class WarmPresentationItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    public function __construct(
        public Model $model,
        public ?string $moduleName = null,
        public ?string $moduleRouteName = null,
        public ?string $locale = null,
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
        ]);

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

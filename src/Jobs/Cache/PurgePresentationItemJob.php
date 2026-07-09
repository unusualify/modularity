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

final class PurgePresentationItemJob implements ShouldQueue
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

        ModularousCacheLogger::info('cache.job.purge_presentation_item.start', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'moduleName' => $this->moduleName,
            'moduleRouteName' => $this->moduleRouteName,
            'locale' => $this->locale,
        ]);

        ModularousCache::purgePresentationItemForModel(
            $this->model,
            $this->moduleName,
            $this->moduleRouteName,
            $this->locale,
        );
    }

    protected function modularousCacheOverlapKey(): string
    {
        return sprintf(
            'cache:purge-presentation:%s:%s:%s:%s',
            $this->moduleName ?? 'auto',
            $this->moduleRouteName ?? 'auto',
            get_class($this->model),
            (string) $this->model->getKey(),
        );
    }
}

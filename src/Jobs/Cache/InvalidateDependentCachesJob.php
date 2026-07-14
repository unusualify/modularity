<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class InvalidateDependentCachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    public function __construct(
        public Model $model,
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(DependentCacheInvalidator $invalidator): void
    {
        ModularousCacheLogger::info('cache.job.invalidate_dependents.start', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
        ]);

        $invalidator->invalidateForModel($this->model);
    }

    protected function modularousCacheOverlapKey(): string
    {
        return sprintf(
            'cache:invalidate-dependents:%s:%s',
            get_class($this->model),
            (string) $this->model->getKey(),
        );
    }
}

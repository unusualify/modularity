<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\Concerns\ModularousCacheJob;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class PurgeModulePresentationCachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, ModularousCacheJob, Queueable, SerializesModels;

    public function __construct(
        public string $moduleName,
        public string $moduleRouteName,
        public int $chunkSize = 100,
    ) {
        $this->configureModularousCacheQueue();
    }

    public function handle(): void
    {
        $moduleName = Str::studly($this->moduleName);
        $moduleRouteName = Str::studly($this->moduleRouteName);

        ModularousCacheLogger::info('cache.job.purge_module_presentation.start', [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'chunkSize' => $this->chunkSize,
        ]);

        if (! ModularousCache::isEnabled($moduleName, $moduleRouteName, 'presentationItem')) {
            return;
        }

        $module = Modularous::find($moduleName);
        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        ModularousCache::purgePresentationItemForModuleRoute($moduleName, $moduleRouteName);
    }

    protected function modularousCacheOverlapKey(): string
    {
        return sprintf(
            'cache:purge-route-presentation:%s:%s',
            Str::studly($this->moduleName),
            Str::studly($this->moduleRouteName),
        );
    }
}

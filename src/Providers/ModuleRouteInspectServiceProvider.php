<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Providers;

use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteInspectSource;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectHealer;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspectRemedyMapper;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;
use Unusualify\Modularous\Services\ModuleRouteInspect\Stores\DatabaseModuleRouteStatusStore;
use Unusualify\Modularous\Services\ModuleRouteInspect\Stores\FilesystemModuleRouteStatusStore;

class ModuleRouteInspectServiceProvider extends ServiceProvider
{
    /**
     * Register Module Route Inspect feature bindings.
     */
    public function register(): void
    {
        $this->app->singleton(FeatureDetector::class);
        $this->app->singleton(ModuleRouteInspectRemedyMapper::class);
        $this->app->singleton(ModuleRouteInspectHealer::class);

        $this->app->singleton(ModuleRouteStatusStoreInterface::class, function ($app) {
            $driver = (string) config('modularous.module_route_inspect.driver', 'filesystem');

            return match ($driver) {
                'database' => $app->make(DatabaseModuleRouteStatusStore::class),
                default => $app->make(FilesystemModuleRouteStatusStore::class),
            };
        });

        $this->app->singleton(ModuleRouteInspector::class);
        $this->app->alias(ModuleRouteInspector::class, ModuleRouteInspectSource::class);
    }

    /**
     * @return list<class-string>
     */
    public function provides(): array
    {
        return [
            FeatureDetector::class,
            ModuleRouteInspectRemedyMapper::class,
            ModuleRouteInspectHealer::class,
            ModuleRouteStatusStoreInterface::class,
            ModuleRouteInspector::class,
            ModuleRouteInspectSource::class,
        ];
    }
}

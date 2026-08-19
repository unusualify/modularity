<?php

namespace Unusualify\Modularous\Providers;

use Camroncade\Timezone\TimezoneServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Torann\GeoIP\GeoIPServiceProvider;

class ModularousProvider extends ServiceProvider
{
    protected $providers = [
        // Bind ActivatorInterface when package:discover only loads this package.
        LaravelModulesServiceProvider::class,

        // Third Party Providers
        GeoIPServiceProvider::class,
        TimezoneServiceProvider::class,

        // Unusual Providers
        BaseServiceProvider::class,
        ModuleServiceProvider::class,
        SecurityServiceProvider::class,
        RemoteApiServiceProvider::class,
        RouteServiceProvider::class,
        AuthServiceProvider::class,
        CoverageServiceProvider::class,
        ArtisanRunnerServiceProvider::class,
        ModuleRouteInspectServiceProvider::class,
        ModuleRoutePresentationServiceProvider::class,

        // AuthServiceProvider::class,
        // ValidationServiceProvider::class,
        // TranslatableServiceProvider::class,
        // TagsServiceProvider::class,
        // ActivitylogServiceProvider::class,
        // CapsulesServiceProvider::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerProviders();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Has to be merged after routeServiceProvider registered
        if (exceptionalRunningInConsole()) {
            // $this->mergeConfigFrom(__DIR__ . '/../../config/navigation.php', modularousBaseKey() . '-navigation');
            $this->booted(function () {});
        }
    }

    /**
     * Register providers.
     */
    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}

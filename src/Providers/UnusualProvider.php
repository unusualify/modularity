<?php

namespace Unusualify\Modularity\Providers;

class UnusualProvider extends ServiceProvider
{
    protected $providers = [
        // Third Party Providers
        \Torann\GeoIP\GeoIPServiceProvider::class,
        \Camroncade\Timezone\TimezoneServiceProvider::class,

        // Unusual Providers
        BaseServiceProvider::class,
        ModuleServiceProvider::class,
        RouteServiceProvider::class,
        AuthServiceProvider::class,

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
            $this->mergeConfigFrom(__DIR__ . '/../../config/navigation.php', unusualBaseKey() . '-navigation');
        }
        // dd($this->app->config->get(unusualBaseKey() . '-navigation'), require __DIR__ . '/../../config/navigation.php');
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

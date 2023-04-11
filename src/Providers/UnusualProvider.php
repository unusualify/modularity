<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Database\Eloquent\Factory;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use OoBook\CRM\Base\View\Table;

class UnusualProvider extends ServiceProvider
{
    protected $providers = [
        BaseServiceProvider::class,
        ConfigServiceProvider::class,
        ResourceServiceProvider::class,
        RouteServiceProvider::class,
        MenuServiceProvider::class,
        // ViewServiceProvider::class,

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

        $this->publishAssets();
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

    private function publishAssets(): void
    {

        $this->publishes([
            base_path( env('UNUSUAL_VENDOR_PATH', 'vendor/oobook/crm-base') . '/vue/dist') => public_path(),
        ], 'assets');
    }
}

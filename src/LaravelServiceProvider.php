<?php


namespace OoBook\CRM\Base;

use Illuminate\Support\ServiceProvider;

final class LaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishMigrations();

        $this->publishAssets();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishConfigs();
    }

    private function publishMigrations(): void
    {
        // $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        if (config('unusual.load_default_migrations', true)) {

        }

        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations/default'),
        ], 'migrations');

    }

    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../vue/dist' => public_path(),
        ], 'assets');
    }

    private function publishConfigs(): void
    {
        $this->publishes([
            base_path('vendor/torann/geoip/config/geoip.php') => config_path('geoip.php'),
        ], 'config');
    }
}

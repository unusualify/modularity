<?php


namespace Unusualify\Modularity;


use Illuminate\Support\ServiceProvider;

final class LaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        // $this->publishMigrations();
        // $this->mergeMigrations();
        $this->publishConfigs();
        $this->publishAssets();
        $this->publishViews();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

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
        // $this->publishes([
        //     __DIR__ . '/../vue/dist' => public_path(''),
        // ], 'assets');
        $this->publishes([
            __DIR__ . '/../vue/dist' => public_path('vendor'),
        ], 'assets');
    }

    private function publishConfigs(): void
    {
        $this->publishes([
            __DIR__ . '/Config/publishes/translatable.php' => config_path('translatable.php'),
            __DIR__ . '/Config/publishes/translation.php' => config_path('translation.php'),
            __DIR__ . '/Config/publishes/publish.php' => config_path(unusualBaseKey() . '.php'),
            __DIR__ . '/Config/publishes/navigation-publish.php' => config_path( unusualBaseKey() . '-navigation.php'),
            __DIR__ . '/Config/publishes/one-time-operations.php' => config_path('one-time-operations.php'),
            base_path('vendor/torann/geoip/config/geoip.php') => config_path('geoip.php'),
        ], 'config');

    }

    private function publishViews(): void
    {
        $this->publishes([
            __DIR__ . '/Resources/views/vendor/translation' => resource_path('views/vendor/translation')
        ], 'views');

        $this->publishes([
            __DIR__ . '/../vue/dist/modularity/assets/icons' => resource_path('views/vendor/modularity/partials/icons')
        ], 'assets');
    }




}

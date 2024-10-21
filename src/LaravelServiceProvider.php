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
        $this->publishLang();
        $this->publishAssets();
        $this->publishViews();
        $this->publishResources();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {}

    private function publishAssets(): void
    {
        // $this->publishes([
        //     __DIR__ . '/../vue/dist' => public_path(''),
        // ], 'assets');
        $this->publishes([
            __DIR__ . '/../vue/dist/modularity' => public_path('vendor/modularity'),
            __DIR__.'/../resources/assets/images' => public_path('vendor/modularity/assets/images'),

        ], 'assets');
    }

    private function publishConfigs(): void
    {
        $this->publishes([
            __DIR__ . '/../config/publishes/publish.php' => config_path(unusualBaseKey() . '.php'),
            __DIR__ . '/../config/publishes/navigation-publish.php' => config_path(unusualBaseKey() . '-navigation.php'),
            __DIR__ . '/../config/publishes/translatable.php' => config_path('translatable.php'),
            __DIR__ . '/../config/publishes/translation.php' => config_path('translation.php'),
            __DIR__ . '/../config/publishes/one-time-operations.php' => config_path('one-time-operations.php'),
            __DIR__ . '/../config/publishes/modules.php' => config_path('modules.php'),
            __DIR__ . '/../config/publishes/priceable.php' => config_path('priceable.php'),
            __DIR__ . '/../config/publishes/snapshot.php' => config_path('snapshot.php'),
            __DIR__ . '/../config/publishes/payable.php' => config_path('payable.php'),
            __DIR__ . '/../config/publishes/permission.php' => config_path('permission.php'),
            __DIR__ . '/../config/publishes/activitylog.php' => config_path('activitylog.php'),
            __DIR__ . '/../config/publishes/geoip.php' => config_path('geoip.php'),
            // base_path('vendor/torann/geoip/config/geoip.php') => config_path('geoip.php'),
        ], 'config');

    }

    private function publishViews(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/views/vendor/translation' => resource_path('views/vendor/translation'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../vue/dist/modularity/assets/icons' => resource_path('views/vendor/modularity/partials/icons'),
        ], 'assets');
    }

    private function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../vue/drafts/components' => resource_path(unusualConfig('custom_components_resource_path', 'vendor/modularity/js/components')),
        ], 'custom-components');
    }

    private function publishLang(): void
    {
        $this->publishes([
            __DIR__ . '/../lang' => base_path('lang'),
        ], 'lang');
    }
}

<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use OoBook\CRM\Base\Facades\UnusualNavigation;
use OoBook\CRM\Base\View\Table;

class UnusualProvider extends ServiceProvider
{
    protected $providers = [
        // Third Party Providers
        \Torann\GeoIP\GeoIPServiceProvider::class,

        // Unusual Providers
        BaseServiceProvider::class,
        ConfigServiceProvider::class,
        ResourceServiceProvider::class,
        RouteServiceProvider::class,
        AuthServiceProvider::class,

        // MenuServiceProvider::class,
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

        $this->publishConfigs();

        $this->publishAssets();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Has to be merged after routeServiceProvider registered
        $this->mergeConfigFrom(__DIR__ . '/../Config/navigation.php', unusualBaseKey() . '-navigation');
        // dd($this->app->config->get(unusualBaseKey() . '-navigation'), require __DIR__ . '/../Config/navigation.php');
        // dd(
        //     $this->app->config->get(unusualBaseKey() . '-navigation')
        // );
        // dd(
        //     $this->app->config->get(unusualBaseKey() . '-navigation')
        // );
        $this->app->config->set([
            unusualBaseKey() . '-navigation.sidebar' => UnusualNavigation::formatSidebarMenus($this->app->config->get(unusualBaseKey() . '-navigation.sidebar'))
        ]);

        // load base module migrations
        $this->loadMigrationsFrom(
            base_path( config( $this->baseKey . '.vendor_path') . '/src/Database/Migrations/default' )
        );

        // load each enable module migrations
        $modules_folder = base_path(config('modules.namespace'));
        $module_migration_folder = GenerateConfigReader::read('migration')->getPath();
        foreach(Module::allEnabled() as $module){
            $this->loadMigrationsFrom(
                $modules_folder . "/" . $module->getStudlyName() . "/" . $module_migration_folder
            );
        }

        // dd(
        //     Module::allEnabled(),
        //     Module::allDisabled(),
        //     Module::getPath(),
        //     config('modules.namespace')
        //     config('modules.namespace') . "/{$this->module->getStudlyName()}/Database/Migrations"
        // );
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

    private function publishConfigs(): void
    {
        $this->publishes([__DIR__ . '/../config/publish.php' => config_path('unusual.php')], 'config');
        $this->publishes([__DIR__ . '/../config/navigation-publish.php' => config_path('unusual-navigation.php')], 'config');
    }
}

<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UNavigation;
use Unusualify\Modularity\View\Table;

class UnusualProvider extends ServiceProvider
{
    protected $providers = [
        // Third Party Providers
        \Torann\GeoIP\GeoIPServiceProvider::class,
        \Camroncade\Timezone\TimezoneServiceProvider::class,

        // Unusual Providers
        ConfigServiceProvider::class,
        BaseServiceProvider::class,
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
        // $this->app->config->set([
        //     unusualBaseKey() . '-navigation.sidebar' => UNavigation::formatSidebarMenus($this->app->config->get(unusualBaseKey() . '-navigation.sidebar'))
        // ]);

        // load unusual migrations
        $this->loadMigrationsFrom(
            base_path( config( $this->baseKey . '.vendor_path') . '/src/Database/Migrations/default' )
        );

        // load each enable module migrations
        $modules_folder = base_path(config('modules.namespace'));
        $module_migration_folder = GenerateConfigReader::read('migration')->getPath();
        foreach(Modularity::allEnabled() as $module){
            $this->loadMigrationsFrom(
                $modules_folder . "/" . $module->getStudlyName() . "/" . $module_migration_folder
            );
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

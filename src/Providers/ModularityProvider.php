<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Auth\Notifications\ResetPassword;

class ModularityProvider extends ServiceProvider
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
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {

            // return route('admin.password.reset',[
            //     'token' => $token,
            //     'email' => $notifiable->getEmailForPasswordReset()
            // ]);
            // TODO: Move this to BaseServiceProvider
            return url(route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });
        // dd(__FUNCTION__, __CLASS__);
        // Has to be merged after routeServiceProvider registered
        if (exceptionalRunningInConsole()) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/navigation.php', modularityBaseKey() . '-navigation');
        }
        // dd($this->app->config->get(modularityBaseKey() . '-navigation'), require __DIR__ . '/../../config/navigation.php');
        // dd(
        //     $this->app->config->get(modularityBaseKey() . '-navigation')
        // );
        // dd(
        //     $this->app->config->get(modularityBaseKey() . '-navigation')
        // );
        // $this->app->config->set([
        //     modularityBaseKey() . '-navigation.sidebar' => UNavigation::formatSidebarMenus($this->app->config->get(modularityBaseKey() . '-navigation.sidebar'))
        // ]);

        // // load unusual migrations
        // $this->loadMigrationsFrom(
        //     base_path( config( $this->baseKey . '.vendor_path') . '/src/Database/Migrations/default' )
        // );

        // load each enable module migrations
        // $modules_folder = base_path(config('modules.namespace'));
        // $module_migration_folder = GenerateConfigReader::read('migration')->getPath();
        // foreach(Modularity::allEnabled() as $module){
        //     $this->loadMigrationsFrom(
        //         $modules_folder . "/" . $module->getStudlyName() . "/" . $module_migration_folder
        //     );
        // }
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

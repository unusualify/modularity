<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Facades\Module;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;
use Nwidart\Modules\Support\Config\GenerateConfigReader;

class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // config([
        //     'modules.cache.enabled' => true,
        //     'modules.cache.key' => 'modularity',
        //     'modules.cache.lifetime' => 600
        // ]);

        $this->bootModules();
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModules();
    }


    public function registerModules()
    {

    }

    public function bootModules()
    {
        foreach(Modularity::allEnabled() as $module){

            // LOAD MODULE CONFIG
            if(file_exists(module_path($module->getName(), 'Config/config.php'))){
                $this->mergeConfigFrom(
                    module_path($module->getName(), 'Config/config.php'), $module->getSnakeName()
                );

            }

            // LOAD MODULE MIGRATIONS
            $modules_folder = base_path(config('modules.namespace'));
            $module_migration_folder = GenerateConfigReader::read('migration')->getPath();
            foreach(Modularity::allEnabled() as $module){
                $this->loadMigrationsFrom(
                    $modules_folder . "/" . $module->getStudlyName() . "/" . $module_migration_folder
                );
            }

            // LOAD MODULE VIEWS
            $this->viewSourcePath = module_path($module->getName(), 'Resources/views');
            // $this->publishes([
            //     $sourcePath => $viewPath
            // ], ['views', $module->getLowerName() . '-module-views']);
            $this->loadViewsFrom(
                array_merge(
                    $this->getPublishableViewPaths(
                        snakeCase($module->getName())
                    ),
                    [$this->viewSourcePath]
                ),
                snakeCase($module->getName())
            );

            //LOAD MODULE TRANSLATION
            $langPath = base_path('lang/modules/' . $module->getLowerName());
            if (is_dir($langPath)) {
                $this->loadTranslationsFrom($langPath, $module->getLowerName());
            } else {
                $this->loadTranslationsFrom(
                    module_path($module->getName(), 'Resources/lang'),
                    $module->getLowerName()
                );
            }
        }
    }

}

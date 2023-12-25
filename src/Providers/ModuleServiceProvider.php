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
        $migration_directory = GenerateConfigReader::read('migration')->getPath();
        foreach(Modularity::allEnabled() as $module){

            $module_name = $module->getName();

            // LOAD MODULE CONFIG
            if(file_exists(module_path($module->getName(), 'Config/config.php'))){
                $this->mergeConfigFrom(
                    $module->getDirectoryPath('Config/config.php'), $module->getSnakeName()
                );

            }

            // LOAD MODULE MIGRATIONS
            $this->loadMigrationsFrom(
                $module->getDirectoryPath($migration_directory)
            );


            // LOAD MODULE VIEWS
            $sourcePath = module_path($module->getName(), 'Resources/views');

            $this->loadViewsFrom(
                array_merge(
                    $this->getPublishableViewPaths(
                        $module->getSnakeName()
                    ),
                    [$sourcePath]
                ),
                $module->getSnakeName()
            );

            //LOAD MODULE TRANSLATION
            $langPath = base_path('lang/modules/' . $module->getLowerName());
            if (is_dir($langPath)) {
                $this->loadTranslationsFrom($langPath, $module->getLowerName());
            } else {
                $this->loadTranslationsFrom(
                    $module->getDirectoryPath('Resources/lang'),
                    $module->getLowerName()
                );
            }
        }

        // dd('dsjafh');

    }

}

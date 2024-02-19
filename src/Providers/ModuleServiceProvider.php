<?php

namespace Unusualify\Modularity\Providers;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;
use Nwidart\Modules\Support\Config\GenerateConfigReader;

use Modules\Webinar\Http\Middleware\WebinarAuthMiddleware;
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

    }

    public function bootModules()
    {
        $migration_directory = GenerateConfigReader::read('migration')->getPath();

        foreach(Modularity::allEnabled() as $module){

            $module_name = $module->getName();

            // REGISTER MODULE MIDDLEWARES
            if(file_exists($module->getDirectoryPath('Http/Middleware'))){
                foreach ( ClassFinder::getClassesInNamespace($module->getClassNamespace('Http\Middleware')) as $key => $middleware) {

                    if(@class_exists($middleware)){
                        $class = app($middleware);

                        $shortName = get_class_short_name($class);
                        $namespace = get_class($class);

                        $aliasName = implode('.', Arr::where(explode('_', snakeCase($shortName)), function($value){
                            return $value !== 'middleware';
                        }));

                        Route::aliasMiddleware($aliasName, $namespace);
                    }
                }
            }

            // LOAD MODULE CONFIG
            // if(file_exists(module_path($module->getName(), 'Config/config.php'))){
            if(file_exists($module->getDirectoryPath('Config/config.php'))){
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

            // LOAD MODULE VIEW COMPONENTS
            $namespace = $module->getClassNamespace('View\\Components');
            Blade::componentNamespace($namespace, snakeCase($module_name));

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
    }

}

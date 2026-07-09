<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Blade;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularous\Facades\Modularous;

class ModuleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootModules();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {}

    public function bootModules()
    {
        $migration_folder = GenerateConfigReader::read('migration')->getPath();
        $config_folder = GenerateConfigReader::read('config')->getPath();
        $provider_folder = GenerateConfigReader::read('provider')->getPath();
        $provider_namespace = GenerateConfigReader::read('provider')->getNamespace();
        $views_folder = GenerateConfigReader::read('views')->getPath();
        $lang_folder = GenerateConfigReader::read('lang')->getPath();
        $component_class_namespace = GenerateConfigReader::read('component-class')->getNamespace();

        foreach (Modularous::allEnabled() as $module) {

            $module_name = $module->getName();

            // REGISTER MODULE MIDDLEWARES
            $module->createMiddlewareAliases();

            // REGISTER MODULE PROVIDERS
            if (file_exists(($providerDir = $module->getDirectoryPath($provider_folder)))) {
                foreach (glob($providerDir . '/*ServiceProvider.php') as $providerFile) {
                    $providerFileName = pathinfo($providerFile)['filename']; // $filename
                    $providerClass = $module->getClassNamespace("{$provider_namespace}\\" . $providerFileName);
                    if (@class_exists($providerClass)) {
                        $this->app->register($providerClass);
                    }
                }
            }
            // LOAD MODULE CONFIG
            $module->loadConfig();

            // LOAD MODULE COMMANDS
            $module->loadCommands();

            // LOAD MODULE MIGRATIONS
            $this->loadMigrationsFrom(
                $module->getDirectoryPath($migration_folder)
            );

            // LOAD MODULE VIEWS
            $sourcePath = $module->getDirectoryPath($views_folder);
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
            $namespace = $module->getClassNamespace($component_class_namespace);
            Blade::componentNamespace($namespace, snakeCase($module_name));

            // LOAD MODULE TRANSLATION
            $langPath = base_path('lang/modules/' . $module->getSnakeName());

            // Add lang paths to merge with laravel translations
            /**
             * Unusualify\Modularous\Translation\Translator::class instance is $this->app['translator']
             */
            // $this->app['translator']->addPath($module->getDirectoryPath($lang_folder));

            if (is_dir($langPath)) {
                $this->loadTranslationsFrom($langPath, $module->getLowerName());
            } else {
                $this->loadTranslationsFrom(
                    $module->getDirectoryPath('Resources/lang'),
                    $module->getSnakeName()
                );
            }
        }
        if (! $this->app->runningInConsole()) {
        }
    }
}

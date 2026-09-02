<?php

namespace Unusualify\Modularous\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Blade;
use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Unusualify\Modularous\Contracts\ModulePresentationAssetLoaderInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\ModulePresentationAssetLoader;
use Unusualify\Modularous\Support\ConsoleCommandRegistration;

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
    public function register()
    {
        $this->app->singleton(
            ModulePresentationAssetLoaderInterface::class,
            ModulePresentationAssetLoader::class,
        );
    }

    public function bootModules()
    {
        $migration_folder = GenerateConfigReader::read('migration')->getPath();
        $provider_folder = GenerateConfigReader::read('provider')->getPath();
        $provider_namespace = GenerateConfigReader::read('provider')->getNamespace();
        $views_folder = GenerateConfigReader::read('views')->getPath();
        $lang_folder = GenerateConfigReader::read('lang')->getPath();
        $component_class_namespace = GenerateConfigReader::read('component-class')->getNamespace();

        $isFront = ! $this->app->runningInConsole() && Modularous::isFrontUrl();
        $deferPresentationAssets = $isFront && ModularousCache::isUrlStaleServeFirst();

        $this->app->make(ModulePresentationAssetLoaderInterface::class)->configure(
            $deferPresentationAssets,
            fn () => $this->loadDeferredModulePresentationAssets(),
        );

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

            // LOAD MODULE COMMANDS (CLI, or HTTP ArtisanRunner which uses Artisan::all())
            if (ConsoleCommandRegistration::shouldRegister(
                $this->app->runningInConsole(),
                $this->app->bound('request') ? (string) $this->app['request']->path() : null,
            )) {
                $module->loadCommands();
            }

            if ($this->app->runningInConsole()) {
                $this->loadMigrationsFrom(
                    $module->getDirectoryPath($migration_folder)
                );
            }

            if (! $deferPresentationAssets) {
                $this->loadModulePresentationAssets(
                    $module,
                    $module_name,
                    $views_folder,
                    $component_class_namespace,
                    $lang_folder,
                );
            }
        }
    }

    public function loadDeferredModulePresentationAssets(): void
    {
        $views_folder = GenerateConfigReader::read('views')->getPath();
        $lang_folder = GenerateConfigReader::read('lang')->getPath();
        $component_class_namespace = GenerateConfigReader::read('component-class')->getNamespace();

        foreach (Modularous::allEnabled() as $module) {
            $this->loadModulePresentationAssets(
                $module,
                $module->getName(),
                $views_folder,
                $component_class_namespace,
                $lang_folder,
            );
        }
    }

    protected function loadModulePresentationAssets(
        Module $module,
        string $module_name,
        string $views_folder,
        string $component_class_namespace,
        string $lang_folder,
    ): void {
        $sourcePath = $module->getDirectoryPath($views_folder);
        $this->loadViewsFrom(
            array_merge(
                $this->getPublishableViewPaths($module->getSnakeName()),
                [$sourcePath]
            ),
            $module->getSnakeName()
        );

        $namespace = $module->getClassNamespace($component_class_namespace);
        Blade::componentNamespace($namespace, snakeCase($module_name));

        $langPath = base_path('lang/modules/' . $module->getSnakeName());

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $module->getLowerName());
        } else {
            $this->loadTranslationsFrom(
                $module->getDirectoryPath('Resources/lang'),
                $module->getSnakeName()
            );
        }
    }

    /**
     * @return list<string>
     */
    protected function getPublishableViewPaths(?string $moduleSnakeName = null): array
    {
        $paths = [];
        $key = $moduleSnakeName ?? $this->baseKey;

        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $key)) {
                $paths[] = $path . '/modules/' . $key;
            }
        }

        return $paths;
    }
}

<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\View;
use Unusualify\Modularity\Activators\FileActivator;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Http\ViewComposers\CurrentUser;
use Unusualify\Modularity\Http\ViewComposers\FilesUploaderConfig;
use Unusualify\Modularity\Http\ViewComposers\Localization;
use Unusualify\Modularity\Http\ViewComposers\MediasUploaderConfig;
use Unusualify\Modularity\Http\ViewComposers\Urls;
use Unusualify\Modularity\Modularity;
use Unusualify\Modularity\Services\View\UNavigation;
use Unusualify\Modularity\Support\FileLoader;
use Unusualify\Modularity\Translation\Translator;
use Unusualify\Modularity\Exceptions\AuthConfigurationException;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPackageConfigs();

        if (unusualConfig('enabled.media-library')) {
            $this->app->singleton('imageService', function () {
                return $this->app->make(config($this->baseKey . '.media_library.image_service'));
            });
        }
        if (
            unusualConfig('media_library.endpoint_type') === 'local'
            && unusualConfig('media_library.disk') === unusualBaseKey() . '_media_library'
        ) {
            $this->setLocalDiskUrl('media');
        }

        if (unusualConfig('enabled.file-library')) {
            $this->app->singleton('fileService', function () {
                return $this->app->make(config($this->baseKey . '.file_library.file_service'));
            });
        }
        if (unusualConfig('file_library.endpoint_type') === 'local'
            && unusualConfig('file_library.disk') === unusualBaseKey() . '_file_library') {
            $this->setLocalDiskUrl('file');
        }

        $this->bootMacros();

        $this->bootBaseMigrations();

        $this->bootBaseViews();

        $this->bootBaseTranslation();

        $this->bootBaseViewComposers();

        $this->bootBaseViewComponents();

        AboutCommand::add('Modularity', function () {
            $composer = base_path('composer.lock');

            if ($this->app['files']->isFile(($composer_path = base_path('composer-dev.lock')))) {
                $composer = $composer_path;
            }

            $package = collect(json_decode(file_get_contents($composer))->packages)
                ->filter(fn ($p) => $p->name == 'unusualify/modularity')
                ->first();

            return [
                'Vendor' => get_modularity_vendor_dir(),
                'Theme' => unusualConfig('app_theme'),
                'Version' => $package->version,
            ];
        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerHelpers();

        $this->registerBaseConfigs();

        $this->registerCommands();

        // $this->app->singleton(\Unusualify\Modularity\Contracts\RepositoryInterface::class, function ($app) {
        $this->app->singleton('unusual.modularity', function (Application $app) {
            $path = $app['config']->get('modules.paths.modules');

            return new Modularity($app, $path);
        });

        // $this->app->singleton(FileActivator::class, function ($app) {
        $this->app->singleton('unusual.activator', function (Application $app) {
            // echo 'unusual.activator' . "</br>";
            return new FileActivator($app);
        });

        $this->app->singleton('unusual.navigation', UNavigation::class);
        // $this->app->alias(\Unusualify\Modularity\Contracts\RepositoryInterface::class, 'ue_modules');
        $this->app->alias('unusual.modularity', 'modularity');

        $this->app->singleton('model.relation.namespace', function () {
            return "Illuminate\Database\Eloquent\Relations";
        });

        $this->app->singleton('model.relation.pattern', function () {
            $relationNamespace = app('model.relation.namespace');

            return '|' . preg_quote($relationNamespace, '|') . '|';
        });

        $this->app->singleton('unusualify.hosting', function (Application $app) {
            return new \Unusualify\Modularity\Support\HostRouting($app, unusualConfig('app_url'));
        });

        $this->app->singleton('unusualify.hostRouting', function (Application $app) {
            return new \Unusualify\Modularity\Support\HostRouteRegistrar($app, unusualConfig('app_url'));
        });

        $this->app->singleton('Filepond', function (Application $app) {
            return new \Unusualify\Modularity\Services\FilepondManager;
        });

        $this->app->singleton('currency.exchange', function (Application $app) {
            return new \Unusualify\Modularity\Services\CurrencyExchangeService;
        });

        $this->app->alias(\Unusualify\Modularity\Facades\ModularityVite::class, 'ModularityVite');

        // $this->app->alias(FileActivator::class, 'module_activator');

        $this->app->alias(\Torann\GeoIP\Facades\GeoIP::class, 'GeoIP');

        $this->registerTranslationService();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    private function registerHelpers()
    {
        foreach (glob(__DIR__ . '/../Helpers/*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * {@inheritdoc}
     */
    private function registerBaseConfigs()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', $this->baseKey
        );

        foreach (glob(__DIR__ . '/../../config/merges/*.php') as $path) {
            extract(pathinfo($path)); // $filename
            $this->mergeConfigFrom($path, $this->baseKey . ".{$filename}");
        }

        $this->mergeConfigFrom(__DIR__ . '/../../config/disks.php', 'filesystems.disks');
    }

    /**
     * {@inheritdoc}
     */
    private function registerCommands()
    {
        $this->commands($this->resolveCommands());
    }

    public function registerTranslationService()
    {
        $this->app->extend('translation.loader', function ($service, $app) {
            return new FileLoader($app['files'], [base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'), realpath(__DIR__ . '/../../lang'),  $app['path.lang']]);
            // return new \Illuminate\Translation\FileLoader($app['files'], [base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'), realpath(__DIR__.'/../../lang'),  $app['path.lang']]);
        });

        $this->app->extend('translator', function ($service, $app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];
            // $trans = new \Illuminate\Translation\Translator($loader, $locale);
            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

    /**
     * {@inheritdoc}
     */
    private function bootPackageConfigs()
    {
        if (modularityConfig('enabled.users-management') && !$this->app->runningInConsole()) {
            $modularityAuthGuardAbsent = blank(config('auth.guards.' . Modularity::getAuthGuardName()));
            $modularityAuthProviderAbsent = blank(config('auth.providers.' . Modularity::getAuthProviderName()));
            $modularityAuthPasswordAbsent = blank(config('auth.passwords.' . Modularity::getAuthProviderName()));

            if ($modularityAuthGuardAbsent) {
                throw AuthConfigurationException::guardMissing();
            }

            if ($modularityAuthProviderAbsent) {
                throw AuthConfigurationException::providerMissing();
            }

            if ($modularityAuthPasswordAbsent) {
                throw AuthConfigurationException::passwordMissing();
            }

            // try {
            //     // code that might throw AuthConfigurationException
            // } catch (AuthConfigurationException $e) {
            //     switch ($e->getCode()) {
            //         case AuthConfigurationException::GUARD_MISSING:
            //             // Handle missing guard
            //             break;
            //         case AuthConfigurationException::PROVIDER_MISSING:
            //             // Handle missing provider
            //             break;
            //         case AuthConfigurationException::PASSWORD_MISSING:
            //             // Handle missing password configuration
            //             break;
            //     }
            // }
        }

        config([
            'modularity.vendor_dir' => is_modularity_production()
                ? 'vendor/unusualify/modularity'
                : env('MODULARITY_VENDOR_DIR', env('MODULARITY_VENDOR_PATH', 'packages/modularity')),
        ]);

        // Nwidart/laravel-modules scan enabled & scan path addition
        if(!config('modules.scan.enabled')) {
            config([
                'modules.scan.enabled' => true,
            ]);
        }
        $scan_paths = config('modules.scan.paths', []);
        $umodulesPath = \Unusualify\Modularity\Facades\Modularity::getVendorPath('umodules');
        if(!in_array($umodulesPath, $scan_paths)) {
            array_push($scan_paths, $umodulesPath);
            config([
                'modules.scan.paths' => $scan_paths,
            ]);
        }

        if(!$this->app->isProduction() && config('modules.cache.enabled')){
            config([
                'modules.cache.enabled' => false,
            ]);
        }

        // timokoerber/laravel-one-time-operations directory set
        config([
            'one-time-operations.directory' => get_modularity_vendor_dir('operations'),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    private function bootMacros()
    {

        \Illuminate\Support\Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });

        // Lang::handleMissingKeysUsing(function (string $key, array $replacements, string $locale) {
        //     info("Missing translation key [$key] detected.");

        //     return $key;
        // });
    }

    /**
     * {@inheritdoc}
     */
    private function bootBaseMigrations()
    {
        // LOAD BASE MIGRATIONS
        $this->loadMigrationsFrom(
            // get_modularity_vendor_path('database/migrations/default')
            \Unusualify\Modularity\Facades\Modularity::getVendorPath('database/migrations/default')
        );
    }

    /**
     * {@inheritdoc}
     */
    private function bootBaseViews()
    {

        // LOAD BASE VIEWS
        $this->loadViewsFrom(
            array_merge(
                $this->getPublishableViewPaths($this->baseKey),
                [resource_path('views/vendor/modularity')],
                [$this->viewSourcePath],
            ),
            $this->baseKey
        );
    }

    private function bootBaseTranslation()
    {

        // $name = snakeCase( config($this->baseKey . '.name') );
        $name = unusualBaseKey();
        $langPath = base_path('lang/modules/' . $name);
        $laravelLangPath = base_path('lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $name);
        } else {
            // dd('resource');
            // Lang::addNamespace('unusual',  __DIR__ .  '/../../lang');
            // $this->app['translation.loader']->addNamespace('unusual',  __DIR__ .  '/../../lang');

            // $this->loadTranslationsFrom(
            //     __DIR__ .  '/../../lang',
            //     $name
            // );

            // $this->loadJsonTranslationsFrom(
            //     __DIR__ .  '/../../lang',
            // );
        }

        // if (is_dir($laravelLangPath)) {
        //     $this->loadJsonTranslationsFrom($laravelLangPath);
        // }

        // dd(
        //     ___('edit-item', ['item' => 'hagü']),
        // );
    }

    /**
     * Registers the package additional View Composers.
     */
    private function bootBaseViewComposers(): void
    {
        view()->composer('*', function ($view) {
            $view->with('BASE_KEY', $this->baseKey);
            $view->with('MODULARITY_VIEW_NAMESPACE', $this->baseKey);
            $view->with('SYSTEM_PACKAGE_VERSIONS', [
                'APP_VERSION' => env('APP_VERSION', 'v0.0.1'),
                'MODULARITY_VERSION' => env('MODULARITY_VERSION', 'Not Found'),
                'PAYABLE_VERSION' => env('PAYABLE_VERSION', 'Not Found'),
                'SNAPSHOT_VERSION' => env('SNAPSHOT_VERSION', 'Not Found'),
            ]);
        });

        view()->composer('*', Urls::class);

        if (config($this->baseKey . '.enabled.users-management')) {
            View::composer(['admin.*', "$this->baseKey::*"], CurrentUser::class);
        }

        if (config($this->baseKey . '.enabled.media-library')) {
            View::composer("$this->baseKey::layouts.master", MediasUploaderConfig::class);
        }

        if (config($this->baseKey . '.enabled.file-library')) {
            View::composer("$this->baseKey::layouts.master", FilesUploaderConfig::class);
        }

        // View::composer("$this->baseKey::partials.navigation.*", ActiveNavigation::class);

        View::composer(['admin.*', 'templates.*', "$this->baseKey::*"], function ($view) {
            $with = array_merge([
                'renderForBlocks' => false,
                'renderForModal' => false,
            ], $view->getData());

            return $view->with($with);
        });

        View::composer(["$this->baseKey::layouts.master", "$this->baseKey::auth.layout"], Localization::class);
    }

    /**
     * Registers the package additional View Composers.
     */
    private function bootBaseViewComponents(): void
    {
        // Blade::component('table', Table::class);

    }

    /**
     * {@inheritdoc}
     */
    private function resolveCommands(): array
    {
        $cmds = [];

        foreach (glob(__DIR__ . '/../Console/*.php') as $cmd) {
            preg_match("/[^\/]+(?=\.[^\/.]*$)/", $cmd, $match);

            if (count($match) == 1 && ! preg_match('#(.*?)(BaseCommand)(.*?)#', $cmd)) {
                $cmds[] = preg_match('|' . preg_quote($this->terminalNamespace, '|') . '|', $match[0])
                            ? $cmd
                            : "{$this->terminalNamespace}\\{$match[0]}";
            }
        }

        return $cmds;
    }

    private function setLocalDiskUrl($type): void
    {
        config([
            'filesystems.disks.' . unusualBaseKey() . '_' . $type . '_library.url' => request()->getScheme()
            . '://'
            // . str_replace(['http://', 'https://'], '', config('app.url'))
            . request()->getHttpHost()
            . '/storage/'
            . trim(unusualConfig($type . '_library.local_path'), '/ '),
        ]);
    }

    public function mergeKeysFromConfig(array $mergeKeys = [])
    {
        foreach (config($this->baseKey) as $name => $array) {
            if (in_array($name, $mergeKeys)) {
                $this->app['files']->put(__DIR__ . "/../../config/merges/{$name}.php", php_array_file_content($array));
            }
        }
    }
}

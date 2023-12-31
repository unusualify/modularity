<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Activators\FileActivator;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Modularity;
use Unusualify\Modularity\Services\View\UNavigation;

use Illuminate\Support\Facades\View;
use Unusualify\Modularity\Http\ViewComposers\CurrentUser;
use Unusualify\Modularity\Http\ViewComposers\FilesUploaderConfig;
use Unusualify\Modularity\Http\ViewComposers\Localization;
use Unusualify\Modularity\Http\ViewComposers\MediasUploaderConfig;
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
        $this->app->singleton('unusual.modularity', function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new Modularity($app, $path);
        });

        // $this->app->singleton(FileActivator::class, function ($app) {
        $this->app->singleton('unusual.activator', function ($app) {
            // echo 'unusual.activator' . "</br>";
            return new FileActivator($app);
        });

        // $this->app->singleton('unusual.ge', function ($app) {
        //     return new FileActivator($app);
        // });

        $this->app->singleton('unusual.navigation', UNavigation::class);
        // $this->app->alias(\Unusualify\Modularity\Contracts\RepositoryInterface::class, 'ue_modules');
        $this->app->alias('unusual.modularity', 'modularity');

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
        foreach (glob( __DIR__ . '/../Helpers/*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * {@inheritdoc}
     */
    private function registerBaseConfigs()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', $this->baseKey
        );

        $this->mergeConfigFrom(__DIR__ . '/../Config/input_drafts.php', $this->baseKey . '.input_drafts');
        $this->mergeConfigFrom(__DIR__ . '/../Config/media_library.php', $this->baseKey . '.media_library');
        $this->mergeConfigFrom(__DIR__ . '/../Config/file_library.php', $this->baseKey . '.file_library');
        $this->mergeConfigFrom(__DIR__ . '/../Config/imgix.php', $this->baseKey . '.imgix');
        $this->mergeConfigFrom(__DIR__ . '/../Config/glide.php', $this->baseKey . '.glide');
        $this->mergeConfigFrom(__DIR__ . '/../Config/disks.php', 'filesystems.disks');
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
        $this->app->singleton('translation.loader', function ($app) {
            return new \Illuminate\Translation\FileLoader($app['files'], [__DIR__.'/../../lang',  $app['path.lang']]);
            // return new \Illuminate\Translation\FileLoader($app['files'], [__DIR__.'/../../laravel-lang',  $app['path.lang']]);
        });

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];
            $trans = new \Illuminate\Translation\Translator($loader, $locale);
            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

    /**
     * {@inheritdoc}
     */
    private function bootPackageConfigs()
    {
        if (unusualConfig('enabled.users-management')) {
            config(['auth.providers.unusual_users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ]]);

            config(['auth.guards.unusual_users' => [
                'driver' => 'session',
                'provider' => 'unusual_users',
            ]]);

            if (blank(config('auth.passwords.unusual_users'))) {
                config(['auth.passwords.unusual_users' => [
                    'provider' => 'unusual_users',
                    'table' => config($this->baseKey . '.password_resets_table', 'password_resets'),
                    'expire' => 60,
                    'throttle' => 60,
                ]]);
            }
        }

        // Nwidart/laravel-modules scan enabled & scan path addition
        $scan_paths = config('modules.scan.paths');
        array_push($scan_paths, base_path( unusualConfig('vendor_path') . '/src/UModules'));
        config([
            'modules.scan.enabled' => true,
            'modules.scan.paths' => $scan_paths
        ]);

        if (!app()->runningInConsole()) {
            config([
                'modules.cache.enabled' => true,
                'modules.cache.key' => 'modularity',
                'modules.cache.lifetime' => 600
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    private function bootMacros(){

        \Illuminate\Support\Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    private function bootBaseMigrations(){
        // LOAD BASE MIGRATIONS
        $this->loadMigrationsFrom(
            base_path( config( $this->baseKey . '.vendor_path') . '/src/Database/Migrations/default' )
        );
    }

    /**
     * {@inheritdoc}
     */
    private function bootBaseViews(){

        // LOAD BASE VIEWS
        $this->loadViewsFrom(
            array_merge(
                $this->getPublishableViewPaths($this->baseKey),
                [$this->viewSourcePath]
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
     *
     * @return void
     */
    private function bootBaseViewComposers(): void
    {
        view()->composer('*',function($view) {
            $view->with('BASE_KEY', $this->baseKey);
        });

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
     *
     * @return void
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
        // dd(glob(__DIR__."/../Console/*.php"));
        foreach (glob(__DIR__."/../Console/*.php") as $cmd) {
            preg_match("/[^\/]+(?=\.[^\/.]*$)/", $cmd, $match);

            if(count($match) == 1 && !preg_match('#(.*?)(BaseCommand)(.*?)#', $cmd)){
                $cmds[] = preg_match("|" . preg_quote($this->terminalNamespace, "|") . "|", $match[0])
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
            . str_replace(['http://', 'https://'], '', config('app.url'))
            . '/storage/'
            . trim(config(unusualBaseKey() . '.' . $type . '_library.local_path'), '/ '),
        ]);
    }
}

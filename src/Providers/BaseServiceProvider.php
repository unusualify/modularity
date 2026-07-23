<?php

namespace Unusualify\Modularous\Providers;

use App\Exceptions\Handler;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Torann\GeoIP\Facades\GeoIP;
use Unusualify\Modularous\Brokers\RegisterBrokerManager;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Exceptions\AuthConfigurationException;
use Unusualify\Modularous\Facades\ModularousVite;
use Unusualify\Modularous\Http\Middleware\HandleInertiaRequests;
use Unusualify\Modularous\Http\ViewComposers\CurrentUser;
use Unusualify\Modularous\Http\ViewComposers\FilesUploaderConfig;
use Unusualify\Modularous\Http\ViewComposers\Localization;
use Unusualify\Modularous\Http\ViewComposers\MediasUploaderConfig;
use Unusualify\Modularous\Http\ViewComposers\Urls;
use Unusualify\Modularous\Logging\ModularousLogHandler;
use Unusualify\Modularous\Modularous;
use Unusualify\Modularous\Services\BulkCsv\BulkCsvImportOrchestrator;
use Unusualify\Modularous\Services\BulkCsv\BulkImportService;
use Unusualify\Modularous\Services\CacheRelationshipGraph;
use Unusualify\Modularous\Services\Currency\NullCurrencyProvider;
use Unusualify\Modularous\Services\Currency\SystemPricingCurrencyProvider;
use Unusualify\Modularous\Services\CurrencyExchangeService;
use Unusualify\Modularous\Services\FilepondManager;
use Unusualify\Modularous\Services\MigrationBackup;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Services\RedirectService;
use Unusualify\Modularous\Services\UtmParameters;
use Unusualify\Modularous\Services\View\ModularousNavigation;
use Unusualify\Modularous\Support\CommandDiscovery;
use Unusualify\Modularous\Support\FileLoader;
use Unusualify\Modularous\Support\HostRouteRegistrar;
use Unusualify\Modularous\Support\HostRouting;
use Unusualify\Modularous\Translation\Translator;
use Unusualify\Modularous\Validation\Validator as ModularousValidator;

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

        if (modularousConfig('enabled.media-library')) {
            $this->app->singleton('imageService', function () {
                return $this->app->make(config($this->baseKey . '.media_library.image_service'));
            });
        }
        if (
            modularousConfig('media_library.endpoint_type') === 'local'
            && modularousConfig('media_library.disk') === modularousBaseKey() . '_media_library'
        ) {
            $this->setLocalDiskUrl('media');
        }

        if (modularousConfig('enabled.file-library')) {
            $this->app->singleton('fileService', function () {
                return $this->app->make(config($this->baseKey . '.file_library.file_service'));
            });
        }
        if (modularousConfig('file_library.endpoint_type') === 'local'
            && modularousConfig('file_library.disk') === modularousBaseKey() . '_file_library') {
            $this->setLocalDiskUrl('file');
        }

        $this->bootMacros();

        $this->bootBaseMigrations();

        $this->bootBaseViews();

        $this->bootBaseTranslation();

        $this->bootBaseViewComposers();

        $this->bootBaseViewComponents();

        $this->bootModularousLogChannel();

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url(route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        AboutCommand::add('Modularous', function () {
            return [
                // 'Mode' => $this->app['modularous']->isDevelopment() ? 'development' : 'production',
                'Cache' => $this->app['modularous']->config('cache.enabled') ? 'enabled' : 'disabled',
                'Scan' => $this->app['modularous']->config('scan.enabled') ? 'enabled' : 'disabled',
                'Theme' => modularousConfig('app_theme'),
                'Url' => $this->app['modularous']->getAppUrl(),
                'Url (Admin)' => $this->app['modularous']->getAdminAppUrl(),
                'Vendor' => $this->app['modularous']->getVendorDir(),
                'Version' => get_package_version('unusualify/modularous'),
            ];
        });

        // Register scheduler class instead of direct command
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('modularous:fileponds:scheduler --days=7')
                ->daily();
            // ->everyFiveMinutes();
            // ->appendOutputTo(storage_path('logs/scheduler.log'));

            $schedule->command('telescope:prune --hours=168')
                ->daily()
                ->appendOutputTo(storage_path('logs/scheduler.log'));

            $schedule->command('modularous:scheduler:chatable')
                ->everyMinute();
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

        // Register the modularous exception handler
        $this->registerExceptionHandler();

        // $this->app->singleton('modularous', function (Application $app) {
        //     $path = $app['config']->get('modules.paths.modules');

        //     return new Modularous($app, $path);
        // });
        // CANCEL \Nwidart\Modules\Laravel\LaravelFileRepository binding
        // and Nwidart\Modules\Laravel\Module binding in the LaravelFileRepository createModule method
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new Modularous($app, $path);
        });
        $this->app->alias(RepositoryInterface::class, Modularous::class);
        $this->app->alias(RepositoryInterface::class, 'modularous');

        // $this->app->singleton(FileActivator::class, function ($app) {
        // $this->app->singleton('modularous.activator', function (Application $app) {
        //     return new ModuleActivator($app);
        // });

        $this->app->singleton('modularous.navigation', ModularousNavigation::class);

        $this->app->singleton('model.relation.namespace', function () {
            return "Illuminate\Database\Eloquent\Relations";
        });

        $this->app->singleton('model.relation.pattern', function () {
            $relationNamespace = app('model.relation.namespace');

            return '|' . preg_quote($relationNamespace, '|') . '|';
        });

        $this->app->singleton('unusualify.hosting', function (Application $app) {
            // return new \Unusualify\Modularous\Support\HostRouting($app, modularousConfig('app_url'));
            return new HostRouting($app, $app['modularous']->getAppHost());
        });

        $this->app->singleton('unusualify.hostRouting', function (Application $app) {
            // return new \Unusualify\Modularous\Support\HostRouteRegistrar($app, modularousConfig('app_url'));
            return new HostRouteRegistrar($app, $app['modularous']->getAppHost());
        });

        $this->app->singleton('Filepond', function (Application $app) {
            return new FilepondManager;
        });

        $this->app->singleton('currency.exchange', function (Application $app) {
            return new CurrencyExchangeService;
        });

        $this->app->singleton(CurrencyProviderInterface::class, function (Application $app) {
            $providerClass = config('modularous.currency_provider', null);
            if ($providerClass && class_exists($providerClass)) {
                return $app->make($providerClass);
            }
            $systemPricing = new SystemPricingCurrencyProvider;
            if ($systemPricing->isAvailable()) {
                return $systemPricing;
            }

            return new NullCurrencyProvider;
        });

        $this->app->singleton('modularous.relationship.graph', function (Application $app) {
            return new CacheRelationshipGraph;
        });

        $this->app->singleton('modularous.cache', function (Application $app) {
            return new ModularousCacheService;
        });

        $this->app->singleton(UrlPresentationCacheStoreInterface::class, function (Application $app) {
            return $app->make('modularous.cache')->getUrlPresentationCacheStore();
        });

        $this->app->singleton('migration.backup', function (Application $app) {
            return new MigrationBackup;
        });

        $this->app->singleton(BulkCsvImportOrchestrator::class);

        $this->app->singleton(BulkImportService::class);

        $this->app->singleton('modularous.redirect', function (Application $app) {
            return new RedirectService;
        });

        $this->app->singleton('modularous.utm', function (Application $app) {
            return new UtmParameters($app['request']);
        });

        $this->app->singleton('auth.register', function (Application $app) {
            return new RegisterBrokerManager($app);
        });

        $this->app->alias(ModularousVite::class, 'ModularousVite');

        $this->app->alias(GeoIP::class, 'GeoIP');

        // Register Inertia middleware
        $this->app->singleton('inertia.middleware', HandleInertiaRequests::class);

        $this->app->register(TelescopeServiceProvider::class);

        $this->registerTranslationService();

        $this->registerValidationFactoryResolver();
    }

    /**
     * Register the modularous exception handler
     */
    private function registerExceptionHandler(): void
    {
        // Register our modularous exception handler
        $this->app->extend(ExceptionHandler::class, function ($handler, $app) {
            // If the current handler is the default app handler, wrap it with modularous functionality
            if (get_class($handler) === Handler::class) {
                if ($app['modularous']->isPanelUrl()) {
                    return new \Unusualify\Modularous\Exceptions\Handler($app);
                }
            }

            // Otherwise, keep the existing handler
            return $handler;
        });
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
            $ignoredModularousPath = base_path('modularous/lang');
            $hasIgnoredModularousPath = file_exists($ignoredModularousPath);
            if ($hasIgnoredModularousPath) {
                $app->useLangPath($ignoredModularousPath);
            }

            $paths = [
                base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'),
                realpath(__DIR__ . '/../../lang'),
            ];

            if ($hasIgnoredModularousPath && file_exists(base_path('lang'))) {
                $paths[] = base_path('lang');
            } elseif (! $hasIgnoredModularousPath && ! file_exists(base_path('lang'))) {
                $app->useLangPath(realpath(__DIR__ . '/../../lang'));
            }

            $paths[] = $app['path.lang'];

            return new FileLoader($app['files'], $paths);
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
     * Use a Validator that normalizes {placeholder} lines to :placeholder before Laravel replaces values.
     */
    public function registerValidationFactoryResolver(): void
    {
        $this->callAfterResolving('validator', function ($factory) {
            $factory->resolver(function ($translator, $data, $rules, $messages, $attributes) {
                return new ModularousValidator($translator, $data, $rules, $messages, $attributes);
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    private function bootPackageConfigs()
    {
        if (modularousConfig('enabled.users-management') && ! $this->app->runningInConsole()) {
            $modularousAuthGuardAbsent = blank(config('auth.guards.' . Modularous::getAuthGuardName()));
            $modularousAuthProviderAbsent = blank(config('auth.providers.' . Modularous::getAuthProviderName()));
            $modularousAuthPasswordAbsent = blank(config('auth.passwords.' . Modularous::getAuthProviderName()));

            if ($modularousAuthGuardAbsent) {
                throw AuthConfigurationException::guardMissing();
            }

            if ($modularousAuthProviderAbsent) {
                throw AuthConfigurationException::providerMissing();
            }

            if ($modularousAuthPasswordAbsent) {
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

        // if (! config('modules.scan.enabled')) {
        //     throw new \Exception('Modules scan is not enabled, set scan.enabled to true in config/modules.php');
        // }

    }

    /**
     * {@inheritdoc}
     */
    private function bootMacros()
    {
        Str::macro('modularousSlug', function ($string, $separator = '-', $language = 'en', ?array $dictionary = null) {
            $dictionary = array_merge(Lang::get('slug-dictionary', locale: $language), $dictionary ?? []);

            if (array_key_exists($separator, $dictionary)) {
                unset($dictionary[$separator]);
            }

            return Str::slug($string, $separator, language: null, dictionary: $dictionary);
        });

        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });

        Request::macro('getCachedUserCurrency', function () {
            if ($session = Session::get('user-currency')) {
                return config('priceable.models.currency')::find($session);
            }

            $currency = app(CurrencyProviderInterface::class)->findById(config('priceable.defaults.currencies'));
            if (! $currency) {
                $currency = config('priceable.models.currency')::first();
            }

            return $currency;
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
        // Avoid Modularous facade here: package:discover may run before Nwidart binds ActivatorInterface.
        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations/default'
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
                [resource_path('views/vendor/modularous')],
                [$this->viewSourcePath],
            ),
            $this->baseKey
        );
    }

    private function bootBaseTranslation()
    {

        // $name = snakeCase( config($this->baseKey . '.name') );
        $name = modularousBaseKey();
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
            $view->with('MODULAROUS_VIEW_NAMESPACE', $this->baseKey);
            $view->with('SYSTEM_PACKAGE_VERSIONS', [
                'APP_VERSION' => env('APP_VERSION', 'v0.0.1'),
                'MODULAROUS_VERSION' => env('MODULAROUS_VERSION', 'Not Found'),
                'PAYABLE_VERSION' => env('PAYABLE_VERSION', 'Not Found'),
                'SNAPSHOT_VERSION' => env('SNAPSHOT_VERSION', 'Not Found'),
                'COMPOSER' => env('COMPOSER', 'Not Found'),
            ]);
        });

        view()->composer('*', Urls::class);

        if (config($this->baseKey . '.enabled.users-management')) {
            View::composer(['admin.*', "$this->baseKey::*"], CurrentUser::class);
        }

        if (config($this->baseKey . '.enabled.media-library')) {
            View::composer(["$this->baseKey::layouts.master", "$this->baseKey::layouts.app-inertia"], MediasUploaderConfig::class);
        }

        if (config($this->baseKey . '.enabled.file-library')) {
            View::composer(["$this->baseKey::layouts.master", "$this->baseKey::layouts.app-inertia"], FilesUploaderConfig::class);
        }

        // View::composer("$this->baseKey::partials.navigation.*", ActiveNavigation::class);

        View::composer(['admin.*', 'templates.*', "$this->baseKey::*"], function ($view) {
            $with = array_merge([
                'renderForBlocks' => false,
                'renderForModal' => false,
            ], $view->getData());

            return $view->with($with);
        });

        View::composer(["$this->baseKey::layouts.master", "$this->baseKey::auth.layout", "$this->baseKey::layouts.app-inertia"], Localization::class);
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
        $paths = [
            __DIR__ . '/../Console/*.php',
            __DIR__ . '/../Console/Make/*.php',
            __DIR__ . '/../Console/Cache/*.php',
            __DIR__ . '/../Console/Migration/*.php',
            __DIR__ . '/../Console/Module/*.php',
            __DIR__ . '/../Console/Setup/*.php',
            __DIR__ . '/../Console/Sync/*.php',
            __DIR__ . '/../Console/Operations/*.php',
            __DIR__ . '/../Console/Flush/*.php',
            __DIR__ . '/../Console/Update/*.php',
            __DIR__ . '/../Console/Docs/*.php',
            __DIR__ . '/../Schedulers/*.php',
        ];

        return CommandDiscovery::discover($paths);
    }

    private function setLocalDiskUrl($type): void
    {
        config([
            'filesystems.disks.' . modularousBaseKey() . '_' . $type . '_library.url' => request()->getScheme()
            . '://'
            // . str_replace(['http://', 'https://'], '', config('app.url'))
            . request()->getHttpHost()
            . '/storage/'
            . trim(modularousConfig($type . '_library.local_path'), '/ '),
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

    private function bootModularousLogChannel()
    {
        $this->addModularousLogChannels();

        $this->app->singleton('modularous.log', function () {
            return Log::channel('modularous');
        });
    }

    private function addModularousLogChannels()
    {
        $this->app['config']->set('logging.channels.modularous', [
            'driver' => 'monolog',
            'handler' => ModularousLogHandler::class,
            'handler_with' => [
                'level' => env('MODULAROUS_LOG_LEVEL', 'debug'),
                'maxFiles' => 14, // Keep 14 days of logs
            ],
        ]);
        $this->app['config']->set('logging.channels.modularous-notification-failure', [
            'driver' => 'daily',
            'path' => storage_path('logs/modularous-notification-failure.log'),
            'level' => env('MODULAROUS_NOTIFICATION_FAILURE_LOG_LEVEL', 'error'),
            'days' => 14,
        ]);

        if (! config('logging.channels.modularous-resource-cache')) {
            $this->app['config']->set('logging.channels.modularous-resource-cache', [
                'driver' => 'daily',
                'path' => storage_path('logs/modularous-resource-cache.log'),
                'level' => env('LOG_LEVEL', 'info'),
                'days' => env('LOG_DAILY_DAYS', 10),
                'replace_placeholders' => true,
            ]);
        }

        if (! config('logging.channels.scheduler')) {
            $this->app['config']->set('logging.channels.scheduler', [
                'driver' => 'single',
                'path' => storage_path('logs/scheduler.log'),
                'level' => env('LOG_LEVEL', 'info'),
                'replace_placeholders' => true,
            ]);
        }
    }
}

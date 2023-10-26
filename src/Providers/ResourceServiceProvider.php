<?php

namespace OoBook\CRM\Base\Providers;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\View;
use OoBook\CRM\Base\Http\ViewComposers\ActiveNavigation;
use OoBook\CRM\Base\Http\ViewComposers\CurrentUser;
use OoBook\CRM\Base\Http\ViewComposers\FilesUploaderConfig;
use OoBook\CRM\Base\Http\ViewComposers\Localization;
use OoBook\CRM\Base\Http\ViewComposers\MediasUploaderConfig;

use Illuminate\Support\Facades\Lang;

class ResourceServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerTranslations();

        $this->bootViews();

        $this->bootTranslations();

        $this->bootComponents();

        $this->addViewComposers();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function bootViews()
    {
        $sourcePath = __DIR__ .  '/../Resources/views';
        // dd(
        //     $this->baseKey,
        //     $sourcePath,
        //     $this->getPublishableViewPaths($this->baseKey),
        // );

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($this->baseKey), [$sourcePath]), $this->baseKey);

        foreach(Module::all() as $module){
            // if( $module->getName() != 'Base' && $module->isStatus(true)){
            if( $module->isStatus(true)){
                // $viewPath = resource_path('views/modules/' . $module->getLowerName());

                $sourcePath = module_path($module->getName(), 'Resources/views');

                // $this->publishes([
                //     $sourcePath => $viewPath
                // ], ['views', $module->getLowerName() . '-module-views']);
                $this->loadViewsFrom(
                    array_merge(
                        $this->getPublishableViewPaths(
                            snakeCase($module->getName())
                        ),
                        [$sourcePath]
                    ),
                    snakeCase($module->getName())
                );
            }
        }
    }

    /**
     * Boot translations.
     *
     * @return void
     */
    public function bootTranslations()
    {

        $this->bootUnusualTranslation();

        foreach(Module::all() as $module){
            if(  $module->isStatus(true) && $module->getLowerName() != 'base' ){
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
        // $langPath = resource_path('lang/modules/' . $this->baseKey);

        // if (is_dir($langPath)) {
        //     $this->loadTranslationsFrom($langPath, $this->baseKey);
        // } else {
        //     $this->loadTranslationsFrom(module_path($this->baseName, 'Resources/lang'), $this->baseKey);
        // }
    }

    private function bootUnusualTranslation()
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

    public function registerTranslations()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new \Illuminate\Translation\FileLoader($app['files'], [__DIR__.'/../../lang',  $app['path.lang']]);
            return new \Illuminate\Translation\FileLoader($app['files'], [__DIR__.'/../../laravel-lang',  $app['path.lang']]);
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
    private function getPublishableViewPaths($name): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $name)) {
                $paths[] = $path . '/modules/' . $name;
            }
        }
        return $paths;
    }

    /**
     * {@inheritdoc}
     */
    private function bootComponents(){
        // Blade::component('table', Table::class);
    }

    /**
     * Registers the package additional View Composers.
     *
     * @return void
     */
    private function addViewComposers(): void
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



}

<?php

namespace OoBook\CRM\Base\Providers;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\View;
use OoBook\CRM\Base\Http\ViewComposers\ActiveNavigation;
use OoBook\CRM\Base\Http\ViewComposers\CurrentUser;
use OoBook\CRM\Base\Http\ViewComposers\FilesUploaderConfig;
use OoBook\CRM\Base\Http\ViewComposers\Localization;
use OoBook\CRM\Base\Http\ViewComposers\MediasUploaderConfig;

class ResourceServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

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
     * Register translations.
     *
     * @return void
     */
    public function bootTranslations()
    {
        $this->bootUnusualTranslation();

        foreach(Module::all() as $module){
            if(  $module->isStatus(true) && $module->getLowerName() != 'base' ){
                $langPath = resource_path('lang/modules/' . $module->getLowerName());

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

        View::composer("$this->baseKey::partials.navigation.*", ActiveNavigation::class);

        View::composer(['admin.*', 'templates.*', "$this->baseKey::*"], function ($view) {
            $with = array_merge([
                'renderForBlocks' => false,
                'renderForModal' => false,
            ], $view->getData());

            return $view->with($with);
        });

        View::composer(["$this->baseKey::layouts.master"], Localization::class);
    }

    private function bootUnusualTranslation()
    {
        $name = lowerName( config($this->baseKey . '.name') );
        $langPath = resource_path('lang/modules/' . 'base');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $name);
        } else {
            $this->loadTranslationsFrom(
                // module_path('Base', 'Resources/lang'),
                __DIR__ .  '/../Resources/lang',
                $name
            );
        }
    }

}

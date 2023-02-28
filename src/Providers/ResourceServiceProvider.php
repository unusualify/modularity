<?php

namespace Unusual\CRM\Base\Providers;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\View;
use Unusual\CRM\Base\Http\ViewComposers\ActiveNavigation;
use Unusual\CRM\Base\Http\ViewComposers\CurrentUser;
use Unusual\CRM\Base\Http\ViewComposers\FilesUploaderConfig;
use Unusual\CRM\Base\Http\ViewComposers\Localization;
use Unusual\CRM\Base\Http\ViewComposers\MediasUploaderConfig;

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
        //     $this->moduleNameLower,
        //     $sourcePath,
        //     $this->getPublishableViewPaths($this->moduleNameLower),
        // );
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($this->moduleNameLower), [$sourcePath]), $this->moduleNameLower);

        foreach(Module::all() as $module){
            // if( $module->getName() != 'Base' && $module->isStatus(true)){
            if( $module->isStatus(true)){
                // $viewPath = resource_path('views/modules/' . $module->getLowerName());

                $sourcePath = module_path($module->getName(), 'Resources/views');

                // $this->publishes([
                //     $sourcePath => $viewPath
                // ], ['views', $module->getLowerName() . '-module-views']);
                $this->loadViewsFrom(array_merge($this->getPublishableViewPaths($module->getLowerName()), [$sourcePath]), $module->getLowerName());
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
        // $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        // if (is_dir($langPath)) {
        //     $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        // } else {
        //     $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        // }
    }

    /**
     * {@inheritdoc}
     */
    private function getPublishableViewPaths($lower_module_name): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $lower_module_name)) {
                $paths[] = $path . '/modules/' . $lower_module_name;
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
        if (config($this->moduleNameLower . '.enabled.users-management')) {
            View::composer(['admin.*', 'base::*'], CurrentUser::class);
        }

        if (config($this->moduleNameLower . '.enabled.media-library')) {
            View::composer('base::layouts.master', MediasUploaderConfig::class);
        }

        if (config($this->moduleNameLower . '.enabled.file-library')) {
            View::composer('base::layouts.master', FilesUploaderConfig::class);
        }

        View::composer('base::partials.navigation.*', ActiveNavigation::class);

        View::composer(['admin.*', 'templates.*', 'base::*'], function ($view) {
            $with = array_merge([
                'renderForBlocks' => false,
                'renderForModal' => false,
            ], $view->getData());

            return $view->with($with);
        });

        View::composer(['base::layouts.master'], Localization::class);
    }

    private function bootUnusualTranslation()
    {
        $name = lowerName( config($this->moduleNameLower . '.name') );
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






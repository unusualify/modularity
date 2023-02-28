<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Support\Facades\Route;
use Unusual\CRM\Base\Activators\FileActivator;
use Unusual\CRM\Base\UnusualFileRepository;

class BaseServiceProvider extends ServiceProvider
{

    /**
     * Namespace of the terminal commands
     * @var string
     */
    protected $terminalNamespace = "OoBook\\CRM\\Base\\Console";

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadMigrationsFrom(
            base_path( config( $this->moduleNameLower . '.vendor_path') . '/src/Database/Migrations' )
        );

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerHelpers();

        $this->macros();

        $this->commands($this->resolveCommands());

        $this->app->singleton(Contracts\RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('modules.paths.modules');

            return new UnusualFileRepository($app, $path);
        });

        $this->app->singleton(FileActivator::class, function ($app) {
            return new FileActivator($app);
        });

        if (config($this->moduleNameLower . '.enabled.media-library')) {
            $this->app->singleton('imageService', function () {
                return $this->app->make(config($this->moduleNameLower . '.media_library.image_service'));
            });
        }

        if (config($this->moduleNameLower . '.enabled.file-library')) {
            $this->app->singleton('fileService', function () {
                return $this->app->make(config($this->moduleNameLower . '.file_library.file_service'));
            });
        }

        $this->app->alias(Contracts\RepositoryInterface::class, 'ue_modules');
        $this->app->alias(FileActivator::class, 'module_activator');

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
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
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
    private function macros(){

        Route::macro('moduleRoutes', function ($file = null) {

            if(!$file){
                $pattern = '/[M|m]odules\/[A-Za-z]*\/Routes\//';

                $file = fileTrace($pattern);
            }
            // echo $file;

            Route::middleware('auth')->group( function() use($file){


                Route::name( getCurrentModuleLowerName($file).'.')
                    ->group(function() use($file){

                    if( is_array( $parent = config( getCurrentModuleLowerName($file).'.parent_route' ) ) ){
                        $url = $parent['url'] ?? lowerName($parent['name']);
                        $studlyName = studlyName($parent['name']);
                        // $names = $value['route_name'] ?? $url;
                        // dd(request());
                        Route::resource($url, $studlyName.'Controller');
                    }
                    Route::prefix( getCurrentModuleLowerName($file) )->group(function () use($file){

                        if( is_array(config( getCurrentModuleLowerName($file).'.sub_routes' ))){
                            foreach( config( getCurrentModuleLowerName($file).'.sub_routes' ) as $key => $value) {
                                $url = $value['url'] ?? lowerName($value['name']);
                                $studlyName = studlyName($value['name']);
                                $names = $value['route_name'] ?? $url;
                                // dd(request());
                                Route::resource($url, $studlyName.'Controller' , ['names' => $names]);
                            }
                        }

                    });
                });

                Route::resource( getCurrentModuleLowerName($file) , getCurrentModuleStudlyName($file).'Controller');

                Route::prefix('api')
                    ->name('api.')
                    ->namespace('API')
                    ->group(function() use($file){

                    // Route::apiResource( getCurrentModuleLowerName($file) , getCurrentModuleStudlyName($file).'Controller');
                    if( is_array( $parent = config( getCurrentModuleLowerName($file).'.parent_route' ) ) ){
                        $url = $parent['url'] ?? lowerName($parent['name']);
                        $studlyName = studlyName($parent['name']);
                        // $names = $value['route_name'] ?? $url;
                        // dd(request());
                        Route::apiResource($url, $studlyName.'Controller');
                    }
                    Route::prefix( getCurrentModuleLowerName($file) )
                        ->name( getCurrentModuleLowerName($file).'.' )
                        ->group(function() use($file){

                        if( is_array(config( getCurrentModuleLowerName($file).'.sub_routes' ))){
                            foreach( config( getCurrentModuleLowerName($file).'.sub_routes' ) as $value) {
                                $url = $value['url'] ?? lowerName($value['name']);
                                $studlyName = studlyName($value['name']);
                                $names = $value['route_name'] ?? $url;

                                Route::apiResource($url, $studlyName.'Controller', ['names' => $names]);
                            }
                        }
                    });

                });

            });
        });

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
    private function resolveCommands(): array
    {
        $cmds = [];
        // dd(glob(__DIR__."/../Console/*.php"));
        foreach (glob(__DIR__."/../Console/*.php") as $cmd) {
            preg_match("/[^\/]+(?=\.[^\/.]*$)/", $cmd, $match);

            if(count($match) == 1 && !preg_match('#(.*?)(BaseCommand)(.*?)#', $cmd)){
                $cmds[] = preg_match("/{$this->terminalNamespace}/", $match[0])
                            ? $cmd
                            : "{$this->terminalNamespace}\\{$match[0]}";
            }
        }

        return $cmds;
    }

}

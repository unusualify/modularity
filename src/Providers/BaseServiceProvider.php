<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Support\Facades\Route;
use OoBook\CRM\Base\Activators\FileActivator;
use OoBook\CRM\Base\UnusualFileRepository;

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
            base_path( config( $this->baseKey . '.vendor_path') . '/src/Database/Migrations' )
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

        if (config($this->baseKey . '.enabled.media-library')) {
            $this->app->singleton('imageService', function () {
                return $this->app->make(config($this->baseKey . '.media_library.image_service'));
            });
        }

        if (config($this->baseKey . '.enabled.file-library')) {
            $this->app->singleton('fileService', function () {
                return $this->app->make(config($this->baseKey . '.file_library.file_service'));
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
            if (is_dir($path . '/modules/' . $this->baseKey)) {
                $paths[] = $path . '/modules/' . $this->baseKey;
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

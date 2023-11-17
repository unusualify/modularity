<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Database\Eloquent\Factory;

use Nwidart\Modules\Facades\Module;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;

class ConfigServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfigs();
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // $this->publishConfigs();
    }

    /**
     * Merges the package configuration files into the given configuration namespaces.
     *
     * @return void
     */
    private function registerConfigs(): void
    {
        $this->registerUnusualConfig();
        // dd(glob(__DIR__."/../Config/modules/*.php"));
        // $base_config_name = strtolower(config($this->baseKey . '.name'));

        // $this->mergeConfigFrom(__DIR__ . '/../config/unusual.php', 'unusual');

        // $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'unusual');

        // $this->mergeConfigFrom(__DIR__ . '/../Config/system_modules.php', $this->baseKey . '.system_modules');
        $this->mergeConfigFrom(__DIR__ . '/../Config/input_drafts.php', $this->baseKey . '.input_drafts');
        $this->mergeConfigFrom(__DIR__ . '/../Config/media_library.php', $this->baseKey . '.media_library');
        $this->mergeConfigFrom(__DIR__ . '/../Config/file_library.php', $this->baseKey . '.file_library');
        $this->mergeConfigFrom(__DIR__ . '/../Config/imgix.php', $this->baseKey . '.imgix');
        $this->mergeConfigFrom(__DIR__ . '/../Config/glide.php', $this->baseKey . '.glide');
        $this->mergeConfigFrom(__DIR__ . '/../Config/disks.php', 'filesystems.disks');


        if (config($this->baseKey . '.enabled.users-management')) {
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
        config([
            'modules.cache.enabled' => true,
            'modules.cache.key' => 'modularity',
            'modules.cache.lifetime' => 600
        ]);


        if (
            config(unusualBaseKey() . '.media_library.endpoint_type') === 'local'
            && config(unusualBaseKey() . '.media_library.disk') === unusualBaseKey() . '_media_library'
        ) {
            $this->setLocalDiskUrl('media');
        }

        if (config(unusualBaseKey() . '.file_library.endpoint_type') === 'local'
            && config(unusualBaseKey() . '.file_library.disk') === unusualBaseKey() . '_file_library') {
            $this->setLocalDiskUrl('file');
        }


        $this->registerModuleConfigs();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerUnusualConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', $this->baseKey
        );
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

    public function registerModuleConfigs()
    {
        // $module = Modularity::find('SystemUser');

        foreach(Modularity::allEnabled() as $module){
            $this->mergeConfigFrom(
                module_path($module->getName(), 'Config/config.php'), $module->getSnakeName()
            );
            // if( $module->getName() != 'Base' && $module->isStatus(true)){
            //     $this->mergeConfigFrom(
            //         module_path($module->getName(), 'Config/config.php'), $module->getSnakeName()
            //     );
            // }
        }

    }
}

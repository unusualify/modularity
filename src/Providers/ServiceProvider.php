<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Str;
use Illuminate\Contracts\Foundation\CachesConfiguration;


class ServiceProvider extends Provider
{
    /**
     * @var string $moduleName
     */
    protected $baseName;

    /**
     * @var string $moduleNameLower
     */
    protected $baseKey;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->baseName = env('BASE_NAME', 'Base');

        $this->baseKey = Str::snake($this->baseName);
    }

    /**
     *
     *
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');
            // if($key == 'unusual'){
            //     dd(
            //         require $path,
            //         $config->get($key, []),
            //         array_merge(
            //             require $path, $config->get($key, [])
            //         )
            //     );
            // }
            $config->set($key, array_merge_recursive_preserve(
                require $path, $config->get($key, [])
            ));
        }
    }
}

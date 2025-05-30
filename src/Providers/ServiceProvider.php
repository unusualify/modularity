<?php

namespace Unusualify\Modularity\Providers;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Str;

class ServiceProvider extends Provider
{
    /**
     * @var string
     */
    protected $baseName;

    /**
     * @var string
     */
    protected $baseKey;

    /**
     * Namespace of the terminal commands
     *
     * @var string
     */
    protected $terminalNamespace = 'Unusualify\\Modularity\\Console';

    protected $viewSourcePath = __DIR__ . '/../../resources/views';

    /**
     * Create a new service provider instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->baseName = env('MODULARITY_BASE_NAME', 'Modularity');

        $this->baseKey = Str::snake($this->baseName);
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');
            $config->set($key, array_merge_recursive_preserve(
                require $path, $config->get($key, [])
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->baseKey)) {
                $paths[] = $path . '/modules/' . $this->baseKey;
            }
        }

        return $paths;
    }
}

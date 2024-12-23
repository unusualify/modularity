<?php

namespace Unusualify\Modularity;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Json;

class Modularity extends FileRepository
{
    /**
     * @var ConfigRepository
     */
    private $modularityConfig;

    /**
     * @var CacheManager
     */
    private $modularityCache;

    /**
     * The constructor.
     *
     * @param string|null $path
     */
    public function __construct(Container $app, $path = null)
    {
        parent::__construct($app, $path);

        $this->modularityCache = $app['cache'];
        $this->modularityConfig = $app['config'];
    }

    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new \Unusualify\Modularity\Module($args[1], $args[2] ?? null);
    }

    /**
     * Get all modules.
     */
    public function all(): array
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->formatCached($this->getCached());
    }

    /**
     * Format the cached data as array of modules.
     *
     * @param array $cached
     * @return array
     */
    protected function formatCached($cached)
    {
        $modules = [];

        $resetCache = false;
        $basePath = base_path();
        $pathPattern = preg_quote("{$basePath}", '/');

        foreach ($cached as $name => $module) {
            $path = $module['path'];

            if (! preg_match("/{$pathPattern}/", $path)) {
                $resetCache = true;

                break;
            }
            $modules[$name] = $this->createModule($this->app, $name, $path);
        }

        if ($resetCache) {
            return $this->scan();
        }

        return $modules;
    }

    /**
     * Get & scan all modules.
     *
     * @return array
     */
    public function scan()
    {
        $paths = $this->getScanPaths();

        $modules = [];

        foreach ($paths as $key => $path) {
            // dd(
            //     $paths,
            //     $path,
            //     $this->getFiles()->glob(
            //         "{$path}/module.json"
            //     )
            // );
            $manifests = $this->getFiles()->glob("{$path}/module.json");

            is_array($manifests) || $manifests = [];
            foreach ($manifests as $manifest) {
                $name = Json::make($manifest)->get('name');
                // if(preg_match('/oguzhanbukcuoglu/', $manifest)){
                //     dd(
                //         $manifest,
                //         dirname($manifest),
                //         $name,
                //         $manifests,
                //     );
                // }
                $modules[$name] = $this->createModule($this->app, $name, dirname($manifest));

                // dd($path, $manifests, $paths,  Json::make($manifest), $modules);

            }
        }

        return $modules;
    }

    /**
     * Get scanned modules paths.
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;

        $paths[] = $this->getPath();

        if ($this->config('scan.enabled')) {
            $paths = array_merge($this->config('scan.paths'), $paths);
        }

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Get cached modules.
     *
     * @return array
     */
    public function getCached()
    {
        $store = $this->modularityCache->store($this->modularityConfig->get('modules.cache.driver'));

        if ($store->has($this->config('cache.key'))) {
            return $store->get($this->config('cache.key'));
        } else {
            $store->set($this->config('cache.key'), $this->toCollection()->toArray(), $this->config('cache.lifetime'));

            return $this->toCollection()->toArray();
        }
    }

    /**
     * Clear the modules cache if it is enabled
     */
    public function clearCache()
    {
        if (config('modules.cache.enabled') === true) {
            app('cache')->forget(config('modules.cache.key'));
        }
    }

    /**
     * Disable the modules cache
     */
    public function disableCache()
    {
        return config([
            'modules.cache.enabled' => false,
        ]);
    }

    public function getGroupedModules($group_name)
    {
        return array_filter($this->allEnabled(), function ($item) use ($group_name) {
            $module_config = $item->getConfig();

            return isset($module_config['group']) && $module_config['group'] === $group_name;
        });
    }

    public function getSystemModules()
    {
        return $this->getGroupedModules('system');
    }

    public function getModules()
    {
        return array_filter($this->allEnabled(), function ($item) {
            $module_config = $item->getConfig();

            return ! isset($module_config['group']) || ! $module_config['group'];
        });
    }

    public function deleteModule(string $name): bool
    {
        $module = null;

        $this->scan();

        foreach ($this->all() as $moduleInstance) {
            if ($moduleInstance->getStudlyName() === studlyName($name)) {
                $module = $moduleInstance;

                break;
            }
        }

        if ($module) {
            $res = $module->delete();

            if ($res) {
                $this->clearCache();

                return $res;
            }

            return $res;
        }

        return false;
    }

    public function getModels($routeName)
    {
        $models = [];

        foreach ($this->allEnabled() as $key => $module) {
            $entityPath = $module->getDirectoryPath('Entities');
            if (! file_exists($entityPath)) {
                continue;
            }

            foreach ($this->getClasses($entityPath) as $_class) {
                if (get_class_short_name(App::make($_class)) === studlyName($routeName)) {
                    $models[] = $_class;
                }
            }
        }

        return $models;
    }

    public function getClasses($path)
    {
        $classes = [];

        foreach (ClassMapGenerator::createMap($path) as $class => $file) {
            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * Get vendor path.
     *
     * @param string $dir
     * @return string
     */
    public function getVendorPath($dir = '')
    {
        return base_path(concatenate_path(unusualConfig('vendor_path'), $dir));
    }

    /**
     * Get modularity namespace.
     *
     * @param \Nwidart\Modules\Module $module
     * @return string
     */
    public function getVendorNamespace($append = null)
    {
        return concatenate_namespace(unusualConfig('namespace'), $append);
    }
}

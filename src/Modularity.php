<?php

namespace Unusualify\Modularity;

use Illuminate\Container\Container;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Json;
use Illuminate\Support\Str;

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
     * @param Container $app
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
                if(preg_match('/oguzhan/', $manifest)){
                    dd(
                        $manifest,
                        dirname($manifest),
                        $name,
                        $manifests,
                    );
                }
                $modules[$name] = $this->createModule($this->app, $name, dirname($manifest));

                // dd($path, $manifests, $paths,  Json::make($manifest), $modules);

            }
        }

        return $modules;
    }

    /**
     * Get scanned modules paths.
     *
     * @return array
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

        if($store->has($this->config('cache.key'))){
            return $store->get($this->config('cache.key'));
        }else{
            $store->set($this->config('cache.key'), $this->toCollection()->toArray() , $this->config('cache.lifetime'));
            return $this->toCollection()->toArray();
        }
    }

    public function getGroupedModules($group_name) {
        return array_filter($this->allEnabled(), function($item) use($group_name){
            $module_config = $item->getConfig();
            return isset($module_config['group']) && $module_config['group'] === $group_name;
        });
    }

    public function getSystemModules() {
        return $this->getGroupedModules('system');
    }

    public function getModules() {
        return array_filter($this->allEnabled(), function($item) {
            $module_config = $item->getConfig();
            return !isset($module_config['group']) || !$module_config['group'];
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

        if($module){
            return $module->delete();
        }

        return false;
    }

}

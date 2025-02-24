<?php

namespace Unusualify\Modularity\Activators;

use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Nwidart\Modules\Activators\FileActivator;
// use Nwidart\Modules\Module;
use Unusualify\Modularity\Module;

class ModuleActivator extends FileActivator
{
    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    private $cache;

    /**
     * @var Illuminate\Config\Repository
     */
    private $config;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var int
     */
    private $cacheLifetime;

    /**
     * Route Statuses File
     *
     * @var string path
     */
    private $statusesFile;

    /**
     * Array of modules activation statuses
     *
     * @var array
     */
    private $routesStatuses;

    public function __construct(Container $app, Private Module $module)
    {
        // parent::__construct($app);

        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->config = $app['config'];

        $this->cacheKey = $this->generateCacheKey();
        $this->cacheLifetime = 604800;
        $this->statusesFile = $this->module->getDirectoryPath('routes_statuses.json');

        $this->routesStatuses = $this->getRoutesStatuses();
    }

    // public function setModule($module, $path)
    // {
    //     $this->module = $module;

    //     $this->statusesFile = $path . '/' . $this->config('statuses-file');

    //     $this->setCacheKey($this->generateCacheKey());

    //     $this->routesStatuses = $this->getRoutesStatuses();

    //     return $this;
    // }

    public function generateCacheKey()
    {
        $moduleName = (string) $this->module;

        return 'module-activator.installed.' . kebabCase($moduleName);
    }

    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Reads a config parameter under the 'activators.file' key
     *
     * @return mixed
     */
    private function config(string $key, $default = null)
    {
        return $this->config->get(modularityBaseKey() . '.activators.file.' . $key, $default);
    }

    /**
     * Get modules statuses, either from the cache or from
     * the json statuses file if the cache is disabled.
     *
     * @throws FileNotFoundException
     */
    public function getRoutesStatuses(): array
    {
        if (! $this->config->get('modules.cache.enabled')) {
            return $this->readJson();
        }

        return $this->cache->remember($this->getCacheKey(), $this->cacheLifetime, function () {
            return $this->readJson();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function enable($route): void
    {
        $this->setActiveByName($route, true);
    }

    /**
     * {@inheritDoc}
     */
    public function disable($route): void
    {
        $this->setActiveByName($route, false);
    }

    /**
     * {@inheritDoc}
     */
    public function hasStatus($route, bool $status): bool
    {
        if (! isset($this->routesStatuses[$route])) {
            return $status === false;
        }

        return $this->routesStatuses[$route] === $status;
    }

    /**
     * {@inheritDoc}
     */
    public function setActive($route, bool $active): void
    {
        $this->setActiveByName($route, $active);
    }

    /**
     * {@inheritDoc}
     */
    public function setActiveByName(string $name, bool $status): void
    {
        $this->routesStatuses[$name] = $status;
        $this->writeJson();
        $this->flushCache();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($route): void
    {
        if (! isset($this->routesStatuses[$route])) {
            return;
        }
        unset($this->routesStatuses[$route]);
        $this->writeJson();
        $this->flushCache();
    }

    /**
     * Reads the json file that contains the activation statuses.
     *
     * @throws FileNotFoundException
     */
    public function readJson(): array
    {
        if (! $this->files->exists($this->statusesFile)) {
            return [];
        }

        return json_decode($this->files->get($this->statusesFile), true);
    }

    /**
     * Writes the activation statuses in a file, as json
     */
    private function writeJson(): void
    {
        $this->files->put($this->statusesFile, json_encode($this->routesStatuses, JSON_PRETTY_PRINT));
    }

    /**
     * Flushes the modules activation statuses cache
     */
    private function flushCache(): void
    {
        $this->cache->forget($this->cacheKey);
    }

    public function getRoutes()
    {
        return array_keys(json_decode($this->files->get($this->statusesFile), true));
    }
}

<?php

namespace Unusualify\Modularous\Activators;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Nwidart\Modules\Activators\FileActivator;

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

    public function __construct(Container $app, string $cacheKey, string $statusesFile)
    {
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->config = $app['config'];

        $this->cacheKey = $cacheKey;
        $this->cacheLifetime = 604800;
        $this->statusesFile = $statusesFile;

        $this->routesStatuses = $this->getRoutesStatuses();
    }

    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    // /**
    //  * Reads a config parameter under the 'activators.file' key
    //  *
    //  * @return mixed
    //  */
    // private function config(string $key, $default = null)
    // {
    //     return $this->config->get(modularousBaseKey() . '.activators.file.' . $key, $default);
    // }

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
     * {@inheritDoc}
     */
    public function reset(): void
    {
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }
        $this->routesStatuses = [];
        $this->flushCache();
    }

    /**
     * Ensure the routes statuses file exists as an empty object.
     */
    public function ensureFileExists(): void
    {
        if ($this->files->exists($this->statusesFile)) {
            return;
        }

        $this->routesStatuses = [];
        $this->files->put($this->statusesFile, json_encode(new \stdClass(), JSON_PRETTY_PRINT));
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

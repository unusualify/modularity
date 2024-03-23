<?php

namespace Unusualify\Modularity\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Activators\FileActivator as ActivatorsFileActivator;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;
use Illuminate\Support\Str;
class FileActivator extends ActivatorsFileActivator
{

    /**
     * @var module name
     */
    private $module;

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

    public function __construct(Container $app)
    {
        parent::__construct($app);

        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->config = $app['config'];
        $this->cacheKey = 'module-activator.installed';
        $this->cacheLifetime = $this->config('cache-lifetime');

        // $this->statusesFile = $this->config('statuses-file');
        // $this->routesStatuses = $this->getroutesStatuses();

    }

    public function setModule($module, $path)
    {
        $this->module = $module;

        // $this->statusesFile = base_path( config('modules.namespace') . "/" . $this->module ."/" . $this->config('statuses-file')) ;
        $this->statusesFile = $path . '/' .  $this->config('statuses-file');

        $this->routesStatuses = $this->getRoutesStatuses();

        return $this;
    }

    /**
     * Reads a config parameter under the 'activators.file' key
     *
     * @param  string $key
     * @param  $default
     * @return mixed
     */
    private function config(string $key, $default = null)
    {
        return $this->config->get(unusualBaseKey() . '.activators.file.' . $key, $default);
    }

    /**
     * Get modules statuses, either from the cache or from
     * the json statuses file if the cache is disabled.
     * @return array
     * @throws FileNotFoundException
     */
    public function getRoutesStatuses(): array
    {
        if (!$this->config->get(unusualBaseKey() . '.cache.enabled')) {
            return $this->readRoutesJson();
        }

        return $this->cache->remember($this->cacheKey, $this->cacheLifetime, function () {
            return $this->readRoutesJson();
        });
    }

    /**
     * @inheritDoc
     */
    public function enable($route): void
    {
        $this->setActiveByName($route, true);
    }

    /**
     * @inheritDoc
     */
    public function disable($route): void
    {
        $this->setActiveByName($route, false);
    }

    /**
     * @inheritDoc
     */
    public function hasStatus($route, bool $status): bool
    {
        if (!isset($this->routesStatuses[$route])) {
            return $status === false;
        }

        return $this->routesStatuses[$route] === $status;
    }

    /**
     * @inheritDoc
     */
    public function setActive($route, bool $active): void
    {
        $this->setActiveByName($route, $active);
    }

    /**
     * @inheritDoc
     */
    public function setActiveByName(string $name, bool $status): void
    {
        $this->routesStatuses[$name] = $status;
        $this->writeRoutesJson();
        $this->flushRouteCache();
    }

    /**
     * @inheritDoc
     */
    public function delete($route): void
    {
        if (!isset($this->routesStatuses[$route])) {
            return;
        }
        unset($this->routesStatuses[$route]);
        $this->writeJson();
        $this->flushCache();
    }

    /**
     * Reads the json file that contains the activation statuses.
     * @return array
     * @throws FileNotFoundException
     */
    public function readRoutesJson(): array
    {
        if (!$this->files->exists($this->statusesFile)) {
            return [];
        }
        return json_decode($this->files->get($this->statusesFile), true);
    }

    /**
     * Writes the activation statuses in a file, as json
     */
    private function writeRoutesJson(): void
    {
        $this->files->put($this->statusesFile, json_encode($this->routesStatuses, JSON_PRETTY_PRINT));
    }

    /**
     * Flushes the modules activation statuses cache
     */
    private function flushRouteCache(): void
    {
        $this->cache->forget($this->cacheKey);
    }

    public function getRoutes(){
        return array_keys(json_decode($this->files->get($this->statusesFile), true));
    }


}

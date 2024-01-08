<?php

namespace Unusualify\Modularity;

use Illuminate\Container\Container;
use Nwidart\Modules\Laravel\Module as NwidartModule;
use Illuminate\Support\Str;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;

class Module extends NwidartModule
{

    /**
     * @var ModuleActivatorInterface
     */
    private $moduleActivator;

    /**
     * @var ModuleActivatorInterface
     */
    private $config;

    /**
     * The constructor.
     * @param Container $app
     * @param $name
     * @param $path
     */
    public function __construct(string $name, $path = null)
    {
        // dd($path);
        $app = app();
        $path ??= $app['config']->get('modules.paths.modules');
        parent::__construct($app, $name, $path);

        $this->moduleActivator = $app['unusual.activator'];
        $this->moduleActivator->setModule($name);

        // $this->config =
        // dd($this->moduleActivator, $app);
    }

    public function setModuleActivator($name)
    {
        $this->moduleActivator->setModule($name);
    }

    /**
     * Enable the current module route.
     */
    public function enableRoute($route): void
    {
        $this->fireModuleEvent('enabling', $route);

        $this->moduleActivator->enable($route);

        $this->flushModuleCache();

        $this->fireModuleEvent('enabled', $route);
    }

    /**
     * Disable the current module route.
     */
    public function disableRoute($route): void
    {
        $this->fireModuleEvent('disabling', $route);

        $this->moduleActivator->disable($route);

        $this->flushModuleCache();

        $this->fireModuleEvent('disabled', $route);
    }

    /**
     * Register the module's route event.
     *
     * @param string $event
     */
    protected function fireModuleEvent($event, $route): void
    {
        $this->app['events']->dispatch(sprintf('modules.%s.%s' . $event, $this->getLowerName(),$route), [$this]);
    }

    /**
     * Determine whether the current module route activated.
     *
     * @return bool
     */
    public function isEnabledRoute($route) : bool
    {
        return $this->moduleActivator->hasStatus($route, true);
    }

    /**
     *  Determine whether the current module route not disabled.
     *
     * @return bool
     */
    public function isDisabledRoute($route) : bool
    {
        return !$this->isEnabledRoute($route);
    }

    private function flushModuleCache(): void
    {

        if (config(unusualBaseKey() . '.cache.enabled')) {
            $this->cache->store()->flush();
        }
    }

    /**
     * Get directory path.
     *
     * @return string
     */
    public function getDirectoryPath($directory = ''): string
    {
        return $this->getPath() . (empty($directory) ?: "/$directory");
    }

    /**
     * Get specific class namespace of module.
     *
     * @return string
     */
    public function getClassNamespace($class): string
    {
        return  $this->getBaseNamespace() . "\\"  . $class;
    }

    /**
     * Get base namespace of the module.
     *
     * @return string
     */
    public function getBaseNamespace(): string
    {
        return  config('modules.namespace', 'Modules') . "\\" . $this->getStudlyName();
    }

    public function getRouteConfig($route_name){
        return $this->getRouteConfigs( snakeCase($route_name) ) ;
    }

    public function getRouteConfigs($notation = null){
        $notation = !$notation ? $notation : ".{$notation}";

        return $this->getConfig('routes' . $notation);
    }

    public function getConfig($notation = null){
        $notation = !$notation ? $notation : ".{$notation}";
        return $this->app['config']->get("{$this->getSnakeName()}{$notation}");
    }

    public function getParentRoute() {
        return array_values(array_filter($this->getRouteConfigs(), function($r){
            return isset($r['parent']) && $r['parent'];
        }))[0] ?? [];
    }

    public function hasParentRoute() {
        return count($this->getParentRoute()) > 0;
    }

    public function hasSystemPrefix() {
        return $this->getConfig('system_prefix') ?? $this->getConfig('base_prefix', false);
    }

    public function systemPrefix() {
        return systemUrlPrefix();
    }

    public function systemRouteNamePrefix() {
        return systemRouteNamePrefix();
    }

    public function prefix() {
        return $this->hasParentRoute()
            ? $this->getParentRoute()['url']
            : pluralize( kebabCase($this->getConfig('name')) );
    }

    public function fullPrefix() {
        $prefixes = [];

        $adminUrlPrefix = adminUrlPrefix();

        if($adminUrlPrefix)
            $prefixes[] = $adminUrlPrefix;

        if($this->hasSystemPrefix())
            $prefixes[] = $this->systemPrefix();

        $prefixes[] = $this->prefix();

        return implode('/', $prefixes);
    }

    public function routeNamePrefix() {
        return $this->hasParentRoute()
            ? $this->getParentRoute()['route_name']
            : snakeCase($this->getConfig('name'));
    }

    public function fullRouteNamePrefix() {
        $prefixes = [];

        $adminRouteNamePrefix = adminRouteNamePrefix();

        if($adminRouteNamePrefix)
            $prefixes[] = $adminRouteNamePrefix;

        // if($this->hasSystemPrefix())
        //     $prefixes[] = $this->systemRouteNamePrefix();

        $prefixes[] = $this->routeNameprefix();

        return implode('.', $prefixes);
    }

}

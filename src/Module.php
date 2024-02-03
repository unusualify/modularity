<?php

namespace Unusualify\Modularity;

use Illuminate\Container\Container;
use Nwidart\Modules\Laravel\Module as NwidartModule;
use Illuminate\Support\Str;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Activators\FileActivator;
use Unusualify\Modularity\Support\Finder;

class Module extends NwidartModule
{

    /**
     * @var ModuleActivatorInterface
     */
    private $moduleActivator;

    /**
     * @var
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

        // $this->moduleActivator = $app['unusual.activator'];
        $this->moduleActivator = (new FileActivator($app))->setModule($this->getName());
        // $this->moduleActivator->setModule($name);

        // $this->config =
        // dd($this->moduleActivator, $app);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath(): string
    {
        // This checks if we are running on a Laravel Vapor managed instance
        // and sets the path to a writable one (services path is not on a writable storage in Vapor).
        if (!is_null(env('VAPOR_MAINTENANCE_MODE', null))) {
            return Str::replaceLast('config.php', $this->getSnakeName() . '_module.php', $this->app->getCachedConfigPath());
        }

        return Str::replaceLast('services.php', $this->getSnakeName() . '_module.php', $this->app->getCachedServicesPath());
    }

    /**
     * {@inheritdoc}
     */
    public function registerProviders(): void
    {
        (new ProviderRepository($this->app, new Filesystem(), $this->getCachedServicesPath()))
            ->load($this->get('providers', []));
    }

    /**
     * {@inheritdoc}
     */
    public function registerAliases(): void
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
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
     *
     */
    public function getRoutes()
    {
        return $this->moduleActivator->getRoutes();
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

    /**
     * flushModuleCache
     *
     * @return void
     */
    private function flushModuleCache(): void
    {

        if (unusualConfig('cache.enabled')) {
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

    /**
     * getRouteConfigs
     *
     * @param  mixed $notation
     * @return array
     */
    public function getRouteConfigs($notation = null, $valid = false): array
    {
        $notation = !$notation ? $notation : ".{$notation}";

        return ($valid && !$notation) ? Arr::where($this->getConfig('routes' . $notation), function($item,$key) use($notation){
            return !(!isset($item['name']) || !$this->isRouteTableExists($item['name'], $key));
        }) : $this->getConfig('routes' . $notation);
    }

    /**
     * getRouteConfig
     *
     * @param  mixed $route_name
     * @return array
     */
    public function getRouteConfig($route_name): array
    {
        return $this->getRouteConfigs( snakeCase($route_name) ) ;
    }

    /**
     * getRouteInput
     *
     * @param  mixed $route_name
     * @param  mixed $input_name
     * @return array
     */
    public function getRouteInput($route_name, $input_name = null): array
    {
        $inputs = $this->getRouteConfig($route_name)['inputs'];
        return $this->getRouteConfig($route_name)['inputs'];
    }

    /**
     * getConfig
     *
     * @param  mixed $notation
     * @return mixed
     */
    public function getConfig($notation = null): mixed
    {
        $notation = !$notation ? '' : ".{$notation}";

        if(!$this->app['config']->has($this->getSnakeName()) && $this->app->runningInConsole() && file_exists($this->getDirectoryPath('Config/config.php'))){
            $this->app['config']->set("{$this->getSnakeName()}", include($this->getDirectoryPath('Config/config.php')));
        }

        return $this->app['config']->get("{$this->getSnakeName()}{$notation}", []);
    }

    public function setConfig($newConfig,$notation = null, ): mixed{

        $notation = !$notation ? '' : ".{$notation}";

        if(!$this->app['config']->has($this->getSnakeName()) && $this->app->runningInConsole() && file_exists($this->getDirectoryPath('Config/config.php'))){
            $this->app['config']->set("{$this->getSnakeName()}", include($this->getDirectoryPath('Config/config.php')));
        }

        return $this->app['config']->set("{$this->getSnakeName()}{$notation}", $newConfig);
    }

    /**
     * getParentRoute
     *
     * @return array
     */
    public function getParentRoute(): array
    {
        return array_values(array_filter($this->getRouteConfigs(), function($r){
            return isset($r['parent']) && $r['parent'];
        }))[0] ?? [];
    }

    /**
     * hasParentRoute
     *
     * @return bool
     */
    public function hasParentRoute(): bool
    {
        return count($this->getParentRoute()) > 0;
    }

    /**
     * hasSystemPrefix
     *
     * @return mixed
     */
    public function hasSystemPrefix(): mixed
    {
        return $this->getConfig('system_prefix') ?? $this->getConfig('base_prefix', false);
    }

    /**
     * systemPrefix
     *
     * @return string
     */
    public function systemPrefix(): string
    {
        return systemUrlPrefix();
    }

    /**
     * systemRouteNamePrefix
     *
     * @return string
     */
    public function systemRouteNamePrefix(): string
    {
        return systemRouteNamePrefix();
    }

    /**
     * prefix
     *
     * @return string
     */
    public function prefix(): string
    {
        $pr = $this->getParentRoute();
        $name = getValueOrNull($this->getConfig('name')) ?? $this->getName();
        return $this->hasParentRoute() && (isset($pr['url'])  || isset($pr['name']))
            ? ($pr['url'] ?? pluralize( kebabCase($pr['name'])))
            : pluralize(kebabCase($name));
    }

    /**
     * fullPrefix
     *
     * @return string
     */
    public function fullPrefix(): string
    {
        $prefixes = [];

        $adminUrlPrefix = adminUrlPrefix();

        if($adminUrlPrefix)
            $prefixes[] = $adminUrlPrefix;

        if($this->hasSystemPrefix())
            $prefixes[] = $this->systemPrefix();

        $prefixes[] = $this->prefix();

        return implode('/', $prefixes);
    }

    /**
     * routeNamePrefix
     *
     * @return string
     */
    public function routeNamePrefix(): string
    {
        return $this->hasParentRoute()
            ? ($this->getParentRoute()['route_name'] ?? $this->getSnakeName())
            : snakeCase(getValueOrNull($this->getConfig('name')) ?? $this->getName());
    }

    /**
     * fullRouteNamePrefix
     *
     * @return string
     */
    public function fullRouteNamePrefix(): string
    {
        $prefixes = [];

        $adminRouteNamePrefix = adminRouteNamePrefix();

        if($adminRouteNamePrefix)
            $prefixes[] = $adminRouteNamePrefix;

        // if($this->hasSystemPrefix())
        //     $prefixes[] = $this->systemRouteNamePrefix();

        $prefixes[] = $this->routeNameprefix();

        return implode('.', $prefixes);
    }


    public function getRepository($routeName, $asClass = true){
        return (new Finder)->getRouteRepository($routeName,$asClass);
    }


    public function isRouteTableExists($routeName = null, $notation = null){
        $tableName = $this->getRepository($routeName ?? $this->getStudlyName(), false) ? $this->getRepository($routeName ?? $this->getStudlyName())->getModel()->getTable() : $this->getRepository($notation)->getModel()->getTable();
        return Schema::hasTable($tableName);
    }

    public function getConfigPath(){
        return $this->getPath().'/Config/config.php';
    }


    /**
     * Check whether the file is presents
     *
     * @param string fileName
     *
     * @return bool
     */
    public function isFileExists($fileName){
        $pattern = $this->getDirectoryPath('**/*/*' . $fileName . '*');
        // $pattern = base_path('Modules/'.$this->module->getStudlyName().'/**/*/*'.$fileName.'*');
        $search = glob($pattern);

        return !empty($search);
    }


}

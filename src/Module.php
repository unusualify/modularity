<?php

namespace OoBook\CRM\Base;

use Illuminate\Container\Container;
use Nwidart\Modules\Module as NwidartModule;
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
     * The constructor.
     * @param Container $app
     * @param $name
     * @param $path
     */
    public function __construct(Container $app, string $name, $path)
    {
        // dd($path);
        parent::__construct($app, $name, $path);

        $this->moduleActivator = $app['unusual.activator'];
        $this->moduleActivator->setModule($name);
        // dd($this->moduleActivator, $app);
    }

    public function setModuleActivator($name)
    {
        $this->moduleActivator->setModule($name);
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
}

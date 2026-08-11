<?php

namespace Unusualify\Modularous\Traits;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\ModuleRoute;

trait ManageModuleRoute
{
    use Moduleable;

    protected ?Module $module = null;

    protected ?ModuleRoute $moduleRoute = null;

    protected ?array $routeConfig = [];

    public function isModuleRouteClass()
    {
        $moduleName = $this->getModuleName();
        $moduleRouteName = $this->getModuleRouteName();

        if (! $moduleName || ! $moduleRouteName) {
            return false;
        }

        if (! Modularous::find($moduleName)?->hasRoute($moduleRouteName)) {
            return false;
        }

        return true;
    }

    /**
     * @deprecated use Moduleable::getModuleName() instead
     *
     * @return string|null
     */
    public function moduleName()
    {
        return $this->getModuleName();
    }

    /**
     * @deprecated use Moduleable::getModuleRouteName() instead
     *
     * @return string|null
     */
    public function routeName()
    {
        return $this->getModuleRouteName();
    }

    /**
     * @return Module|null
     */
    public function getModule()
    {
        return Modularous::find($this->getModuleName());
    }

    /**
     * @return $this
     */
    public function setModule(Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return $this
     */
    public function setModuleRoute(ModuleRoute $moduleRoute): static
    {
        $this->moduleRoute = $moduleRoute;

        return $this;
    }

    /**
     * @return ModuleRoute|null
     */
    public function getModuleRoute()
    {
        return $this->ensureModuleRouteResolved();
    }

    /**
     * Resolve ModuleRoute once per controller request (not in __construct — hot-path ADR).
     */
    protected function ensureModuleRouteResolved(): ?ModuleRoute
    {
        if ($this->moduleRoute instanceof ModuleRoute) {
            return $this->moduleRoute;
        }

        if (! $this->module) {
            return null;
        }

        if ($this->routeName && ! $this->moduleRouteName) {
            $this->moduleRouteName = $this->routeName;
        }

        $name = $this->moduleRouteName ?? $this->routeName;
        if (! is_string($name) || $name === '') {
            return null;
        }

        $this->moduleRoute = $this->module->moduleRoute($name)
            ?? $this->getModule()?->moduleRoute($name);

        return $this->moduleRoute;
    }

    /**
     * Called from CoreController::preload() via Traitify naming.
     *
     * Intentionally a no-op: resolving {@see ModuleRoute} here forces ModuleRouteRegistry
     * on every panel request. Resolve lazily from getModuleRoute() / URL helpers only.
     */
    protected function preloadManageModuleRoute(): void
    {
        //
    }

    /**
     * @return array
     */
    public function getRouteConfig()
    {
        if ($this->routeConfig && ! empty($this->routeConfig)) {
            return $this->routeConfig;
        }

        // Prefer already-resolved ModuleRoute; do not force registry build for config reads.
        if ($this->moduleRoute instanceof ModuleRoute) {
            $this->routeConfig = $this->moduleRoute->rawConfig();

            return $this->routeConfig;
        }

        $module = $this->getModule();

        if ($module) {
            $this->routeConfig = $module->getRawRouteConfig($this->getRouteName());
        }

        return $this->routeConfig;
    }

    public function getRouteTitleColumnKey(): string
    {
        return ! empty($conf = $this->getRouteConfig()) ? ($conf['title_column_key'] ?? 'name') : 'name';
    }

    public function getRouteInputs(): array
    {
        if ($this->moduleRoute instanceof ModuleRoute) {
            $inputs = $this->moduleRoute->rawConfig('inputs', []);

            return is_array($inputs) ? $inputs : [];
        }

        return ! empty($conf = $this->getRouteConfig()) ? ($conf['inputs'] ?? []) : [];
    }

    public function getRouteHeaders(): array
    {
        if ($this->moduleRoute instanceof ModuleRoute) {
            $headers = $this->moduleRoute->rawConfig('headers', []);

            return is_array($headers) ? $headers : [];
        }

        return ! empty($conf = $this->getRouteConfig()) ? ($conf['headers'] ?? []) : [];
    }

    public function getRouteTableOptions(): array
    {
        if ($this->moduleRoute instanceof ModuleRoute) {
            $options = $this->moduleRoute->rawConfig('table_options', []);

            return is_array($options) ? $options : [];
        }

        return ! empty($conf = $this->getRouteConfig()) ? ($conf['table_options'] ?? []) : [];
    }

    /**
     * Build the Laravel route-name prefix for panel URLs.
     *
     * Uses {@see ModuleRoute} only when already resolved — never forces registry build.
     */
    protected function generateRoutePrefix($noNested = false): string
    {
        if ($this->moduleRoute instanceof ModuleRoute) {
            return $this->moduleRoute->generateRoutePrefix(
                noNested: (bool) $noNested,
                isNested: (bool) ($this->isNested ?? false),
                nestedParentName: isset($this->nestedParentName) ? (string) $this->nestedParentName : null,
                isParent: isset($this->isParent) ? (bool) $this->isParent : null,
            );
        }

        $routePrefixes = [];

        $adminRoutePrefix = adminRouteNamePrefix();

        if ($adminRoutePrefix) {
            $routePrefixes[] = $adminRoutePrefix;
        }

        if (isset($this->config->system_prefix)) {
            if ($this->config->system_prefix) {
                $routePrefixes[] = systemRouteNamePrefix();
            }
        } elseif (isset($this->config->base_prefix) && $this->config->base_prefix) {
            $routePrefixes[] = systemRouteNamePrefix();
        }

        $isParent = (bool) ($this->isParent ?? false);
        $isNested = (bool) ($this->isNested ?? false);

        if (! $isParent || ($isNested && ! $noNested)) {
            $routePrefixes[] = snakeCase((string) ($this->moduleName ?? ''));
        }

        if ($isNested && ! $noNested && filled($this->nestedParentName ?? null)) {
            $routePrefixes[] = (string) $this->nestedParentName;
            $routePrefixes[] = 'nested';
        }

        return implode('.', $routePrefixes);
    }
}

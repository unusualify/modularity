<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

use Illuminate\Contracts\Auth\Authenticatable;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\ModuleRoute;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteInspectSource;

/**
 * Inspects module routes for enable/disable status and feature trait wiring.
 *
 * Findings are produced here; route data comes from {@see ModuleRoute}.
 */
final class ModuleRouteInspector implements ModuleRouteInspectSource
{
    public function __construct(
        private readonly FeatureDetector $featureDetector,
    ) {
    }

    public function inspect(?string $moduleName = null, ?string $routeName = null): ModuleRouteInspectReport
    {
        $modules = $this->resolveModules($moduleName);
        $entries = [];

        foreach ($modules as $module) {
            $moduleRoutes = $this->resolveModuleRoutes($module, $routeName);

            foreach ($moduleRoutes as $moduleRoute) {
                $entries[] = $this->inspectModuleRoute($moduleRoute);
            }
        }

        return new ModuleRouteInspectReport($entries);
    }

    public function userCanAccess(Authenticatable $user): bool
    {
        if (! modularousConfig('module_route_inspect.enabled', false)) {
            return false;
        }

        /** @var list<string> $roles */
        $roles = array_values(array_filter(
            (array) modularousConfig('module_route_inspect.allowed_roles', ['superadmin'])
        ));

        if ($roles === []) {
            return false;
        }

        if ($this->isSuperadmin($user) && in_array('superadmin', $roles, true)) {
            return true;
        }

        if (is_callable([$user, 'hasAnyRole'])) {
            return (bool) $user->hasAnyRole($roles);
        }

        if (is_callable([$user, 'hasRole'])) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isSuperadmin(Authenticatable $user): bool
    {
        if (isset($user->is_superadmin) && $user->is_superadmin) {
            return true;
        }

        if (is_callable([$user, 'hasRole'])) {
            return (bool) $user->hasRole('superadmin');
        }

        return false;
    }

    public function canToggleStatus(): bool
    {
        return (bool) modularousConfig('module_route_inspect.allow_status_toggle', true);
    }

    public function canHeal(): bool
    {
        return (bool) modularousConfig('module_route_inspect.allow_heal', false);
    }

    /**
     * @return array{inspect: string, setStatus: string, heal: string}
     */
    public function panelEndpoints(): array
    {
        $prefix = Modularous::getAdminRouteNamePrefix() . '.';

        return [
            'inspect' => route($prefix . 'module-route-inspect.inspect'),
            'setStatus' => route($prefix . 'module-route-inspect.status'),
            'heal' => route($prefix . 'module-route-inspect.heal'),
        ];
    }

    /**
     * @return array{inspect: string, setStatus: string, heal: string}
     */
    public function emptyPanelEndpoints(): array
    {
        return [
            'inspect' => '',
            'setStatus' => '',
            'heal' => '',
        ];
    }

    public function setRouteEnabled(string $moduleName, string $route, bool $enabled): void
    {
        if (! $this->canToggleStatus()) {
            throw new \RuntimeException('Module route status toggle is disabled.');
        }

        $module = Modularous::findOrFail($moduleName);

        if (! $module instanceof Module) {
            throw new \InvalidArgumentException("Module [{$moduleName}] is not a Modularous module.");
        }

        $moduleRoute = $module->route($route);
        if ($moduleRoute === null) {
            // Allow enabling a route that exists only after explicit status write
            // (e.g. brand-new Studly name): fall back to Module enable/disable.
            if ($enabled) {
                $module->enableRoute($route);
            } else {
                $module->disableRoute($route);
            }

            return;
        }

        if ($enabled) {
            $moduleRoute->enable();
        } else {
            $moduleRoute->disable();
        }
    }

    /**
     * @return list<string>
     */
    public function featureKeys(): array
    {
        return array_keys($this->featureDetector->definitions());
    }

    /**
     * @return list<string>
     */
    public function highlightFeatureKeys(): array
    {
        return array_values(array_filter(
            (array) modularousConfig('module_route_inspect.table_feature_keys', [])
        ));
    }

    private function inspectModuleRoute(ModuleRoute $route): ModuleRouteInspectEntry
    {
        $moduleName = $route->module()->getStudlyName();
        $routeName = $route->name();
        $inStatuses = $route->inStatuses();
        $inConfig = $route->inConfig();
        $enabled = $route->isEnabled();

        $findings = [];

        if (! $inConfig && $inStatuses) {
            $findings[] = new ModuleRouteInspectFinding(
                'warning',
                'orphan_status',
                "Route [{$routeName}] exists in routes_statuses.json but not in module config routes."
            );
        }

        if ($inConfig && ! $inStatuses) {
            $findings[] = new ModuleRouteInspectFinding(
                'info',
                'missing_status',
                "Route [{$routeName}] exists in module config but is missing from routes_statuses.json (treated as disabled)."
            );
        }

        $modelClass = $route->modelClass();
        $repositoryClass = $route->repositoryClass();

        if ($modelClass === null) {
            $findings[] = new ModuleRouteInspectFinding(
                'error',
                'missing_model',
                "Model class not found for route [{$routeName}]."
            );
        }

        if ($repositoryClass === null) {
            $findings[] = new ModuleRouteInspectFinding(
                'error',
                'missing_repository',
                "Repository class not found for route [{$routeName}]."
            );
        }

        $detected = $this->featureDetector->detect($modelClass, $repositoryClass);
        $features = $detected['features'];
        $findings = array_merge($findings, $detected['findings']);
        $findings = array_merge(
            $findings,
            $this->featureDetector->detectCmrFrontController($route->module(), $routeName, $features)
        );

        return new ModuleRouteInspectEntry(
            module: $moduleName,
            route: $routeName,
            enabled: $enabled,
            parent: $route->isParent(),
            model: $modelClass,
            repository: $repositoryClass,
            features: $features,
            findings: $findings,
            inConfig: $inConfig,
            inStatuses: $inStatuses,
        );
    }

    /**
     * @return list<Module>
     */
    private function resolveModules(?string $moduleName): array
    {
        if ($moduleName !== null && $moduleName !== '') {
            $module = Modularous::findOrFail($moduleName);

            if (! $module instanceof Module) {
                throw new \InvalidArgumentException("Module [{$moduleName}] is not a Modularous module.");
            }

            return [$module];
        }

        $modules = [];
        foreach (Modularous::allEnabled() as $module) {
            if ($module instanceof Module) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * @return list<ModuleRoute>
     */
    private function resolveModuleRoutes(Module $module, ?string $routeName): array
    {
        $registry = $module->routeRegistry();

        if ($routeName === null || $routeName === '') {
            return $registry->all()->values()->all();
        }

        return [$registry->get($routeName)];
    }
}

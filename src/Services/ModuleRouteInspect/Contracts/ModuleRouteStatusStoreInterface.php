<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect\Contracts;

/**
 * Persistence adapter for per-module route enable/disable statuses.
 *
 * Drivers (config `modularous.module_route_inspect.driver`):
 * - filesystem (default): `{module}/routes_statuses.json` via ModuleActivator
 * - database: `um_module_route_statuses` for env-specific toggles outside git
 */
interface ModuleRouteStatusStoreInterface
{
    /**
     * @return array<string, bool> Studly route name => enabled
     */
    public function getStatuses(string $moduleName): array;

    public function isEnabled(string $moduleName, string $route): bool;

    public function setEnabled(string $moduleName, string $route, bool $enabled): void;

    /**
     * Ensure the backing store for the module exists (empty statuses if new).
     */
    public function ensureExists(string $moduleName): void;
}

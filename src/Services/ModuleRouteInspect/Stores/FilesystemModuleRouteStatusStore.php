<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect\Stores;

use Unusualify\Modularous\Activators\ModuleActivator;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;

/**
 * Filesystem adapter wrapping {@see ModuleActivator}.
 *
 * Writes `{module}/routes_statuses.json` via the activator directly (not
 * {@see Module::enableRoute}) to avoid recursion when Module delegates to this store.
 */
final class FilesystemModuleRouteStatusStore implements ModuleRouteStatusStoreInterface
{
    public function getStatuses(string $moduleName): array
    {
        $module = $this->resolveModule($moduleName);

        /** @var array<string, bool> $statuses */
        $statuses = $module->getActivator()->readJson();

        return $statuses;
    }

    public function isEnabled(string $moduleName, string $route): bool
    {
        $route = studlyName($route);

        return $this->resolveModule($moduleName)->getActivator()->hasStatus($route, true);
    }

    public function setEnabled(string $moduleName, string $route, bool $enabled): void
    {
        $activator = $this->resolveModule($moduleName)->getActivator();
        $route = studlyName($route);

        if ($enabled) {
            $activator->enable($route);
        } else {
            $activator->disable($route);
        }
    }

    public function ensureExists(string $moduleName): void
    {
        $this->resolveModule($moduleName)->getActivator()->ensureFileExists();
    }

    private function resolveModule(string $moduleName): Module
    {
        $module = Modularous::findOrFail($moduleName);

        if (! $module instanceof Module) {
            throw new \InvalidArgumentException("Module [{$moduleName}] is not a Modularous module.");
        }

        return $module;
    }
}

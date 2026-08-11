<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect\Stores;

use Unusualify\Modularous\Entities\ModuleRouteStatus;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;

/**
 * Database adapter for per-module route enable/disable statuses.
 *
 * Source of truth: {@see ModuleRouteStatus} / `um_module_route_statuses`.
 * Use when runtime toggles must not mutate git-tracked `routes_statuses.json`.
 */
final class DatabaseModuleRouteStatusStore implements ModuleRouteStatusStoreInterface
{
    public function getStatuses(string $moduleName): array
    {
        $module = $this->normalizeModule($moduleName);

        /** @var array<string, bool> $statuses */
        $statuses = ModuleRouteStatus::query()
            ->where('module', $module)
            ->get()
            ->mapWithKeys(static fn (ModuleRouteStatus $row): array => [
                $row->route => (bool) $row->enabled,
            ])
            ->all();

        return $statuses;
    }

    public function isEnabled(string $moduleName, string $route): bool
    {
        $row = ModuleRouteStatus::query()
            ->where('module', $this->normalizeModule($moduleName))
            ->where('route', $this->normalizeRoute($route))
            ->first();

        return $row !== null && (bool) $row->enabled;
    }

    public function setEnabled(string $moduleName, string $route, bool $enabled): void
    {
        ModuleRouteStatus::query()->updateOrCreate(
            [
                'module' => $this->normalizeModule($moduleName),
                'route' => $this->normalizeRoute($route),
            ],
            [
                'enabled' => $enabled,
            ]
        );
    }

    public function ensureExists(string $moduleName): void
    {
        // Table is created by migration; rows are created on enable/disable.
    }

    private function normalizeModule(string $moduleName): string
    {
        return studlyName($moduleName);
    }

    private function normalizeRoute(string $route): string
    {
        return studlyName($route);
    }
}

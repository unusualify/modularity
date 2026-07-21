<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Repositories\Logic;

use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Traits\Moduleable;

trait ResourceCacheActionsTrait
{
    use Moduleable;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getResourceCacheActionSchema(): array
    {
        if (! $this->resourceCacheActionsEnabled()) {
            return [];
        }

        $manualTypes = ModularousCache::resolveManualCacheTypes(
            $this->getModuleName(),
            $this->getRouteName(),
        );

        if (count(array_filter($manualTypes)) === 0) {
            return [];
        }

        $defaultTypes = array_keys(array_filter($manualTypes));

        return [
            [
                'name' => 'cachePurge',
                'label' => __('messages.resource-cache.purge-record.label'),
                'icon' => 'mdi-delete-sweep',
                'color' => 'warning',
                'params' => ['types' => $defaultTypes],
            ],
            [
                'name' => 'cacheWarm',
                'label' => __('messages.resource-cache.warm-record.label'),
                'icon' => 'mdi-refresh',
                'color' => 'primary',
                'params' => ['types' => $defaultTypes],
                'reloadOnSuccess' => true,
            ],
            [
                'name' => 'cachePurgeAll',
                'label' => __('messages.resource-cache.purge-all.label'),
                'icon' => 'mdi-delete-sweep-outline',
                'color' => 'warning',
                'scope' => 'table',
                'params' => ['types' => $defaultTypes],
                'confirmationModalAttributes' => [
                    'widthType' => 'md',
                    'title' => __('messages.resource-cache.purge-all.confirmation-title'),
                    'description' => __('messages.resource-cache.purge-all.confirmation-description'),
                    'confirmText' => __('messages.resource-cache.purge-all.confirmation-confirmText'),
                    'cancelText' => __('messages.resource-cache.purge-all.confirmation-cancelText'),
                    'titleJustify' => 'center',
                ],
            ],
            [
                'name' => 'cacheWarmAll',
                'label' => __('messages.resource-cache.warm-all.label'),
                'icon' => 'mdi-refresh-circle',
                'color' => 'primary',
                'scope' => 'table',
                'params' => ['types' => $defaultTypes],
                'confirmationModalAttributes' => [
                    'widthType' => 'md',
                    'title' => __('messages.resource-cache.warm-all.confirmation-title'),
                    'description' => __('messages.resource-cache.warm-all.confirmation-description'),
                    'confirmText' => __('messages.resource-cache.warm-all.confirmation-confirmText'),
                    'cancelText' => __('messages.resource-cache.warm-all.confirmation-cancelText'),
                    'titleJustify' => 'center',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getResourceCacheTableActionSchema(): array
    {
        return array_values(array_filter(
            $this->getResourceCacheActionSchema(),
            fn (array $def) => ($def['scope'] ?? null) === 'table',
        ));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getFormActionsResourceCacheActionsTrait(User $user, array $scope = []): array
    {
        if (! $this->resourceCacheActionsEnabled()) {
            return [];
        }

        $permissionName = $this->getPermissionName(Permission::CACHING->value, $this->getRouteName());
        if (! $user->can($permissionName)) {
            return [];
        }

        $module = $this->getModule();
        $isParent = $module->isParentRoute($this->getRouteName());

        $routePrefix = $this->getModule()->panelRouteNamePrefix($isParent) . snakeCase($this->getRouteName()) . '.';

        if ($routePrefix === null) {
            return [];
        }

        $actions = [];

        foreach ($this->getResourceCacheActionSchema() as $def) {
            if (($def['scope'] ?? null) === 'table') {
                continue;
            }

            $action = $this->mapResourceCacheFormAction($def, $routePrefix);
            if ($action === null) {
                continue;
            }

            $key = is_string($def['name'] ?? null) && $def['name'] !== ''
                ? $def['name']
                : 'resourceCache';

            $actions[$key] = $action;
        }

        return $actions;
    }

    protected function resourceCacheActionsEnabled(): bool
    {
        $moduleName = $this->getModuleName();
        $routeName = $this->getRouteName();

        return $moduleName !== null
            && $routeName !== null
            && ModularousCache::hasAdminCacheActions($moduleName, $routeName);
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>|null
     */
    protected function mapResourceCacheFormAction(array $def, string $routePrefix): ?array
    {
        $name = $def['name'] ?? null;
        if (! is_string($name) || $name === '') {
            return null;
        }

        $action = [
            'name' => $name,
            'label' => $def['label'] ?? $name,
            'icon' => $def['icon'] ?? 'mdi-cached',
            'color' => $def['color'] ?? 'primary',
            'variant' => 'tonal',
            'type' => 'request',
            'method' => 'post',
            'endpoint' => $routePrefix . $name,
            'params' => $def['params'] ?? [],
            'creatable' => false,
            'editable' => true,
            'hasConfirmation' => array_key_exists('confirmationModalAttributes', $def),
        ];

        foreach ([
            'forceLabel', 'density', 'variant', 'color', 'textColor', 'allowedRoles', 'noSuperAdmin',
            'tooltip', 'tooltipLocation', 'componentProps', 'responsive', 'badge', 'badgeColor',
            'hasConfirmation', 'confirmationModalAttributes', 'method', 'params', 'reloadOnSuccess',
            'reloadOnly', 'reloadDelay', 'forceRefresh', 'conditions', 'hideOnCondition', 'creatable', 'editable',
        ] as $optionalKey) {
            if (array_key_exists($optionalKey, $def)) {
                $action[$optionalKey] = $def[$optionalKey];
            }
        }

        return $action;
    }
}

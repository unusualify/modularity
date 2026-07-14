<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Controllers\Traits\Form\FormActions;
use Unusualify\Modularous\Http\Controllers\Traits\Table\TableActions;
use Unusualify\Modularous\Jobs\Cache\WarmModuleRouteCachesJob;
use Unusualify\Modularous\Repositories\Logic\ResourceCacheActionsTrait;

/**
 * Superadmin cache purge/warm endpoints and admin UI actions.
 *
 * {@see TableActions::setTableActions()} invokes {@see setTableActionsManageResourceCache()}.
 * {@see FormActions::preloadFormActions()} merges repository form actions from
 * {@see ResourceCacheActionsTrait}.
 */
trait ManageResourceCache
{
    protected function setTableActionsManageResourceCache(): void
    {
        if (! $this->repositoryUsesResourceCacheActions()) {
            return;
        }

        if (! ModularousCache::hasAdminCacheActions($this->getModuleName(), $this->getRouteName())) {
            return;
        }

        $schema = $this->repository->getResourceCacheTableActionSchema();
        if ($schema === []) {
            return;
        }

        $module = $this->getModule();
        $isParent = $module->isParentRoute($this->getRouteName());

        $routePrefix = $module->panelRouteNamePrefix($isParent) . snakeCase($this->getRouteName()) . '.';
        $existing = is_array($this->tableActions ?? null) ? $this->tableActions : [];
        $actions = [];

        foreach ($schema as $def) {
            if (! is_array($def) || ($def['scope'] ?? null) !== 'table') {
                continue;
            }

            $action = $this->mapResourceCacheTableAction($def, $routePrefix);
            if ($action !== null) {
                $actions[] = $action;
            }
        }

        if ($actions === []) {
            return;
        }

        $this->tableActions = array_values(array_merge($existing, $actions));
    }

    /**
     * Check if the repository uses the ResourceCacheActionsTrait.
     */
    protected function repositoryUsesResourceCacheActions(): bool
    {
        if (! $this->repository) {
            return false;
        }

        return in_array(
            ResourceCacheActionsTrait::class,
            class_uses_recursive($this->repository),
            true
        );
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>|null
     */
    protected function mapResourceCacheTableAction(array $def, string $routePrefix): ?array
    {
        $name = $def['name'] ?? null;
        if (! is_string($name) || $name === '') {
            return null;
        }

        $action = [
            'name' => $name,
            'label' => $def['label'] ?? $name,
            'icon' => $def['icon'] ?? 'mdi-cached',
            'color' => $def['color'] ?? 'warning',
            'variant' => 'tonal',
            'type' => 'request',
            'method' => 'post',
            'endpoint' => $routePrefix . $name,
            'params' => $def['params'] ?? [],
            'hasConfirmation' => true,
            'noSuperAdmin' => false,
        ];

        foreach ([
            'forceLabel', 'density', 'variant', 'color', 'textColor', 'allowedRoles', 'noSuperAdmin',
            'tooltip', 'tooltipLocation', 'componentProps', 'responsive', 'badge', 'badgeColor',
            'hasConfirmation', 'confirmationModalAttributes', 'method', 'params', 'reloadOnSuccess',
        ] as $optionalKey) {
            if (array_key_exists($optionalKey, $def)) {
                $action[$optionalKey] = $def[$optionalKey];
            }
        }

        return $action;
    }

    public function cachePurge(Request $request, int $id): JsonResponse
    {
        $this->authorizeResourceCacheAction();

        $model = $this->repository->getById($id);
        $types = $this->resolveResourceCacheTypes($request);

        ModularousCache::purgeModelCacheTypes(
            $model,
            $types,
            $this->getModuleName(),
            $this->getRouteName(),
        );

        return response()->json([
            'message' => __('messages.resource-cache.purge-record.success'),
            'queued' => false,
            'types' => array_keys(array_filter($types)),
        ]);
    }

    public function cacheWarm(Request $request, int $id): JsonResponse
    {
        $this->authorizeResourceCacheAction();

        $model = $this->repository->getById($id);
        $types = $this->resolveResourceCacheTypes($request);

        ModularousCache::refreshModelCaches($model, $types, [
            'moduleName' => $this->getModuleName(),
            'moduleRouteName' => $this->getRouteName(),
        ]);

        return response()->json([
            'message' => __('messages.resource-cache.warm-record.success'),
            'queued' => false,
            'types' => array_keys(array_filter($types)),
        ]);
    }

    public function cachePurgeAll(Request $request): JsonResponse
    {
        $this->authorizeResourceCacheAction();

        ModularousCache::invalidateModuleRoute($this->getModuleName(), $this->getRouteName());

        return response()->json([
            'message' => __('messages.resource-cache.purge-all.success'),
            'queued' => false,
        ]);
    }

    public function cacheWarmAll(Request $request): JsonResponse
    {
        $this->authorizeResourceCacheAction();

        $types = $this->resolveResourceCacheTypes($request);
        $job = WarmModuleRouteCachesJob::dispatch(
            $this->getModuleName(),
            $this->getRouteName(),
            $types,
        );

        return response()->json([
            'message' => __('messages.resource-cache.warm-all.success'),
            'queued' => true,
            'types' => array_keys(array_filter($types)),
            'job_id' => method_exists($job, 'getJobId') ? $job->getJobId() : null,
        ]);
    }

    protected function authorizeResourceCacheAction(): void
    {
        if (! ModularousCache::hasAdminCacheActions($this->getModuleName(), $this->getRouteName())) {
            abort(404);
        }

        if (! $this->user || ! $this->user->is_superadmin) {
            abort(403);
        }
    }

    /**
     * @return array<string, bool>
     */
    protected function resolveResourceCacheTypes(Request $request): array
    {
        $typesInput = $request->input('types', 'all');

        if ($typesInput === null || $typesInput === []) {
            throw ValidationException::withMessages([
                'types' => __('messages.resource-cache.validation.types-required'),
            ]);
        }

        return ModularousCache::normalizeCacheTypesInput(
            is_string($typesInput) ? $typesInput : (array) $typesInput,
            $this->getModuleName(),
            $this->getRouteName(),
        );
    }
}

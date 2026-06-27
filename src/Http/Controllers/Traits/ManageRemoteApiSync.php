<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;

/**
 * Remote API sync controller endpoints and index table toolbar actions.
 *
 * {@see \Unusualify\Modularous\Http\Controllers\Traits\Table\TableActions::setTableActions()} invokes
 * {@see setTableActionsManageRemoteApiSync()} when this trait is used.
 */
trait ManageRemoteApiSync
{
    protected function setTableActionsManageRemoteApiSync(): void
    {
        if (! $this->module || ! $this->repositoryUsesRemoteApiSource()) {
            return;
        }

        if (! $this->remoteApiConnectorIsEnabled()) {
            return;
        }

        $schema = $this->repository->getRemoteApiActionSchema();
        if ($schema === []) {
            return;
        }

        $routePrefix = $this->module->panelRouteNamePrefix() . '.' . Str::snake($this->routeName) . '.';
        $existing = is_array($this->tableActions ?? null) ? $this->tableActions : [];
        $actions = [];

        foreach ($schema as $def) {
            if (! is_array($def) || ($def['scope'] ?? null) !== 'table') {
                continue;
            }

            $action = $this->mapRemoteApiTableAction($def, $routePrefix);
            if ($action !== null) {
                $actions[] = $action;
            }
        }

        if ($actions === []) {
            return;
        }

        $this->tableActions = array_values(array_merge($existing, $actions));
    }

    protected function repositoryUsesRemoteApiSource(): bool
    {
        return in_array(
            RemoteApiSourceTrait::class,
            class_uses_recursive($this->repository),
            true
        );
    }

    protected function remoteApiConnectorIsEnabled(): bool
    {
        try {
            return $this->repository->remoteApiConnector()->configuration()->isEnabled();
        } catch (RemoteApiConfigurationException) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $def
     *
     * @return array<string, mixed>|null
     */
    protected function mapRemoteApiTableAction(array $def, string $routePrefix): ?array
    {
        $name = $def['name'] ?? null;
        if (! is_string($name) || $name === '') {
            return null;
        }

        $action = [
            'name' => $name,
            'label' => $def['label'] ?? $name,
            'icon' => $def['icon'] ?? 'mdi-sync',
            'color' => $def['color'] ?? 'primary',
            'variant' => 'tonal',
            'type' => 'request',
            'method' => 'post',
            'endpoint' => $routePrefix . $name,
            'params' => [],
            'hasConfirmation' => true,
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

        if (isset($def['table_action_extras']) && is_array($def['table_action_extras'])) {
            $action = array_merge($action, $def['table_action_extras']);
        }

        return $action;
    }

    public function syncRemote(Request $request, int $id): JsonResponse
    {
        $remoteId = $request->input('remote_id');

        try {
            $result = $this->repository->syncFromRemote($id, $remoteId);
        } catch (RemoteApiSyncException $exception) {
            throw $this->remoteApiSyncValidationException($exception);
        }

        return response()->json([
            'message' => $result['created'] ? 'Remote record synced (created).' : 'Remote record synced (updated).',
            'data' => $result['model'],
        ]);
    }

    public function syncRemoteAll(): JsonResponse
    {
        try {
            $result = $this->repository->syncAllFromRemote();
        } catch (RemoteApiSyncException $exception) {
            throw $this->remoteApiSyncValidationException($exception);
        }

        $skipped = (int) ($result['skipped'] ?? 0);
        $message = sprintf(
            'Synced %d remote records (%d created, %d updated).',
            $result['total'],
            $result['created'],
            $result['updated'],
        );

        if ($skipped > 0) {
            $message .= sprintf(' Skipped %d stale linked record(s).', $skipped);
        }

        return response()->json([
            'message' => $message,
            'data' => $result,
            'meta' => [
                'http_requests' => $result['http_requests'] ?? ['total' => 0, 'by_url' => []],
                'skipped_records' => $result['skipped_records'] ?? [],
            ],
        ]);
    }

    public function clearRemoteCache(): JsonResponse
    {
        $this->repository->clearRemoteApiCache();

        return response()->json([
            'message' => 'Remote API cache cleared.',
        ]);
    }

    public function previewRemote(Request $request, int $id): JsonResponse
    {
        $record = $this->repository->getById($id);
        $remoteId = $request->input(
            'remote_id',
            method_exists($record, 'getRemoteApiId') ? $record->getRemoteApiId() : null
        );

        $preview = $this->repository->previewRemote($remoteId);
        $connector = $this->repository->remoteApiConnector();

        $response = [
            'data' => $preview,
        ];

        if ($connector instanceof AbstractRemoteApiConnector && is_array($preview)) {
            $response['display_mode'] = $connector->previewResponseDisplay();
            $response['display'] = $connector->buildPreviewResponseDisplay($preview);
        }

        return response()->json($response);
    }

    public function listRemoteCatalog(Request $request): JsonResponse
    {
        $catalogKey = $request->query('catalog');
        $items = $this->repository->listRemoteCatalog(is_string($catalogKey) ? $catalogKey : null);

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => count($items),
            ],
        ]);
    }

    protected function remoteApiSyncValidationException(RemoteApiSyncException $exception): ValidationException
    {
        return ValidationException::withMessages([
            'remote_api' => [$exception->getMessage()],
        ]);
    }
}

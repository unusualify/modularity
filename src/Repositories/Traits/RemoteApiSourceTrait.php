<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConnectorFactory;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiSynchronizer;
use Unusualify\Modularous\Traits\Moduleable;

trait RemoteApiSourceTrait
{
    use Moduleable;

    public function remoteApiConnector(): RemoteApiConnectorInterface
    {
        return app(RemoteApiConnectorFactory::class)->make(
            $this->resolveRemoteApiModuleName(),
            $this->resolveRemoteApiRouteName(),
        );
    }

    /**
     * @return array{model: Model, created: bool}
     */
    public function syncFromRemote(?int $localId = null, int|string|null $remoteId = null): array
    {
        $connector = $this->remoteApiConnector();
        $remoteIdColumn = $connector->configuration()->remoteIdColumn();

        if ($remoteId === null && $localId !== null) {
            $existing = $this->getById($localId);
            $remoteId = method_exists($existing, 'getRemoteApiId')
                ? $existing->getRemoteApiId()
                : ($existing->{$remoteIdColumn} ?? null);
        }

        if ($remoteId === null) {
            throw RemoteApiSyncException::missingRemoteId();
        }

        return app(RemoteApiSynchronizer::class)->syncRecord($connector, $this, $remoteId);
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     skipped_records: list<array{remote_id: int|string, reason: string, message: string}>,
     *     http_requests: array{total: int, by_url: array<string, int>}
     * }
     */
    public function syncAllFromRemote(): array
    {
        return app(RemoteApiSynchronizer::class)->syncAll(
            $this->remoteApiConnector(),
            $this,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function previewSyncFromRemote(?int $localId = null, int|string|null $remoteId = null): array
    {
        $connector = $this->remoteApiConnector();
        $remoteIdColumn = $connector->configuration()->remoteIdColumn();

        if ($remoteId === null && $localId !== null) {
            $existing = $this->getById($localId);
            $remoteId = method_exists($existing, 'getRemoteApiId')
                ? $existing->getRemoteApiId()
                : ($existing->{$remoteIdColumn} ?? null);
        }

        if ($remoteId === null) {
            throw RemoteApiSyncException::missingRemoteId();
        }

        return app(RemoteApiSynchronizer::class)->previewSyncRecord($connector, $this, $remoteId);
    }

    /**
     * @return array<string, mixed>
     */
    public function previewSyncAllFromRemote(): array
    {
        return app(RemoteApiSynchronizer::class)->previewSyncAll(
            $this->remoteApiConnector(),
            $this,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function previewRemoteCatalog(?string $catalogKey = null): array
    {
        $configuration = $this->remoteApiConnector()->configuration();
        $endpoint = $catalogKey !== null
            ? $configuration->catalogEndpoint($catalogKey)
            : $configuration->endpoint();

        return [
            'configuration' => $configuration->toSummaryArray(),
            'catalog_key' => $catalogKey,
            'endpoint' => $endpoint,
            'would_fetch' => sprintf('GET %s/%s', $configuration->baseUrl(), $endpoint),
            'available_catalogs' => $configuration->catalogKeys(),
        ];
    }

    public function clearRemoteApiCache(int|string|null $remoteId = null): void
    {
        $this->remoteApiConnector()->clearCache($remoteId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listRemoteCatalog(?string $catalogKey = null): array
    {
        return $this->remoteApiConnector()->listCatalog($catalogKey);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function previewRemote(int|string $remoteId): ?array
    {
        return $this->remoteApiConnector()->fetchOne($remoteId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRemoteApiActionSchema(): array
    {
        try {
            $actions = $this->remoteApiConnector()->configuration()->actions();
        } catch (RemoteApiConfigurationException) {
            return [];
        }

        $schema = [];

        foreach ($actions as $action) {
            $schema[] = match ($action) {
                'sync_record' => [
                    'name' => 'syncRemote',
                    'label' => __('messages.remote-api.sync-record.label'),
                    'icon' => 'mdi-sync',
                    'color' => 'primary',
                ],
                'sync_all' => [
                    'name' => 'syncRemoteAll',
                    'label' => __('messages.remote-api.sync-all.label'),
                    'icon' => 'mdi-sync',
                    'color' => 'primary',
                    'scope' => 'table',
                    'confirmationModalAttributes' => [
                        'widthType' => 'md',
                        'title' => __('messages.remote-api.sync-all.confirmation-title'),
                        'description' => __('messages.remote-api.sync-all.confirmation-description'),
                        'confirmText' => __('messages.remote-api.sync-all.confirmation-confirmText'),
                        'cancelText' => __('messages.remote-api.sync-all.confirmation-cancelText'),
                        'titleJustify' => 'center',
                    ],
                ],
                'clear_cache' => [
                    'name' => 'clearRemoteCache',
                    'label' => __('messages.remote-api.clear-cache.label'),
                    'icon' => 'mdi-cached',
                    'color' => 'warning',
                    'scope' => 'table',
                    'confirmationModalAttributes' => [
                        'widthType' => 'md',
                        'title' => __('messages.remote-api.clear-cache.confirmation-title'),
                        'description' => __('messages.remote-api.clear-cache.confirmation-description'),
                        'confirmText' => __('messages.remote-api.clear-cache.confirmation-confirmText'),
                        'cancelText' => __('messages.remote-api.clear-cache.confirmation-cancelText'),
                        'titleJustify' => 'center',
                    ],
                ],
                'preview' => [
                    'name' => 'previewRemote',
                    'label' => __('messages.remote-api.preview.label'),
                    'icon' => 'mdi-magnify-scan',
                    'color' => 'info',
                ],
                default => null,
            };
        }

        return array_values(array_filter($schema));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getFormActionsRemoteApiSourceTrait(User $user, array $scope = []): array
    {
        if (! $this->remoteApiConnectorIsEnabled()) {
            return [];
        }

        $schema = $this->getRemoteApiActionSchema();
        if ($schema === []) {
            return [];
        }

        $routePrefix = $this->resolveRemoteApiFormActionRoutePrefix();
        if ($routePrefix === null) {
            return [];
        }

        $actions = [];

        foreach ($schema as $def) {
            if (! is_array($def) || ($def['scope'] ?? null) === 'table') {
                continue;
            }

            $action = $this->mapRemoteApiFormAction($def, $routePrefix);
            if ($action === null) {
                continue;
            }

            $key = is_string($def['name'] ?? null) && $def['name'] !== ''
                ? $def['name']
                : 'remoteApi';

            $actions[$key] = $action;
        }

        return $actions;
    }

    protected function remoteApiConnectorIsEnabled(): bool
    {
        try {
            return $this->remoteApiConnector()->configuration()->isEnabled();
        } catch (RemoteApiConfigurationException) {
            return false;
        }
    }

    protected function resolveRemoteApiFormActionRoutePrefix(): ?string
    {
        try {
            $module = Modularous::find($this->resolveRemoteApiModuleName());
        } catch (\Throwable) {
            return null;
        }

        if ($module === null) {
            return null;
        }

        return $module->panelRouteNamePrefix() . '.' . $this->resolveRemoteApiRouteName() . '.';
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>|null
     */
    protected function mapRemoteApiFormAction(array $def, string $routePrefix): ?array
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
            'method' => 'put',
            'endpoint' => $routePrefix . $name,
            'params' => [],
            'creatable' => false,
            'editable' => true,
            'hideOnCondition' => true,
            'conditions' => [
                ['remote_id', 'exists'],
            ],
        ];

        if ($name === 'syncRemote') {
            $action['reloadOnSuccess'] = true;
            $action['hasConfirmation'] = array_key_exists('confirmationModalAttributes', $def);
        }

        if ($name === 'previewRemote') {
            $previewConfig = $this->resolvePreviewResponseConfig();

            $action['responseDisplay'] = $def['responseDisplay']
                ?? $previewConfig['responseDisplay'];
            $action['responseFields'] = $def['responseFields']
                ?? $previewConfig['responseFields'];
            $action['responseModalAttributes'] = [
                'widthType' => 'sm',
                'title' => $def['label'] ?? __('messages.remote-api.preview.label'),
                'titleJustify' => 'center',
                'noConfirmButton' => true,
                'cancelText' => __('messages.remote-api.preview.close'),
            ];
        }

        foreach ([
            'forceLabel', 'density', 'variant', 'color', 'textColor', 'allowedRoles', 'noSuperAdmin',
            'tooltip', 'tooltipLocation', 'componentProps', 'responsive', 'badge', 'badgeColor',
            'hasConfirmation', 'confirmationModalAttributes', 'responseModalAttributes', 'responseDisplay',
            'responseFields', 'method', 'params', 'reloadOnSuccess', 'reloadOnly', 'reloadDelay',
            'forceRefresh', 'conditions', 'hideOnCondition', 'creatable', 'editable',
        ] as $optionalKey) {
            if (array_key_exists($optionalKey, $def)) {
                $action[$optionalKey] = $def[$optionalKey];
            }
        }

        if (isset($def['form_action_extras']) && is_array($def['form_action_extras'])) {
            $action = array_merge($action, $def['form_action_extras']);
        }

        return $action;
    }

    /**
     * @return array{responseDisplay: string, responseFields: list<array{key: string, label: string}>}
     */
    protected function resolvePreviewResponseConfig(): array
    {
        $connector = $this->remoteApiConnector();

        if ($connector instanceof AbstractRemoteApiConnector) {
            return [
                'responseDisplay' => $connector->previewResponseDisplay(),
                'responseFields' => $connector->previewResponseFields(),
            ];
        }

        return [
            'responseDisplay' => 'fields',
            'responseFields' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'description', 'label' => 'Description'],
            ],
        ];
    }

    protected function resolveRemoteApiModuleName(): string
    {
        return $this->getModuleName() ?? 'App';
    }

    protected function resolveRemoteApiRouteName(): string
    {
        return snakeCase($this->getRouteName() ?? class_basename($this->getModel()));
    }
}

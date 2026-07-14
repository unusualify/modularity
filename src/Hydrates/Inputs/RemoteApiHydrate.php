<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;

/**
 * Select input for linking a local record to a remote API row via {@see HasRemoteApiSource} virtual {@code remote_id}.
 *
 * Config type: {@code remote-api} → hydrated {@code input-remote-api}.
 * Loads catalog rows from the route repository's {@see RemoteApiSourceTrait::listRemoteCatalog()} and
 * exposes {@code catalogEndpoint} for frontend refresh when hydrate could not load items.
 */
class RemoteApiHydrate extends InputHydrate
{
    public $selectable = true;

    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'name' => 'remote_id',
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => null,
        'cascadeKey' => 'items',
        'returnObject' => false,
    ];

    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-remote-api';
        $input['default'] = null;

        $this->applyRemoteApiConnectorSchema($input);

        return $input;
    }

    protected function hydrateRecords()
    {
        $input = $this->input;

        if ((! isset($input['items']) || $input['items'] === []) && ! App::runningInConsole()) {
            $repository = $this->resolveRepository();

            if ($repository !== null && method_exists($repository, 'listRemoteCatalog')) {
                $catalogKey = isset($input['catalog']) && is_string($input['catalog']) ? $input['catalog'] : null;

                try {
                    $items = $repository->listRemoteCatalog($catalogKey);
                    $input['items'] = $items;
                    $input['catalogTotal'] = count($items);
                } catch (\Throwable $exception) {
                    Log::warning('Remote API catalog hydrate failed.', [
                        'catalog_key' => $catalogKey,
                        'repository' => $repository::class,
                        'message' => $exception->getMessage(),
                    ]);
                    $input['items'] = [];
                    $input['catalogTotal'] = 0;
                }
            }
        }

        if ($this->selectable) {
            $this->prependPleaseSelect($input);
        }

        return $input;
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function applyRemoteApiConnectorSchema(array &$input): void
    {
        $repository = $this->resolveRepository();

        if ($repository === null || ! method_exists($repository, 'remoteApiConnector')) {
            return;
        }

        try {
            $configuration = $repository->remoteApiConnector()->configuration();
        } catch (RemoteApiConfigurationException) {
            return;
        }

        $input['itemValue'] ??= $this->resolveListIdKey($configuration);
        $input['itemTitle'] ??= (string) ($configuration->config['catalog_title_key'] ?? 'name');
        $input['remoteIdColumn'] = $configuration->remoteIdColumn();

        if ($this->hasModule() && $this->hasRouteName()) {
            $input['catalogEndpoint'] = $this->getModule()->getRouteActionUrl(
                $this->getRouteName(),
                'listRemoteCatalog'
            );
        }
    }

    protected function resolveRepository(): ?object
    {
        if (isset($this->input['repository']) && is_string($this->input['repository'])) {
            $className = explode(':', $this->input['repository'])[0];

            if (@class_exists($className)) {
                return App::make($className);
            }
        }

        if ($this->hasModule() && $this->hasRouteName()) {
            return $this->getModule()->getRepository($this->getRouteName());
        }

        return null;
    }

    protected function resolveListIdKey(RemoteApiConfiguration $configuration): string
    {
        $remoteIdColumn = $configuration->remoteIdColumn();
        $mapping = $configuration->config['mapping'] ?? [];

        foreach ($mapping as $localKey => $remoteKey) {
            if ((string) $localKey === $remoteIdColumn) {
                return (string) $remoteKey;
            }
        }

        return 'id';
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function prependPleaseSelect(array &$input): void
    {
        if (! isset($input['items']) || count($input['items']) === 0) {
            return;
        }

        $itemValue = $input['itemValue'] ?? 'id';
        $itemTitle = $input['itemTitle'] ?? 'name';
        $firstItem = $input['items'][0];

        if (! isset($firstItem[$itemValue]) || ! $firstItem[$itemValue]) {
            return;
        }

        $itemValueType = gettype($firstItem[$itemValue]);

        array_unshift($input['items'], [
            'id' => 0,
            $itemValue => $itemValueType === 'integer' ? 0 : '',
            $itemTitle => __('Please Select'),
        ]);
    }
}

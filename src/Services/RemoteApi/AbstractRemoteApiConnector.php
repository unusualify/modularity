<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\Contracts\DefinesRemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiAdapterInterface;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;

abstract class AbstractRemoteApiConnector implements DefinesRemoteApiConfiguration, RemoteApiConnectorInterface
{
    /**
     * @return array<string, mixed>
     */
    abstract public static function remoteApiConfiguration(): array;

    public function __construct(
        protected readonly RemoteApiConfiguration $configuration,
        protected readonly RemoteApiClient $client,
        protected readonly RemoteApiCache $cache,
        protected readonly RemoteApiAdapterInterface $adapter,
    ) {}

    public function configuration(): RemoteApiConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchList(array $query = []): array
    {
        $query = $this->beforeFetch($query);

        $items = $this->cache->rememberPaginatedCatalog(
            'list:v2:' . $this->hashQuery($query),
            function () use ($query) {
                $result = $this->client->fetchPaginatedListResult(
                    $this->configuration->endpoint(),
                    $query,
                    $this->configuration->listPath(),
                );

                return [
                    'expected_total' => $result['expected_total'],
                    'items' => $this->afterFetch($result['items'], $query),
                ];
            }
        );

        $this->warmDefaultCatalogCacheFromList($items);

        return $items;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOne(int|string $remoteId, array $query = []): ?array
    {
        $query = $this->beforeFetch($query);

        return $this->cache->remember('record:' . $remoteId, function () use ($remoteId, $query) {
            $item = $this->client->getItem(
                $this->configuration->showEndpoint($remoteId),
                $query,
                $this->configuration->itemPath()
            );

            if ($item === null) {
                return null;
            }

            return $this->afterFetchOne($item, $remoteId, $query);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listCatalog(?string $catalogKey = null, array $query = []): array
    {
        if ($catalogKey === null) {
            $catalogQuery = $this->beforeCatalogFetch($query);
            $cacheKey = 'catalog:v2:default:' . $this->hashQuery($catalogQuery);

            return $this->cache->rememberPaginatedCatalog($cacheKey, function () use ($catalogQuery) {
                $listItems = $this->resolveDefaultCatalogItemsFromListCache($catalogQuery);

                if ($listItems !== null) {
                    return [
                        'expected_total' => count($listItems),
                        'items' => $listItems,
                    ];
                }

                $result = $this->client->fetchPaginatedListResult(
                    $this->configuration->endpoint(),
                    $catalogQuery,
                    $this->configuration->listPath(),
                );

                return [
                    'expected_total' => $result['expected_total'],
                    'items' => $this->afterFetch($result['items'], $catalogQuery),
                ];
            });
        }

        $query = $this->beforeFetch($query);
        $endpoint = $this->configuration->catalogEndpoint($catalogKey);
        $listPath = $this->configuration->catalogListPath($catalogKey);

        return $this->cache->rememberPaginatedCatalog(
            'catalog:v2:' . $catalogKey . ':' . $this->hashQuery($query),
            function () use ($endpoint, $query, $listPath) {
                $result = $this->client->fetchPaginatedListResult($endpoint, $query, $listPath);

                return [
                    'expected_total' => $result['expected_total'],
                    'items' => $this->afterFetch($result['items'], $query),
                ];
            }
        );
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function beforeCatalogFetch(array $query): array
    {
        return array_merge(
            $this->configuration->catalogHttpQuery(),
            ['_skip_sync_includes' => true],
            $query,
        );
    }

    /**
     * @param array<string, mixed> $existingAttributes
     * @return array<string, mixed>
     */
    public function mapRow(array $row, array $existingAttributes = []): array
    {
        return $this->adapter->mapToAttributes($row, $existingAttributes);
    }

    public function clearCache(int|string|null $remoteId = null): void
    {
        $this->cache->flush($remoteId);
    }

    /**
     * @return array{total: int, by_url: array<string, int>}
     */
    public function flushRequestStats(): array
    {
        return $this->client->flushRequestStats();
    }

    public function resetRequestStats(): void
    {
        $this->client->requestTracker()->reset();
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function beforeFetch(array $query): array
    {
        return $query;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function afterFetch(array $items, array $query): array
    {
        return $items;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    protected function afterFetchOne(array $item, int|string $remoteId, array $query): array
    {
        return $item;
    }

    public function previewResponseDisplay(): string
    {
        return $this->configuration->previewResponseDisplay();
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function previewResponseFields(): array
    {
        return $this->configuration->previewResponseFields();
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array{key: string, label: string, value: mixed}>
     */
    public function buildPreviewResponseDisplay(array $payload): array
    {
        $fields = [];

        foreach ($this->previewResponseFields() as $field) {
            $key = $field['key'];

            if ($key === '') {
                continue;
            }

            $fields[] = [
                'key' => $key,
                'label' => $field['label'],
                'value' => data_get($payload, $key),
            ];
        }

        return $this->customizePreviewResponseDisplay($fields, $payload);
    }

    /**
     * @param list<array{key: string, label: string, value: mixed}> $fields
     * @param array<string, mixed> $payload
     * @return list<array{key: string, label: string, value: mixed}>
     */
    protected function customizePreviewResponseDisplay(array $fields, array $payload): array
    {
        return $fields;
    }

    protected function hashQuery(array $query): string
    {
        return RemoteApiCache::queryHash($query);
    }

    /**
     * @param array<int, array<string, mixed>> $listItems
     */
    protected function warmDefaultCatalogCacheFromList(array $listItems): void
    {
        if ($listItems === []) {
            return;
        }

        $catalogQuery = $this->beforeCatalogFetch([]);
        $catalogItems = $this->afterFetch($listItems, $catalogQuery);

        if ($catalogItems === []) {
            return;
        }

        $this->cache->putPaginatedCatalog(
            'catalog:v2:default:' . $this->hashQuery($catalogQuery),
            count($catalogItems),
            $catalogItems,
        );
    }

    /**
     * @param array<string, mixed> $catalogQuery
     * @return array<int, array<string, mixed>>|null
     */
    protected function resolveDefaultCatalogItemsFromListCache(array $catalogQuery): ?array
    {
        $listQuery = $this->beforeFetch([]);
        $listItems = $this->cache->getPaginatedCatalogIfValid('list:v2:' . $this->hashQuery($listQuery));

        if ($listItems === null) {
            return null;
        }

        $catalogItems = $this->afterFetch($listItems, $catalogQuery);

        return $catalogItems === [] ? null : $catalogItems;
    }
}

<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiAdapter;
use Unusualify\Modularous\Services\RemoteApi\AbstractRemoteApiConnector;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiCache;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiClient;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiFieldMapper;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Tests\TestCase;

class AbstractRemoteApiConnectorCacheTest extends TestCase
{
    public function test_list_catalog_uses_list_cache_without_http_when_sync_warmed_list(): void
    {
        config([
            'cache.default' => 'array',
            'modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1',
            'modularous.remote_api.logging.enabled' => false,
        ]);

        $connector = $this->makeConnector();
        $cache = new RemoteApiCache($connector->configuration());
        $listQuery = [];
        $listKey = 'list:v2:' . RemoteApiCache::queryHash($listQuery);

        $cache->putPaginatedCatalog($listKey, 2, [
            ['id' => 1, 'name' => 'Wire', 'description' => 'Full'],
            ['id' => 2, 'name' => 'Premium', 'description' => 'Full'],
        ]);

        Http::fake();

        $items = $connector->listCatalog();

        $this->assertSame([
            ['id' => 1, 'name' => 'Wire'],
            ['id' => 2, 'name' => 'Premium'],
        ], $items);
        Http::assertNothingSent();
    }

    public function test_fetch_list_warms_default_catalog_cache_for_hydrate(): void
    {
        config([
            'cache.default' => 'array',
            'modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1',
            'modularous.remote_api.logging.enabled' => false,
        ]);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::response([
                'data' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 2,
                    'per_page' => 100,
                    'next_page_url' => null,
                    'data' => [
                        ['id' => 1, 'name' => 'Wire', 'description' => 'Full'],
                        ['id' => 2, 'name' => 'Premium', 'description' => 'Full'],
                    ],
                ],
            ]),
        ]);

        $connector = $this->makeConnector();
        $connector->fetchList();

        Http::fake();

        $catalogItems = $connector->listCatalog();

        $this->assertSame([
            ['id' => 1, 'name' => 'Wire'],
            ['id' => 2, 'name' => 'Premium'],
        ], $catalogItems);
        Http::assertNothingSent();
    }

    private function makeConnector(): AbstractRemoteApiConnector
    {
        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'catalog_http' => [
                'query' => [
                    'per_page' => 100,
                ],
            ],
            'response' => [
                'list_path' => 'data.data',
                'item_path' => 'data',
                'meta_path' => 'data',
            ],
            'mapping' => [
                'remote_id' => 'id',
                'synced_name' => 'name',
            ],
            'classes' => [
                'adapter' => CatalogTestAdapter::class,
            ],
        ]);

        $fieldMapper = new RemoteApiFieldMapper($configuration);
        $adapter = new CatalogTestAdapter($configuration, $fieldMapper);
        $client = new RemoteApiClient(
            $configuration,
            new RemoteApiRateLimiter(['enabled' => false]),
        );
        $cache = new RemoteApiCache($configuration);

        return new CatalogTestConnector($configuration, $client, $cache, $adapter);
    }

    private function makeModule(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return $module;
    }
}

class CatalogTestConnector extends AbstractRemoteApiConnector
{
  /**
   * @return array<string, mixed>
   */
    public static function remoteApiConfiguration(): array
    {
        return ['enabled' => false];
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<int, array<string, mixed>>
     */
    protected function afterFetch(array $items, array $query): array
    {
        if ($query['_skip_sync_includes'] ?? false) {
            return array_values(array_map(static fn (array $row): array => [
                'id' => $row['id'],
                'name' => $row['name'],
            ], $items));
        }

        return parent::afterFetch($items, $query);
    }
}

class CatalogTestAdapter extends AbstractRemoteApiAdapter
{
}

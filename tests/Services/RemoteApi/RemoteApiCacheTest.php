<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiCache;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiLogger;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiCacheTest extends TestCase
{
    public function test_remember_uses_untagged_cache_when_store_does_not_support_tags(): void
    {
        config(['cache.default' => 'array']);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $calls = 0;

        $first = $cache->remember('catalog:default:test', function () use (&$calls) {
            $calls++;

            return ['id' => 1, 'name' => 'Wire'];
        });

        $second = $cache->remember('catalog:default:test', function () use (&$calls) {
            $calls++;

            return ['id' => 2, 'name' => 'Premium'];
        });

        $this->assertSame(['id' => 1, 'name' => 'Wire'], $first);
        $this->assertSame(['id' => 1, 'name' => 'Wire'], $second);
        $this->assertSame(1, $calls);
    }

    public function test_flush_bumps_cache_version_without_tag_support(): void
    {
        config(['cache.default' => 'array']);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $calls = 0;

        $cache->remember('catalog:default:test', function () use (&$calls) {
            $calls++;

            return ['id' => 1];
        });

        $cache->flush();

        $cache->remember('catalog:default:test', function () use (&$calls) {
            $calls++;

            return ['id' => 2];
        });

        $this->assertSame(2, $calls);
    }

    public function test_remember_non_empty_does_not_persist_empty_arrays(): void
    {
        config(['cache.default' => 'array']);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $calls = 0;

        $first = $cache->rememberNonEmpty('catalog:default:empty', function () use (&$calls) {
            $calls++;

            return [];
        });

        $second = $cache->rememberNonEmpty('catalog:default:empty', function () use (&$calls) {
            $calls++;

            return [['id' => 1, 'name' => 'Wire']];
        });

        $this->assertSame([], $first);
        $this->assertSame([['id' => 1, 'name' => 'Wire']], $second);
        $this->assertSame(2, $calls);
    }

    public function test_remember_paginated_catalog_discards_legacy_flat_cache(): void
    {
        config(['cache.default' => 'array']);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $prefixedKey = (new \ReflectionClass($cache))->getMethod('prefixKey');
        $prefixedKey->setAccessible(true);
        $key = $prefixedKey->invoke($cache, 'catalog:v2:default:test');

        Cache::put($key, [['id' => 1], ['id' => 2]], now()->addHour());

        $calls = 0;
        $items = $cache->rememberPaginatedCatalog('catalog:v2:default:test', function () use (&$calls) {
            $calls++;

            return [
                'expected_total' => 3,
                'items' => [['id' => 1], ['id' => 2], ['id' => 3]],
            ];
        });

        $this->assertSame([['id' => 1], ['id' => 2], ['id' => 3]], $items);
        $this->assertSame(1, $calls);
    }

    public function test_remember_paginated_catalog_rejects_partial_cache(): void
    {
        config(['cache.default' => 'array']);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $prefixedKey = (new \ReflectionClass($cache))->getMethod('prefixKey');
        $prefixedKey->setAccessible(true);
        $key = $prefixedKey->invoke($cache, 'catalog:v2:default:partial');

        Cache::put($key, [
            'expected_total' => 275,
            'items' => [['id' => 1], ['id' => 2]],
        ], now()->addHour());

        $calls = 0;
        $items = $cache->rememberPaginatedCatalog('catalog:v2:default:partial', function () use (&$calls) {
            $calls++;

            return [
                'expected_total' => 275,
                'items' => array_fill(0, 275, ['id' => 1]),
            ];
        });

        $this->assertCount(275, $items);
        $this->assertSame(1, $calls);
    }

    public function test_remember_paginated_catalog_rejects_partial_sync_list_cache(): void
    {
        config(['cache.default' => 'array', 'modularous.remote_api.logging.enabled' => false]);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration);
        $prefixedKey = (new \ReflectionClass($cache))->getMethod('prefixKey');
        $prefixedKey->setAccessible(true);
        $key = $prefixedKey->invoke($cache, 'list:v2:abc123');

        Cache::put($key, [
            'expected_total' => 50,
            'items' => [['id' => 1]],
        ], now()->addHour());

        $calls = 0;
        $items = $cache->rememberPaginatedCatalog('list:v2:abc123', function () use (&$calls) {
            $calls++;

            return [
                'expected_total' => 50,
                'items' => array_map(static fn (int $id) => ['id' => $id], range(1, 50)),
            ];
        });

        $this->assertCount(50, $items);
        $this->assertSame(1, $calls);
    }

    public function test_query_hash_is_stable_for_equivalent_query_key_order(): void
    {
        $first = RemoteApiCache::queryHash(['per_page' => 100, 'include' => 'packageable', '_skip_sync_includes' => true]);
        $second = RemoteApiCache::queryHash(['_skip_sync_includes' => true, 'include' => 'packageable', 'per_page' => 100]);

        $this->assertSame($first, $second);
    }

    public function test_remember_paginated_catalog_logs_cache_hit_and_miss(): void
    {
        config(['cache.default' => 'array']);

        $logger = Mockery::mock(RemoteApiLogger::class);
        $logger->shouldReceive('logCacheAccess')
            ->once()
            ->with('catalog:v2:default:test', 'miss');
        $logger->shouldReceive('logCacheAccess')
            ->once()
            ->with('catalog:v2:default:test', 'hit', 1);

        $configuration = new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
        ]);

        $cache = new RemoteApiCache($configuration, $logger);
        $key = 'catalog:v2:default:test';

        $cache->rememberPaginatedCatalog($key, function () {
            return [
                'expected_total' => 1,
                'items' => [['id' => 1, 'name' => 'Wire']],
            ];
        });

        $cache->rememberPaginatedCatalog($key, function () {
            return [
                'expected_total' => 2,
                'items' => [['id' => 2, 'name' => 'Other']],
            ];
        });
    }

    private function makeModule(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return $module;
    }
}

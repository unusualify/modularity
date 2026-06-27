<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Support\Facades\Http;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiClient;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiClientTest extends TestCase
{
    public function test_get_throws_rate_limit_exception_on_429_response(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::response([], 429, ['Retry-After' => '30']),
        ]);

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
        );

        $this->expectException(RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API rate limit exceeded');

        $client->get('packages');
    }

    public function test_get_records_http_requests_per_url(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::response([
                'data' => ['id' => 1, 'name' => 'Premium'],
            ]),
        ]);

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
        );

        $client->get('packages', ['page' => 1]);

        $this->assertSame(1, $client->requestTracker()->total());
        $this->assertSame(1, $client->requestTracker()->countsByUrl()['http://app.b2press.test/api/v1/packages']);
    }

    public function test_fetch_paginated_list_fetches_every_page(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::sequence()
                ->push($this->paginatedResponse(1, 3, 5, [['id' => 1], ['id' => 2]], hasNext: true))
                ->push($this->paginatedResponse(2, 3, 5, [['id' => 3], ['id' => 4]], hasNext: true))
                ->push($this->paginatedResponse(3, 3, 5, [['id' => 5]], hasNext: false)),
        ]);

        $client = new RemoteApiClient($this->makeConfiguration());
        $items = $client->fetchPaginatedList('packages', ['per_page' => 100]);

        $this->assertSame([1, 2, 3, 4, 5], array_column($items, 'id'));
        Http::assertSentCount(3);
    }

    public function test_fetch_paginated_list_throws_when_pages_are_incomplete(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::sequence()
                ->push($this->paginatedResponse(1, 1, 275, [['id' => 1]])),
        ]);

        $client = new RemoteApiClient($this->makeConfiguration());

        $this->expectException(RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API pagination returned 1 of 275 expected records.');

        $client->fetchPaginatedList('packages', ['per_page' => 100]);
    }

    public function test_fetch_paginated_list_follows_next_page_url_when_last_page_is_wrong(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::sequence()
                ->push($this->paginatedResponse(1, 1, 3, [['id' => 1]], hasNext: true))
                ->push($this->paginatedResponse(2, 1, 3, [['id' => 2]], hasNext: true))
                ->push($this->paginatedResponse(3, 1, 3, [['id' => 3]], hasNext: false)),
        ]);

        $client = new RemoteApiClient($this->makeConfiguration());
        $items = $client->fetchPaginatedList('packages', ['per_page' => 100]);

        $this->assertSame([1, 2, 3], array_column($items, 'id'));
        Http::assertSentCount(3);
    }

    public function test_get_item_returns_null_on_404(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages/274*' => Http::response([
                'message' => 'No query results for model [Modules\\Package\\Entities\\Package] 274',
            ], 404),
        ]);

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
        );

        $this->assertNull($client->getItem('packages/274'));
    }

    public function test_get_throws_remote_api_sync_exception_on_404_when_not_allowed(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages/274*' => Http::response([], 404),
        ]);

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
        );

        $this->expectException(RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API record [274] was not found.');

        $client->get('packages/274');
    }

    /**
     * @param list<array<string, int>> $records
     * @return array<string, mixed>
     */
    private function paginatedResponse(
        int $currentPage,
        int $lastPage,
        int $total,
        array $records,
        bool $hasNext = false,
    ): array {
        $base = 'http://app.b2press.test/api/v1/packages';

        return [
            'data' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'total' => $total,
                'per_page' => 100,
                'next_page_url' => $hasNext ? $base . '?page=' . ($currentPage + 1) : null,
                'prev_page_url' => $currentPage > 1 ? $base . '?page=' . ($currentPage - 1) : null,
                'data' => $records,
            ],
        ];
    }

    private function makeConfiguration(): RemoteApiConfiguration
    {
        return new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'response' => [
                'list_path' => 'data.data',
                'item_path' => 'data',
                'meta_path' => 'data',
            ],
        ]);
    }

    private function makeModule(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return $module;
    }
}

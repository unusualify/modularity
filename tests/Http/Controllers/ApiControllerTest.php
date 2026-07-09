<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\Http\Controllers\Stubs\ApiControllerStub;
use Unusualify\Modularous\Tests\TestModulesCase;

class ApiControllerTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('modularous.api.rate_limiting.enabled', false);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_constructor_sets_api_defaults_from_request_header(): void
    {
        $request = Request::create('/api/items', 'GET');
        $request->headers->set('API-Version', 'v2');
        $controller = new ApiControllerStub($this->app, $request);

        $this->assertSame('v2', $controller->exposeApiVersion());
        $this->assertSame(15, $controller->exposeDefaultPerPage());
    }

    public function test_get_per_page_caps_value_at_max_per_page(): void
    {
        $controller = new ApiControllerStub(
            $this->app,
            Request::create('/api/items', 'GET', ['per_page' => 500])
        );

        $this->assertSame(100, $controller->exposeGetPerPage());
    }

    public function test_get_includes_for_eager_loading_merges_defaults(): void
    {
        $controller = new ApiControllerStub($this->app, Request::create('/api/items'));
        $controller->configureIncludes(['author'], ['author', 'tags']);

        $includes = $controller->exposeGetIncludesForEagerLoading();

        $this->assertContains('author', $includes);
    }

    public function test_respond_with_data_wraps_payload_when_wrapping_is_enabled(): void
    {
        $controller = new ApiControllerStub($this->app, Request::create('/api/items'));
        $controller->configureResponse(true, ['version' => 'v1']);

        $response = $controller->exposeRespondWithData(['id' => 1]);
        $payload = $response->getData(true);

        $this->assertSame(['id' => 1], $payload['data']);
        $this->assertSame(['version' => 'v1'], $payload['meta']);
    }

    public function test_index_returns_paginated_collection(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 1, 'name' => 'One']], 1, 15);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('query')->andReturnSelf();
        $repository->shouldReceive('get')->once()->andReturn($paginator);

        $controller = new ApiControllerStub($this->app, Request::create('/api/items'), $repository);
        $response = $controller->index();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $response->getData(true));
    }

    public function test_show_returns_not_found_when_item_is_missing(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 99)->andReturnNull();

        $controller = new ApiControllerStub($this->app, Request::create('/api/items/99'), $repository);
        $response = $controller->show(99);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_show_returns_resource_payload_when_item_exists(): void
    {
        $item = (object) ['id' => 3, 'name' => 'Item'];

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 3)->andReturn($item);

        $controller = new ApiControllerStub($this->app, Request::create('/api/items/3'), $repository);
        $response = $controller->show(3);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(3, $response->getData(true)['data']['id']);
    }

    public function test_store_creates_item_and_returns_created_response(): void
    {
        $item = (object) ['id' => 8, 'name' => 'Created'];

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('create')->once()->with(['name' => 'Created'])->andReturn($item);

        $request = Request::create('/api/items', 'POST', ['name' => 'Created']);
        $controller = new ApiControllerStub($this->app, $request, $repository);

        $response = $controller->store();

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(8, $response->getData(true)['data']['id']);
    }

    public function test_update_returns_not_found_when_item_is_missing(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 15)->andReturnNull();

        $controller = new ApiControllerStub($this->app, Request::create('/api/items/15'), $repository);

        $this->assertSame(404, $controller->update(15)->getStatusCode());
    }

    public function test_update_persists_changes_when_item_exists(): void
    {
        $item = (object) ['id' => 6, 'name' => 'Old'];
        $updated = (object) ['id' => 6, 'name' => 'New'];

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 6)->andReturn($item);
        $repository->shouldReceive('update')->with(6, ['name' => 'New'])->andReturn($updated);

        $request = Request::create('/api/items/6', 'PUT', ['name' => 'New']);
        $controller = new ApiControllerStub($this->app, $request, $repository);

        $response = $controller->update(6);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('New', $response->getData(true)['data']['name']);
    }

    public function test_destroy_deletes_existing_item(): void
    {
        $item = (object) ['id' => 11];

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 11)->andReturn($item);
        $repository->shouldReceive('delete')->with(11)->once();

        $controller = new ApiControllerStub($this->app, Request::create('/api/items/11'), $repository);
        $response = $controller->destroy(11);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_bulk_search_filters_and_meta_endpoints_delegate_to_repository(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getByIds')->andReturn(collect([(object) ['id' => 1]]));
        $repository->shouldReceive('search')->andReturn(collect([(object) ['id' => 2]]));
        $repository->shouldReceive('getCountForAll')->andReturn(10);
        $repository->shouldReceive('getCountForPublished')->andReturn(8);

        $controller = new ApiControllerStub(
            $this->app,
            Request::create('/api/items', 'GET', ['ids' => [1, 2], 'q' => 'press']),
            $repository
        );

        $this->assertSame(200, $controller->bulk()->getStatusCode());
        $this->assertSame(200, $controller->search()->getStatusCode());
        $this->assertSame(200, $controller->filters()->getStatusCode());

        $meta = $controller->meta()->getData(true)['data'];
        $this->assertSame(10, $meta['total_count']);
        $this->assertSame(8, $meta['active_count']);
    }

    public function test_get_includes_parses_comma_separated_include_parameter(): void
    {
        $controller = new ApiControllerStub(
            $this->app,
            Request::create('/api/items', 'GET', ['include' => 'author,tags'])
        );
        $controller->configureIncludes([], ['author', 'tags']);

        $this->assertSame(['author', 'tags'], $controller->exposeGetIncludes());
    }

    public function test_respond_with_data_skips_wrapper_when_wrapping_is_disabled(): void
    {
        $controller = new ApiControllerStub($this->app, Request::create('/api/items'));
        $controller->configureResponse(false);

        $payload = $controller->exposeRespondWithData(['plain' => true])->getData(true);

        $this->assertSame(['plain' => true], $payload);
    }

    public function test_destroy_returns_not_found_when_item_does_not_exist(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->andReturnNull();

        $controller = new ApiControllerStub($this->app, Request::create('/api/items/404'), $repository);

        $this->assertSame(404, $controller->destroy(404)->getStatusCode());
    }
}

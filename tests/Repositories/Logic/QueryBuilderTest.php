<?php

namespace Unusualify\Modularous\Tests\Repositories\Logic;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class QueryBuilderTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();
    }

    public function test_get_with_per_page_0(): void
    {
        $this->seedFilterFixtures();

        $emptyPaginator = $this->repository->get(perPage: 0, forcePagination: false);
        $this->assertInstanceOf(LengthAwarePaginator::class, $emptyPaginator);
        $this->assertEquals(5, $emptyPaginator->total());
    }

    public function test_get_with_per_page_negative_1(): void
    {
        $this->seedFilterFixtures();
        $results = $this->repository->get(perPage: -1, forcePagination: false);
        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertEquals(5, $results->total());
    }

    public function test_get_with_scopes_and_except_ids(): void
    {
        $this->seedFilterFixtures();

        $results = $this->repository->get(scopes: ['is_active' => true], orders: ['id' => 'asc'], perPage: 10, exceptIds: [3]);
        $this->assertSame([1, 4], $results->getCollection()->pluck('id')->all());
    }

    public function test_get_with_appends(): void
    {
        $this->seedFilterFixtures();
        $results = $this->repository->get(appends: ['owner_name'], perPage: 10);
        $this->assertCount(5, $results->getCollection());
        $this->assertArrayHasKey('owner_name', $results->getCollection()->first()->getAttributes());
        $this->assertEquals('Owner A', $results->getCollection()->first()->owner_name);
    }

    public function test_get_with_id_calculates_page(): void
    {
        $this->seedFilterFixtures();
        // There are 5 rows, perPage=2, id=3 should be on page 2
        $paginator = $this->repository->get(perPage: 2, id: 3);
        $this->assertEquals(2, $paginator->currentPage());
        $this->assertTrue($paginator->getCollection()->contains('id', 3));

        $paginator = $this->repository->get(orders: ['id' => 'asc'], perPage: 2, id: 3);
        $this->assertEquals(2, $paginator->currentPage());
        $this->assertTrue($paginator->getCollection()->contains('id', 3));

        $paginator = $this->repository->get(orders: ['id' => 'desc'], perPage: 2, id: 4);
        $this->assertEquals(1, $paginator->currentPage());
        $this->assertTrue($paginator->getCollection()->contains('id', 4));
    }

    public function test_get_search_relationship_fields(): void
    {
        $this->seedFilterFixtures();
        $results = $this->repository->get(scopes: [
            'searches' => [
                'owner.name',
            ],
            'search' => 'Owner B',
            'owner.name' => 'Owner B',
        ], perPage: 20);

        $this->assertTrue($results->getCollection()->contains('name', 'Carla'));
        $this->assertTrue($results->getCollection()->contains('name', 'Bob'));
        $this->assertFalse($results->getCollection()->contains('name', 'John'));

        $results = $this->repository->get(scopes: [
            'searches' => [
                'name',
                'owner.name',
            ],
            'search' => 'Alice',
            'name' => 'Alice',
            'owner.name' => 'Alice',
        ], perPage: 20);

        $this->assertTrue($results->getCollection()->contains('name', 'Alice'));
        $this->assertTrue($results->getCollection()->contains('name', 'Alice B'));
        $this->assertFalse($results->getCollection()->contains('name', 'Bob'));
        $this->assertFalse($results->getCollection()->contains('name', 'Carla'));
        $this->assertFalse($results->getCollection()->contains('name', 'John'));
    }

    public function test_get_by_id_with_with_and_withcount_and_lazy(): void
    {
        $this->seedFilterFixtures();
        $id = TestModel::where('name', 'Alice')->value('id');

        $model = $this->repository->getById($id, with: ['notes'], withCount: ['notes'], lazy: ['owner.posts', 'posts.translations']);
        $this->assertTrue($model->relationLoaded('posts'));
        $this->assertTrue($model->relationLoaded('notes'));
        $this->assertTrue($model->relationLoaded('owner'));
        $this->assertTrue($model->owner->relationLoaded('posts'));
        $this->assertArrayHasKey('notes_count', $model->getAttributes());
        $this->assertEquals(1, $model->notes_count);

        $model = $this->repository->getById($id, withCount: ['posts'], lazy: ['posts']);
        $this->assertTrue($model->relationLoaded('posts'));
        $this->assertArrayHasKey('posts_count', $model->getAttributes());
        $this->assertEquals(1, $model->posts_count);
    }

    public function test_get_by_ids_with_appends_and_with(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Bob'])->pluck('id')->all();

        $results = $this->repository->getByIds($ids, appends: ['owner_name'], with: ['notes'], scopes: [], orders: [], lazy: ['owner.posts', 'posts.translations']);
        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('owner'));
        $this->assertTrue($results->first()->relationLoaded('notes'));
        $this->assertTrue($results->first()->relationLoaded('posts'));
        $this->assertTrue($results->first()->owner->relationLoaded('posts'));
        $this->assertTrue($results->first()->posts->first()->relationLoaded('translations'));

        $results = $this->repository->getByIds($ids, lazy: ['posts']);
        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('posts'));
    }

    public function test_get_by_column_value(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Alice B'])->pluck('id')->all();

        $collection = $this->repository->getByColumnValue('id', $ids, isFormatted: true);
        $this->assertCount(2, $collection);
        $first = $collection->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);

        $collection = $this->repository->getByColumnValue('id', $ids);
        $this->assertCount(2, $collection);
        $first = $collection->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);

        $collection = $this->repository->getByColumnValue('id', $ids[0]);
        $this->assertCount(1, $collection);
        $first = $collection->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertEquals('Alice', $first['name']);
    }

    public function test_list_all_with_search(): void
    {
        $this->seedFilterFixtures();
        $results = $this->repository->listAll(scopes: ['searches' => ['name'], 'search' => 'Alice']);
        $this->assertSame(['Alice', 'Alice B'], $results->pluck('name')->all());
    }

    public function test_list_basic_columns_and_except_id(): void
    {
        $this->seedFilterFixtures();
        $results = $this->repository->list(column: 'name', with: ['owner'], scopes: [], orders: [], appends: [], perPage: -1, exceptId: 1);
        $this->assertNotEmpty($results);
        $first = $results->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('owner', $first);
        $this->assertNotEquals(1, $first['id']);

        $results = $this->repository->list(column: ['name', 'context'], with: ['owner'], scopes: [], orders: ['position' => 'desc'], appends: ['owner_name']);
        $this->assertNotEmpty($results);
        $first = $results->first();
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('owner', $first);
        $this->assertNotEquals(1, $first['id']);
        $this->assertEquals('John', $first['name']);
        $this->assertArrayHasKey('owner_name', $first);
        $this->assertEquals('Owner A', $first['owner_name']);
        // $this->assertEquals('John Context', $first['context']);
    }

    public function test_list_force_pagination_transforms_items(): void
    {
        $this->seedFilterFixtures();
        $paginator = $this->repository->list(column: 'name', with: ['owner'], scopes: [], orders: [], appends: ['owner_name'], perPage: 2, exceptId: null, forcePagination: true);
        $this->assertEquals(2, $paginator->perPage());
        $arrayItem = $paginator->getCollection()->first();
        $this->assertIsArray($arrayItem);
        $this->assertArrayHasKey('id', $arrayItem);
        $this->assertArrayHasKey('owner', $arrayItem);
        $this->assertArrayHasKey('name', $arrayItem);
        $this->assertArrayHasKey('owner_name', $arrayItem);
        $this->assertEquals('Owner A', $arrayItem['owner_name']);
    }

    public function test_format_withs_returns_callable_for_assoc_and_string_passthrough(): void
    {
        $with = $this->repository->formatWiths($this->repository->newQuery(), [
            'owner',
            ['roles' => ['functions' => ['select']]],
        ]);

        $this->assertSame('owner', $with[0]);
        $this->assertIsCallable($with[1]);
    }

    public function test_get_by_id_with_scopes_filters(): void
    {
        $this->seedFilterFixtures();
        $bobId = TestModel::where('name', 'Bob')->value('id');

        $found = $this->repository->getById($bobId, scopes: ['name' => 'Bob']);
        $this->assertSame('Bob', $found->name);
    }

    public function test_get_paginator_delegates_to_get_when_cache_disabled(): void
    {
        $this->seedFilterFixtures();

        $this->repository
            ->setCacheModuleName('Blog')
            ->setCacheModuleRouteName('Post')
            ->withoutCache();

        $paginator = $this->repository->getPaginator(perPage: 2, orders: ['id' => 'asc']);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame([1, 2], $paginator->getCollection()->pluck('id')->all());
    }

    public function test_get_cached_round_trips_paginator_payload(): void
    {
        $this->seedFilterFixtures();

        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.modules.Blog.enabled', true);
        Config::set('modularous.cache.modules.Blog.routes.Post.enabled', true);
        Config::set('modularous.cache.modules.Blog.routes.Post.types.index', true);

        $this->repository
            ->setCacheModuleName('Blog')
            ->setCacheModuleRouteName('Post')
            ->withCache(true);

        $first = $this->repository->getCached(perPage: 2, orders: ['id' => 'asc']);
        $second = $this->repository->getCached(perPage: 2, orders: ['id' => 'asc']);

        $this->assertInstanceOf(LengthAwarePaginator::class, $first);
        $this->assertSame(5, $first->total());
        $this->assertSame([1, 2], $first->getCollection()->pluck('id')->all());
        $this->assertSame($first->total(), $second->total());
        $this->assertSame($first->getCollection()->pluck('id')->all(), $second->getCollection()->pluck('id')->all());
    }

    public function test_paginate_reads_request_parameters(): void
    {
        $this->seedFilterFixtures();

        $request = Request::create('/items', 'GET', [
            'itemsPerPage' => 2,
            'orders' => ['id' => 'asc'],
            'scopes' => ['is_active' => true],
            'eager' => 'owner',
            'appends' => 'owner_name',
            'exceptIds' => '3',
        ]);

        $paginator = $this->repository
            ->withoutCache()
            ->paginate($request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(2, $paginator->perPage());
        $this->assertSame([1, 4], $paginator->getCollection()->pluck('id')->all());
        $this->assertArrayHasKey('owner_name', $paginator->getCollection()->first()->getAttributes());
    }

    public function test_get_by_id_with_scopes_enables_default_scopes_via_legacy_alias(): void
    {
        $this->seedFilterFixtures();
        $bobId = TestModel::where('name', 'Bob')->value('id');

        $found = $this->repository->getByIdWithScopes($bobId, scopes: ['name' => 'Bob']);

        $this->assertSame('Bob', $found->name);
    }

    public function test_get_searches_translated_attributes_separately_from_base_columns(): void
    {
        $this->seedFilterFixtures();

        $results = $this->repository->listAll(scopes: [
            'searches' => ['name', 'context'],
            'search' => 'Alice',
        ]);

        $this->assertSame(['Alice', 'Alice B'], $results->pluck('name')->sort()->values()->all());
    }

    public function test_get_by_ids_accepts_deprecated_is_formatted_flag(): void
    {
        $this->seedFilterFixtures();
        $ids = TestModel::whereIn('name', ['Alice', 'Bob'])->pluck('id')->all();

        $results = $this->repository->getByIds($ids, isFormatted: true);

        $this->assertCount(2, $results);
    }
}

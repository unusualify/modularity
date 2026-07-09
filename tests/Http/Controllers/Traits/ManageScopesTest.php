<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingManageScopes;
use Unusualify\Modularous\Tests\TestCase;

class ManageScopesTest extends TestCase
{
    protected ControllerUsingManageScopes $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingManageScopes;
    }

    public function test_after_construct_sets_default_table_orders_from_config(): void
    {
        Config::set(modularousBaseKey() . '.default_table_orders', ['updated_at' => 'asc']);

        $this->controller->invokeAfterConstructManageScopes($this->app, Request::create('/'));

        $this->assertSame(['updated_at' => 'asc'], $this->controller->getDefaultTableOrders());
    }

    public function test_preload_manage_scopes_loads_table_orders(): void
    {
        $this->controller->setTableOrders(['name' => 'asc']);

        $this->controller->invokePreloadManageScopes();

        $orders = $this->controller->invokeGetTableOrders();
        $this->assertArrayHasKey('name', $orders);
        $this->assertSame('asc', $orders['name']);
    }

    public function test_get_exact_scope_merges_fixed_and_config_scopes(): void
    {
        $this->controller->setFixedFilters(['locale' => 'en']);
        $this->controller->setConfigScopes(['published' => true]);

        $this->assertSame([
            'locale' => 'en',
            'published' => true,
        ], $this->controller->invokeGetExactScope());
    }

    public function test_get_request_filters_parses_search_and_json_filter(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'search' => 'hello',
            'filter' => json_encode(['status' => 'published', 'category_id' => 3]),
        ]));

        $this->assertSame([
            'search' => 'hello',
            'status' => 'published',
            'category_id' => 3,
        ], $this->controller->invokeGetRequestFilters());
    }

    public function test_filter_scope_maps_published_status(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'published']),
        ]));

        $scope = $this->controller->invokeFilterScope();

        $this->assertTrue($scope['published']);
        $this->assertArrayNotHasKey('status', $scope);
    }

    public function test_filter_scope_maps_trash_and_mine_statuses(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'trash']),
        ]));
        $this->assertTrue($this->controller->invokeFilterScope()['onlyTrashed']);

        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'mine']),
        ]));
        $this->assertTrue($this->controller->invokeFilterScope()['mine']);
    }

    public function test_filter_scope_maps_custom_main_filter_slug(): void
    {
        $this->controller->setConfigTableFilters([
            (object) ['slug' => 'featured', 'scope' => 'isFeatured'],
        ]);
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'featured']),
        ]));

        $this->assertTrue($this->controller->invokeFilterScope()['isFeatured']);
    }

    public function test_filter_scope_maps_stateable_status(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'isStateablePendingReview']),
        ]));

        $this->assertSame('pending-review', $this->controller->invokeFilterScope()['isStateable']);
    }

    public function test_filter_scope_applies_search_and_column_filters(): void
    {
        $this->controller->setFilters([
            'search' => 'title|slug',
            'category_id' => 'category_id',
        ]);
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode([
                'search' => 'press',
                'columns' => ['locale' => 'en', 'empty' => ''],
            ]),
        ]));

        $scope = $this->controller->invokeFilterScope();

        $this->assertSame(['title', 'slug'], $scope['searches']);
        $this->assertSame('press', $scope['search']);
        $this->assertSame('en', $scope['locale']);
        $this->assertArrayNotHasKey('empty', $scope);
    }

    public function test_filter_scope_applies_relation_filters(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode([
                'relations' => ['author' => 5],
            ]),
        ]));

        $this->assertSame(5, $this->controller->invokeFilterScope()['addRelationAuthor']);
    }

    public function test_filter_scope_prepends_parent_scopes(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'draft']),
        ]));

        $scope = $this->controller->invokeFilterScope(['parent_id' => 9]);

        $this->assertSame(9, $scope['parent_id']);
        $this->assertTrue($scope['draft']);
    }

    public function test_apply_filters_default_options_merges_missing_defaults(): void
    {
        $this->controller->setFiltersDefaultOptions(['status' => 'published']);
        $this->controller->setRequest(Request::create('/', 'GET'));

        $this->controller->invokeApplyFiltersDefaultOptions();

        $filters = json_decode((string) $this->controller->request->get('filter'), true);
        $this->assertSame('published', $filters['status']);
    }

    public function test_apply_filters_default_options_skips_when_search_present(): void
    {
        $this->controller->setFiltersDefaultOptions(['status' => 'published']);
        $this->controller->setRequest(Request::create('/', 'GET', ['search' => 'x']));

        $this->controller->invokeApplyFiltersDefaultOptions();

        $this->assertNull($this->controller->request->get('filter'));
    }

    public function test_order_scope_uses_sort_by_and_default_orders(): void
    {
        $this->controller->setTableOrders(['created_at' => 'desc']);
        $this->controller->setRequest(Request::create('/', 'GET', [
            'sortBy' => [json_encode(['key' => 'name', 'order' => 'asc'])],
        ]));

        $this->assertSame([
            'name' => 'asc',
            'created_at' => 'desc',
        ], $this->controller->invokeOrderScope());
    }

    public function test_order_scope_strips_timestamp_suffix_from_sort_key(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'sortBy' => [json_encode(['key' => 'published_timestamp', 'order' => 'desc'])],
        ]));

        $this->assertSame(['published' => 'desc'], $this->controller->invokeOrderScope());
    }
}

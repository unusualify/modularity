<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mockery;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingTableFilters;
use Unusualify\Modularous\Tests\TestCase;

class TableFiltersTest extends TestCase
{
    protected ControllerUsingTableFilters $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingTableFilters;
        $this->controller->repository = $this->makeRepositoryMock();
        $this->controller->setRequest(Request::create('/'));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_counts_list_includes_all_published_and_trash_filters(): void
    {
        $counts = $this->controller->invokeGetCountsList(['parent_id' => 1]);

        $slugs = array_column($counts, 'slug');

        $this->assertContains('all', $slugs);
        $this->assertContains('published', $slugs);
        $this->assertContains('trash', $slugs);
    }

    public function test_get_counts_list_merges_repository_and_custom_filters(): void
    {
        $this->controller->repository = $this->makeRepositoryMock([
            ['name' => 'Featured', 'slug' => 'featured'],
        ]);
        $this->controller->setConfigTableFilters([
            (object) ['name' => 'Starred', 'slug' => 'starred', 'scope' => 'isStarred'],
        ]);

        $slugs = array_column($this->controller->invokeGetCountsList(), 'slug');

        $this->assertContains('featured', $slugs);
        $this->assertContains('starred', $slugs);
    }

    public function test_handle_filter_count_calls_repository_method(): void
    {
        $count = $this->controller->invokeHandleFilterCount([
            'slug' => 'published',
            'methods' => 'getCountByStatusSlug',
            'params' => ['published', []],
        ]);

        $this->assertSame(2, $count);
    }

    public function test_handle_filter_count_throws_when_methods_missing(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Number or methods is required');

        $this->controller->invokeHandleFilterCount(['slug' => 'broken']);
    }

    public function test_get_table_main_filters_omits_zero_count_filters(): void
    {
        $this->controller->repository = $this->makeRepositoryMock(counts: [
            'all' => 5,
            'published' => 0,
        ]);

        $filters = $this->controller->invokeGetTableMainFilters();

        $slugs = array_column($filters, 'slug');
        $this->assertContains('all', $slugs);
        $this->assertNotContains('published', $slugs);
    }

    public function test_get_table_main_filters_keeps_forced_zero_count_filters(): void
    {
        $this->controller->repository = $this->makeRepositoryMock(counts: [
            'all' => 0,
            'trash' => 0,
        ]);

        $slugs = array_column($this->controller->invokeGetTableMainFilters(), 'slug');

        $this->assertContains('all', $slugs);
        $this->assertContains('trash', $slugs);
    }

    public function test_get_table_main_filters_respects_allowed_roles(): void
    {
        $this->controller->user = (object) ['roles' => collect([])];
        $this->controller->setConfigTableFilters([
            (object) [
                'name' => 'Admin only',
                'slug' => 'admin-only',
                'allowedRoles' => ['super-admin'],
                'skip_count' => true,
            ],
        ]);

        $slugs = array_column($this->controller->invokeGetTableMainFilters(), 'slug');

        $this->assertNotContains('admin-only', $slugs);
    }

    public function test_get_main_counts_list_uses_filter_scope(): void
    {
        $this->controller->setRequest(Request::create('/', 'GET', [
            'filter' => json_encode(['status' => 'published']),
        ]));

        $counts = $this->controller->invokeGetMainCountsList();

        $this->assertNotEmpty($counts);
        $this->assertSame('all', $counts[0]['slug']);
    }

    public function test_get_table_advanced_filters_returns_configured_categories(): void
    {
        $this->controller->setRawFiltersConfig([
            'columns' => [
                ['slug' => 'locale', 'type' => 'select'],
            ],
        ]);
        $this->controller->repository = $this->makeRepositoryMock(hasColumn: ['locale' => true]);

        $advanced = $this->controller->invokeGetTableAdvancedFilters();

        $this->assertArrayHasKey('columns', $advanced);
        $this->assertSame('locale', $advanced['columns'][0]['slug']);
    }

    public function test_columns_filter_configuration_throws_for_missing_column(): void
    {
        $this->controller->repository = $this->makeRepositoryMock(hasColumn: ['locale' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Column 'locale' does not exist");

        $this->controller->invokeColumnsFilterConfiguration([
            'slug' => 'locale',
            'type' => 'select',
        ]);
    }

    public function test_calibrate_filter_resolves_callable_items(): void
    {
        $filter = $this->controller->invokeCalibrateFilter([
            'componentOptions' => [
                'items' => fn () => [['id' => 1, 'name' => 'One']],
            ],
        ]);

        $this->assertSame([['id' => 1, 'name' => 'One']], $filter['componentOptions']['items']);
    }

    public function test_get_table_advanced_filters_date_picker_sets_defaults(): void
    {
        $filter = $this->controller->invokeGetTableAdvancedFiltersDatePicker([
            'slug' => 'published_at',
        ]);

        $this->assertSame('Published At', $filter['componentOptions']['title']);
        $this->assertSame('range', $filter['componentOptions']['multiple']);
    }

    /**
     * @param array<int, array<string, mixed>> $tableFilters
     * @param array<string, int> $counts
     * @param array<string, bool> $hasColumn
     */
    protected function makeRepositoryMock(
        array $tableFilters = [],
        array $counts = ['all' => 5, 'published' => 2, 'trash' => 1],
        array $hasColumn = ['published' => true, 'locale' => true],
    ): object {
        $model = new class extends Model
        {
            protected $table = 'table_filters_test_models';
        };

        $repository = Mockery::mock();
        $repository->shouldReceive('getFillable')->andReturn(['published', 'title']);
        $repository->shouldReceive('hasColumn')->andReturnUsing(function (string $column) use ($hasColumn): bool {
            return $hasColumn[$column] ?? false;
        });
        $repository->shouldReceive('isSoftDeletable')->andReturn(true);
        $repository->shouldReceive('getTableFilters')->andReturn(array_map(
            fn (array $filter) => array_merge($filter, [
                'methods' => 'getCountFor',
                'params' => [$filter['slug']],
            ]),
            $tableFilters === [] ? [['name' => 'Repo', 'slug' => 'repo-filter']] : $tableFilters
        ));
        $repository->shouldReceive('getModel')->andReturn($model);
        $repository->shouldReceive('getCountByStatusSlug')->andReturnUsing(
            fn (string $slug) => $counts[$slug] ?? 0
        );
        $repository->shouldReceive('getCountFor')->andReturn(1);

        return $repository;
    }
}

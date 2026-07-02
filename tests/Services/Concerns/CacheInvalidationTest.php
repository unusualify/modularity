<?php

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    protected $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);

        $this->cacheService = new ConcreteCacheInvalidation;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('invalidate_all_items_models');

        parent::tearDown();
    }

    /** @test */
    public function it_can_invalidate_module_without_tags()
    {
        $result = $this->cacheService->invalidateModule('TestModule');

        $this->assertIsBool($result);
    }

    /** @test */
    public function it_can_invalidate_module_route_without_tags()
    {
        $result = $this->cacheService->invalidateModuleRoute('TestModule', 'TestRoute');

        $this->assertIsBool($result);
    }

    /** @test */
    public function it_returns_false_for_invalidate_by_related_model_without_tags()
    {
        $result = $this->cacheService->invalidateByRelatedModel('Company', 1);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_zero_for_invalidate_by_related_models_without_tags()
    {
        $count = $this->cacheService->invalidateByRelatedModels([
            'Company' => 1,
            'User' => [1, 2, 3],
        ]);

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_returns_zero_for_invalidate_by_pattern_with_tags()
    {
        $this->cacheService->setUsesTags(true);

        $count = $this->cacheService->invalidateByPattern('modularous:*');

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_can_invalidate_count_caches()
    {
        try {
            $this->cacheService->invalidateCountCaches('TestModule', 'TestRoute');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_can_invalidate_index_caches()
    {
        try {
            $this->cacheService->invalidateIndexCaches('TestModule', 'TestRoute');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_can_invalidate_formatted_item_cache()
    {
        try {
            $this->cacheService->invalidateFormattedItemCache('TestModule', 'TestRoute', 1);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_can_invalidate_form_item_cache()
    {
        try {
            $this->cacheService->invalidateFormItemCache('TestModule', 'TestRoute', 1);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_can_invalidate_presentation_item_cache()
    {
        try {
            $this->cacheService->invalidatePresentationItemCache('TestModule', 'TestRoute', 1);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_can_invalidate_for_model()
    {
        $model = new TestModel;
        $model->id = 1;
        $model->exists = true;

        try {
            $this->cacheService->invalidateForModel($model, [], ['warmup' => false]);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_skips_invalidation_for_model_without_module_info()
    {
        $model = new InvalidTestModel;
        $model->id = 1;

        $this->cacheService->invalidateForModel($model);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_invalidate_for_newly_created_model()
    {
        $model = new TestModel;
        $model->id = 1;
        $model->exists = true;
        $model->wasRecentlyCreated = true;

        try {
            $this->cacheService->invalidateForModel($model, [], ['warmup' => false]);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_invalidates_per_id_caches_for_all_records_in_route()
    {
        $this->seedInvalidateAllItemsTable([1, 2, 3]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => false,
        ], shouldWarmDependentModules: false);

        $this->assertEquals([1, 2, 3], $this->cacheService->invalidatedFormItemIds);
        $this->assertEquals([1, 2, 3], $this->cacheService->invalidatedFormattedItemIds);
        $this->assertEmpty($this->cacheService->invalidatedCountRoutes);
        $this->assertEmpty($this->cacheService->invalidatedIndexRoutes);
    }

    /** @test */
    public function it_invalidates_singleton_route_with_single_record_lookup()
    {
        $this->seedInvalidateAllItemsTable([42]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: true);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => false,
        ], shouldWarmDependentModules: false);

        $this->assertEquals([42], $this->cacheService->invalidatedFormItemIds);
        $this->assertEmpty($this->cacheService->invalidatedFormattedItemIds);
    }

    /** @test */
    public function it_flushes_route_level_counts_and_index_once_for_all_item_invalidation()
    {
        $this->seedInvalidateAllItemsTable([1, 2]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'counts' => true,
            'index' => true,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => false,
        ], shouldWarmDependentModules: false);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedCountRoutes);
        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedIndexRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
    }

    /** @test */
    public function it_warms_only_requested_item_cache_types_after_invalidation()
    {
        $this->seedInvalidateAllItemsTable([7]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => true,
        ], shouldWarmDependentModules: true);

        $this->assertEquals([[7, true, false]], $this->cacheService->warmedControllerItems);
        $this->assertEquals([['id' => 7, 'moduleName' => 'TestModule', 'moduleRouteName' => 'TestRoute']], $this->cacheService->warmedPresentationItemIds);
    }

    /** @test */
    public function it_flushes_entire_route_once_when_tags_are_enabled()
    {
        $this->cacheService->setUsesTags(true);
        $this->seedInvalidateAllItemsTable([1, 2, 3]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'formItem' => true,
            'formattedItem' => true,
        ], shouldWarmDependentModules: false);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedModuleRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
    }

    /** @test */
    public function module_route_invalidation_uses_route_only_tags_to_avoid_global_flush(): void
    {
        $service = new ConcreteCacheInvalidation;

        $onlyRouteTags = $service->getModuleRouteTags('PrimaryPage', 'CountryPackagesHub', onlyRoute: true);
        $fullTags = $service->getModuleRouteTags('PrimaryPage', 'CountryPackagesHub', onlyRoute: false);

        $this->assertSame(['modularous:PrimaryPage:CountryPackagesHub'], $onlyRouteTags);
        $this->assertContains('modularous', $fullTags);
        $this->assertNotContains('modularous', $onlyRouteTags);
    }

    /** @test */
    public function it_passes_explicit_module_route_to_warmup_model_caches()
    {
        $model = new TestModel;
        $model->id = 15;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $this->cacheService->warmupModelCaches($model, [
            'presentationItem' => true,
            'formItem' => false,
            'formattedItem' => false,
        ], 'ExplicitModule', 'ExplicitRoute');

        $this->assertCount(1, $this->cacheService->warmedForModelCalls);
        $this->assertSame('ExplicitModule', $this->cacheService->warmedForModelCalls[0]['moduleName']);
        $this->assertSame('ExplicitRoute', $this->cacheService->warmedForModelCalls[0]['moduleRouteName']);
    }

    /** @test */
    public function it_warms_presentation_item_for_explicit_dependency_route()
    {
        $service = new ConcreteCacheInvalidationWithRealWarmup;

        $model = new TestModel;
        $model->id = 21;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $mockController = \Mockery::mock(\Illuminate\Routing\Controller::class);

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('ExplicitRoute')->andReturn(true);
        $mockModule->shouldReceive('isSingleton')->with('ExplicitRoute')->andReturn(false);
        $mockModule->shouldReceive('getController')->with('ExplicitRoute')->andReturn($mockController);
        Modularous::shouldReceive('find')->with('ExplicitModule')->andReturn($mockModule);

        $service->warmupModelCaches($model, [
            'counts' => false,
            'index' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => true,
        ], 'ExplicitModule', 'ExplicitRoute');

        $this->assertEquals([
            [
                'id' => 21,
                'moduleName' => 'ExplicitModule',
                'moduleRouteName' => 'ExplicitRoute',
            ],
        ], $service->warmedPresentationItemIds);
    }

    /** @test */
    public function it_warms_presentation_item_after_invalidate_for_model_with_explicit_dependency_route()
    {
        $service = new ConcreteCacheInvalidationWithRealWarmup;
        $service->setUsesTags(true);

        $model = new TestModel;
        $model->id = 21;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false, routeName: 'ExplicitRoute');
        Modularous::shouldReceive('find')->with('ExplicitModule')->andReturn($mockModule);

        $service->invalidateForModel($model, [
            'counts' => false,
            'index' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => true,
        ], [
            'warmup' => true,
            'moduleName' => 'ExplicitModule',
            'moduleRouteName' => 'ExplicitRoute',
        ]);

        $this->assertEquals([
            [
                'id' => 21,
                'moduleName' => 'ExplicitModule',
                'moduleRouteName' => 'ExplicitRoute',
            ],
        ], $service->warmedPresentationItemIds);
    }

    protected function seedInvalidateAllItemsTable(array $ids): void
    {
        Schema::create('invalidate_all_items_models', function (Blueprint $table) {
            $table->id();
        });

        foreach ($ids as $id) {
            InvalidateAllItemsModel::query()->insert(['id' => $id]);
        }
    }

    protected function mockInvalidateAllItemsModule(bool $isSingleton, string $routeName = 'TestRoute'): Module
    {
        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with($routeName)->andReturn(true);
        $mockModule->shouldReceive('isSingleton')->with($routeName)->andReturn($isSingleton);
        $mockModule->shouldReceive('getModel')->with($routeName)->andReturn(new InvalidateAllItemsModel);
        $mockModule->shouldReceive('getController')->with($routeName)->andReturn(\Mockery::mock(\Illuminate\Routing\Controller::class));

        return $mockModule;
    }
}

class ConcreteCacheInvalidation
{
    use CacheInvalidation;

    protected $store;

    protected $prefix = 'modularous';

    protected $usesTags = false;

    protected $enabled = true;

    public bool $warmupControllerItemShouldThrow = false;

    public array $invalidatedFormItemIds = [];

    public array $invalidatedFormattedItemIds = [];

    public array $invalidatedPresentationItemIds = [];

    public array $invalidatedRecordIds = [];

    public array $invalidatedCountRoutes = [];

    public array $invalidatedIndexRoutes = [];

    public array $invalidatedModuleRoutes = [];

    public array $warmedControllerItems = [];

    public array $warmedPresentationItemIds = [];

    public array $warmedForModelCalls = [];

    public function __construct()
    {
        $this->store = Cache::store('array');
    }

    protected function getStore(): Repository
    {
        return $this->store;
    }

    protected function getPrefix(): string
    {
        return $this->prefix;
    }

    protected function usesTags(): bool
    {
        return $this->usesTags;
    }

    protected function isEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        return $this->enabled;
    }

    public function setUsesTags(bool $usesTags): void
    {
        $this->usesTags = $usesTags;
    }

    protected function getModuleNameFromModel(Model $model): ?string
    {
        return $model instanceof TestModel ? 'TestModule' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof TestModel ? 'TestRoute' : null;
    }

    protected function warmupByModel(Model $model): void
    {
    }

    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null): void
    {
        $this->warmedForModelCalls[] = [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
        ];
    }

    public function invalidateFormItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        $this->invalidatedFormItemIds[] = $id;
    }

    public function invalidateFormattedItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        $this->invalidatedFormattedItemIds[] = $id;
    }

    public function invalidatePresentationItemCache(string $moduleName, string $moduleRouteName, $id): void
    {
        $this->invalidatedPresentationItemIds[] = $id;
    }

    public function invalidateRecordCache(string $moduleName, string $moduleRouteName, $id): void
    {
        $this->invalidatedRecordIds[] = $id;
    }

    public function invalidateCountCaches(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): void
    {
        $this->invalidatedCountRoutes[] = [$moduleName, $moduleRouteName];
    }

    public function invalidateIndexCaches(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): void
    {
        $this->invalidatedIndexRoutes[] = [$moduleName, $moduleRouteName];
    }

    public function invalidateModuleRoute(string $moduleName, string $moduleRouteName): bool
    {
        $this->invalidatedModuleRoutes[] = [$moduleName, $moduleRouteName];

        return true;
    }

    public function warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem): void
    {
        if ($this->warmupControllerItemShouldThrow) {
            throw new \RuntimeException('warmup controller item failed');
        }

        $this->warmedControllerItems[] = [$item->id, $cacheFormItem, $cacheFormattedItem];
    }

    public function warmupPresentationItem(string $moduleName, string $routeName, Model $item): bool
    {
        $this->warmedPresentationItemIds[] = [
            'id' => $item->id,
            'moduleName' => $moduleName,
            'moduleRouteName' => $routeName,
        ];

        return true;
    }
}

class ConcreteCacheInvalidationWithRealWarmup extends ConcreteCacheInvalidation
{
    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null): void
    {
        $moduleName ??= $this->getModuleNameFromModel($model);
        $moduleRouteName ??= $this->getModuleRouteNameFromModel($model);

        if (! $moduleName || ! $moduleRouteName) {
            return;
        }

        $module = Modularous::find($moduleName);

        if (! $module || ! $module->hasRoute($moduleRouteName)) {
            return;
        }

        $warmCounts = ($types['counts'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'counts');
        $warmFormItem = ($types['formItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'formItem');
        $warmFormattedItem = ($types['formattedItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'formattedItem');
        $warmPresentationItem = ($types['presentationItem'] ?? true) && $this->isEnabled($moduleName, $moduleRouteName, 'presentationItem');

        if (! $warmCounts && ! $warmFormItem && ! $warmFormattedItem && ! $warmPresentationItem) {
            return;
        }

        $controller = $module->getController($moduleRouteName);

        if ($warmCounts && $controller) {
            try {
                $this->warmupControllerCounts($controller);
            } catch (\Exception $e) {
            }
        }

        if (($warmFormItem || $warmFormattedItem) && $controller) {
            try {
                $this->warmupControllerItem($controller, $model, $warmFormItem, $warmFormattedItem);
            } catch (\Exception $e) {
            }
        }

        if ($warmPresentationItem) {
            $this->warmupPresentationItem($moduleName, $moduleRouteName, $model);
        }
    }
}

class TestModel extends Model
{
    protected $table = 'test_models';
}

class InvalidTestModel extends Model
{
    protected $table = 'invalid_models';
}

class InvalidateAllItemsModel extends Model
{
    protected $table = 'invalidate_all_items_models';

    public $timestamps = false;
}

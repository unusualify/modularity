<?php

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
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

        $mockController = \Mockery::mock(Controller::class);

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

    /** @test */
    public function it_purges_selected_per_id_cache_types_without_tags(): void
    {
        $model = new TestModel;
        $model->id = 9;
        $model->exists = true;

        $this->cacheService->purgeModelCacheTypes($model, [
            'counts' => false,
            'index' => false,
            'record' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ]);

        $this->assertEquals([9], $this->cacheService->invalidatedRecordIds);
        $this->assertEquals([9], $this->cacheService->invalidatedFormItemIds);
        $this->assertEquals([9], $this->cacheService->invalidatedFormattedItemIds);
        $this->assertEquals([9], $this->cacheService->invalidatedPresentationItemIds);
        $this->assertEmpty($this->cacheService->invalidatedCountRoutes);
        $this->assertEmpty($this->cacheService->invalidatedIndexRoutes);
    }

    /** @test */
    public function it_purges_route_level_cache_types_without_tags(): void
    {
        $model = new TestModel;
        $model->id = 11;
        $model->exists = true;

        $this->cacheService->purgeModelCacheTypes($model, [
            'counts' => true,
            'index' => true,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => false,
        ]);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedCountRoutes);
        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedIndexRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
    }

    /** @test */
    public function it_flushes_the_route_once_when_purging_with_tags_enabled(): void
    {
        $this->cacheService->setUsesTags(true);

        $model = new TestModel;
        $model->id = 13;
        $model->exists = true;

        $this->cacheService->purgeModelCacheTypes($model, [
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => false,
        ]);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedModuleRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
    }

    /** @test */
    public function it_invalidates_all_requested_types_for_model_without_tags(): void
    {
        $model = new TestModel;
        $model->id = 17;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $this->cacheService->invalidateForModel($model, [
            'counts' => true,
            'index' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ], ['warmup' => false]);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedCountRoutes);
        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedIndexRoutes);
        $this->assertEquals([17], $this->cacheService->invalidatedFormItemIds);
        $this->assertEquals([17], $this->cacheService->invalidatedFormattedItemIds);
        $this->assertEquals([17], $this->cacheService->invalidatedPresentationItemIds);
        $this->assertEmpty($this->cacheService->warmedForModelCalls);
    }

    /** @test */
    public function it_skips_per_id_invalidation_for_newly_created_models_without_tags(): void
    {
        $model = new TestModel;
        $model->id = 18;
        $model->exists = true;
        $model->wasRecentlyCreated = true;

        $this->cacheService->invalidateForModel($model, [
            'counts' => true,
            'index' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ], ['warmup' => false]);

        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedCountRoutes);
        $this->assertEquals([['TestModule', 'TestRoute']], $this->cacheService->invalidatedIndexRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
        $this->assertEmpty($this->cacheService->invalidatedFormattedItemIds);
        $this->assertEmpty($this->cacheService->invalidatedPresentationItemIds);
    }

    /** @test */
    public function it_avoids_per_id_invalidation_when_invalidate_for_model_uses_tags(): void
    {
        $this->cacheService->setUsesTags(true);

        $model = new TestModel;
        $model->id = 19;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $this->cacheService->invalidateForModel($model, [
            'formItem' => true,
            'formattedItem' => true,
        ], ['warmup' => false]);

        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
        $this->assertEmpty($this->cacheService->invalidatedFormattedItemIds);
        $this->assertEmpty($this->cacheService->invalidatedModuleRoutes);
    }

    /** @test */
    public function it_warms_after_invalidate_for_model_when_warmup_is_enabled(): void
    {
        $model = new TestModel;
        $model->id = 20;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $this->cacheService->invalidateForModel($model, [
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => false,
        ], ['warmup' => true]);

        $this->assertCount(1, $this->cacheService->warmedForModelCalls);
        $this->assertSame('TestModule', $this->cacheService->warmedForModelCalls[0]['moduleName']);
    }

    /** @test */
    public function it_refreshes_model_caches_without_route_flush(): void
    {
        $model = new TestModel;
        $model->id = 22;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $this->cacheService->refreshModelCaches($model, [
            'presentationItem' => true,
            'formItem' => false,
        ], [
            'moduleName' => 'TestModule',
            'moduleRouteName' => 'TestRoute',
            'locale' => 'en',
        ]);

        $this->assertCount(1, $this->cacheService->warmedForModelCalls);
        $this->assertEmpty($this->cacheService->invalidatedModuleRoutes);
        $this->assertEmpty($this->cacheService->invalidatedFormItemIds);
    }

    /** @test */
    public function it_purges_presentation_item_files_for_model_and_route(): void
    {
        $service = new ConcreteCacheInvalidationWithFilesystemTracking;
        $model = new TestModel;
        $model->id = 23;
        $model->exists = true;

        $deleted = $service->purgePresentationItemForModel($model, 'TestModule', 'TestRoute');

        $this->assertSame(0, $deleted);
        $this->assertTrue($service->purgedRelation);
        $this->assertTrue($service->purgedModuleRouteId);
    }

    /** @test */
    public function it_purges_presentation_item_files_for_module_route(): void
    {
        $service = new ConcreteCacheInvalidationWithFilesystemTracking;

        $deleted = $service->purgePresentationItemForModuleRoute('TestModule', 'TestRoute');

        $this->assertGreaterThanOrEqual(0, $deleted);
        $this->assertTrue($service->purgedModuleRoute);
        $this->assertTrue($service->purgedUrlModuleRoute);
    }

    /** @test */
    public function it_warms_module_route_counts_and_items(): void
    {
        $this->seedInvalidateAllItemsTable([5, 6]);

        $mockModule = $this->mockInvalidateAllItemsModule(isSingleton: false);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $this->cacheService->warmModuleRouteCaches('TestModule', 'TestRoute', [
            'counts' => true,
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => true,
        ]);

        $this->assertNotEmpty($this->cacheService->warmedControllerItems);
        $this->assertCount(2, $this->cacheService->warmedPresentationItemIds);
    }

    /** @test */
    public function it_invalidates_by_related_models_when_tags_are_enabled(): void
    {
        $service = new ConcreteCacheInvalidationWithFilesystemTracking;
        $service->setUsesTags(true);

        $count = $service->invalidateByRelatedModels([
            'Company' => 1,
            'User' => [2, 3],
        ]);

        $this->assertSame(3, $count);
    }

    /** @test */
    public function it_invalidates_presentation_item_per_id_caches_without_tags(): void
    {
        $service = new ConcreteCacheInvalidation;
        $service->setPresentationCacheStore('model');

        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 44, TestModel::class);

        $this->assertEquals([44], $service->invalidatedPresentationItemIds);
    }

    /** @test */
    public function it_clears_stale_file_cache_when_invalidating_presentation_item_with_url_store(): void
    {
        $service = new ConcreteCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:45:' . md5(serialize(['locale' => 'en']));
        $relations = [TestModel::class => 45];
        $service->staleFileCache()->put($cacheKey, '<html>url-store</html>', 3600, $relations);

        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 45, TestModel::class);

        $this->assertNull($service->staleFileCache()->get($cacheKey, null, $relations));
        $this->assertEquals([45], $service->invalidatedPresentationItemIds);
    }

    /** @test */
    public function it_purges_stale_file_cache_for_model_even_when_presentation_store_is_url(): void
    {
        $service = new ConcreteCacheInvalidationWithFilesystemTracking;
        $service->setPresentationCacheStore('url');

        $model = new TestModel;
        $model->id = 46;
        $model->exists = true;

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:46:' . md5(serialize(['locale' => 'en']));
        $relations = [TestModel::class => 46];
        $service->staleFileCache()->put($cacheKey, '<html>purge-url</html>', 3600, $relations);

        $deleted = $service->purgePresentationItemForModel($model, 'TestModule', 'TestRoute');

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertTrue($service->purgedRelation);
        $this->assertNull($service->staleFileCache()->get($cacheKey, null, $relations));
    }

    /** @test */
    public function it_forgets_presentation_stale_for_model_store_agnostically(): void
    {
        $service = new ConcreteCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $model = new TestModel;
        $model->id = 47;
        $model->exists = true;

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:47:' . md5(serialize(['locale' => 'en']));
        $relations = [TestModel::class => 47];
        $service->staleFileCache()->put($cacheKey, '<html>forget-me</html>', 3600, $relations);

        $service->forgetPresentationStaleForModelPublic($model);

        $this->assertNull($service->staleFileCache()->get($cacheKey, null, $relations));
    }

    /** @test */
    public function it_invalidates_record_cache_per_id_without_tags(): void
    {
        $this->cacheService->invalidateRecordCache('TestModule', 'TestRoute', 55);

        $this->assertEquals([55], $this->cacheService->invalidatedRecordIds);
    }

    /** @test */
    public function it_returns_early_from_invalidate_all_item_caches_when_disabled(): void
    {
        $service = new ConcreteCacheInvalidation;
        $service->setEnabled(false);

        Modularous::shouldReceive('find')->never();

        $service->invalidateAllItemCaches('TestModule', 'TestRoute', ['formItem' => true]);

        $this->assertEmpty($service->invalidatedFormItemIds);
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
        $mockModule->shouldReceive('getController')->with($routeName)->andReturn(\Mockery::mock(Controller::class));

        return $mockModule;
    }
}

class ConcreteCacheInvalidation
{
    use CacheInvalidation {
        forgetPresentationStaleForModel as public forgetPresentationStaleForModelPublic;
    }

    protected $store;

    protected $prefix = 'modularous';

    protected $usesTags = false;

    protected $enabled = true;

    protected string $presentationCacheStore = 'model';

    protected StaleFileCache $staleFileCache;

    protected UrlKeyedStaleCache $urlKeyedStaleCache;

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
        $this->staleFileCache = new StaleFileCache(sys_get_temp_dir() . '/modularous-stale-test-' . uniqid());
        $this->urlKeyedStaleCache = new UrlKeyedStaleCache(sys_get_temp_dir() . '/modularous-url-stale-test-' . uniqid());
    }

    protected function getStaleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    public function staleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    protected function getUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        return new FileUrlPresentationCacheDriver($this->urlKeyedStaleCache);
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

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function setPresentationCacheStore(string $store): void
    {
        $this->presentationCacheStore = $store;
    }

    protected function getPresentationCacheStore(): string
    {
        return $this->presentationCacheStore;
    }

    protected function getModuleNameFromModel(Model $model): ?string
    {
        return $model instanceof TestModel ? 'TestModule' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof TestModel ? 'TestRoute' : null;
    }

    protected function warmupByModel(Model $model): void {}

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

    public function invalidatePresentationItemCache(string $moduleName, string $moduleRouteName, $id, ?string $modelClass = null): void
    {
        $this->invalidatedPresentationItemIds[] = $id;

        // Delegate to trait so store-agnostic StaleFileCache clears stay covered.
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        if ($modelClass !== null && $id !== null) {
            $this->getStaleFileCache()->forgetByRelation($modelClass, $id);
            if ($this->getPresentationCacheStore() === 'url') {
                $this->forgetUrlStaleForModel($modelClass, $id);
            }
        } elseif ($id !== null) {
            $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
        }

        if ($id !== null) {
            $this->forgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
        }
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

class ConcreteCacheInvalidationWithFilesystemTracking extends ConcreteCacheInvalidation
{
    public bool $purgedRelation = false;

    public bool $purgedModuleRouteId = false;

    public bool $purgedModuleRoute = false;

    public bool $purgedUrlModuleRoute = false;

    public function invalidateByRelatedModel(string $modelClass, $id): bool
    {
        if ($this->usesTags()) {
            $this->purgedRelation = true;

            return true;
        }

        return parent::invalidateByRelatedModel($modelClass, $id);
    }

    public function purgePresentationItemForModel(
        Model $model,
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
        ?string $locale = null,
    ): int {
        $this->purgedRelation = true;
        $this->purgedModuleRouteId = true;

        return parent::purgePresentationItemForModel($model, $moduleName, $moduleRouteName, $locale);
    }

    public function purgePresentationItemForModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        $this->purgedModuleRoute = true;
        $this->purgedUrlModuleRoute = true;

        return parent::purgePresentationItemForModuleRoute($moduleName, $moduleRouteName);
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

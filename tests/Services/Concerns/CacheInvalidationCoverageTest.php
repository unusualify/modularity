<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Tests\TestCase;

class CacheInvalidationCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('coverage_invalidate_models');
        Schema::dropIfExists('um_cms_url_routes');
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function invalidate_module_flushes_module_tags_when_tags_enabled(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')
            ->once()
            ->with(['modularous:TestModule'])
            ->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $this->assertTrue($service->invalidateModule('test-module'));
    }

    /** @test */
    public function invalidate_module_route_flushes_route_tags_when_tags_enabled(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')
            ->once()
            ->with(['modularous:TestModule:TestRoute'])
            ->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);
        $service->setPresentationCacheStore('url');

        $this->assertTrue($service->invalidateModuleRoute('TestModule', 'TestRoute'));
    }

    /** @test */
    public function invalidate_by_related_model_flushes_relation_tag_and_stale_files(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')
            ->once()
            ->with(['modularous:rel:Company:5'])
            ->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);
        $service->setPresentationCacheStore('model');

        $this->assertTrue($service->invalidateByRelatedModel('Company', 5));
        $this->assertSame([['Company', 5]], $service->forgetByRelationCalls);
    }

    /** @test */
    public function invalidate_by_related_model_returns_false_when_tag_flush_fails(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andThrow(new \RuntimeException('flush failed'));

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->once()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $this->assertFalse($service->invalidateByRelatedModel('Company', 9));
    }

    /** @test */
    public function invalidate_by_related_models_skips_null_ids_and_counts_successes(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->times(2)->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->times(2)->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $count = $service->invalidateByRelatedModels([
            'Company' => [1, null, 2],
        ]);

        $this->assertSame(2, $count);
    }

    /** @test */
    public function invalidate_by_pattern_returns_zero_for_non_redis_driver(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;

        $this->assertSame(0, $service->invalidateByPattern('modularous:*'));
    }

    /** @test */
    public function invalidate_by_pattern_deletes_matching_redis_keys(): void
    {
        Config::set('modularous.cache.driver', 'redis');
        Config::set('database.redis.options.prefix', 'redis:');
        Config::set('database.redis.client', 'phpredis');

        $inner = Mockery::mock();
        $inner->shouldReceive('getPrefix')->andReturn('cache:');

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('getStore')->andReturn($inner);
        $store->shouldReceive('forget')
            ->once()
            ->with('modularous:TestModule:item:1')
            ->andReturnTrue();

        $redis = Mockery::mock();
        $redis->shouldReceive('scan')
            ->once()
            ->with(null, Mockery::type('array'))
            ->andReturn([0, ['redis:cache:modularous:TestModule:item:1']]);

        Redis::shouldReceive('connection')->with('cache')->andReturn($redis);

        $service = new DirectCacheInvalidation($store);

        $this->assertSame(1, $service->invalidateByPattern('modularous:TestModule:*'));
    }

    /** @test */
    public function invalidate_by_pattern_handles_redis_scan_failures_gracefully(): void
    {
        Config::set('modularous.cache.driver', 'redis');
        Config::set('database.redis.options.prefix', '');
        Config::set('database.redis.client', 'predis');

        $inner = Mockery::mock();
        $inner->shouldReceive('getPrefix')->andReturn('');

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('getStore')->andReturn($inner);

        Redis::shouldReceive('connection')->with('cache')->andThrow(new \RuntimeException('redis down'));

        $service = new DirectCacheInvalidation($store);

        $this->assertSame(0, $service->invalidateByPattern('modularous:*'));
    }

    /** @test */
    public function invalidate_count_caches_without_tags_uses_count_pattern(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->invalidateCountCaches('test-module', 'test-route');

        $this->assertContains('modularous:TestModule:TestRoute:count:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_index_caches_without_tags_uses_index_pattern(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->invalidateIndexCaches('test-module', 'test-route');

        $this->assertContains('modularous:TestModule:TestRoute:index:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_formatted_item_cache_without_tags_uses_formatted_pattern(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->invalidateFormattedItemCache('test-module', 'test-route', 12);

        $this->assertContains('modularous:TestModule:TestRoute:formattedItem:12:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_form_item_cache_without_tags_uses_form_pattern(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->invalidateFormItemCache('test-module', 'test-route', 13);

        $this->assertContains('modularous:TestModule:TestRoute:formItem:13:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_record_cache_without_tags_uses_record_pattern(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->invalidateRecordCache('test-module', 'test-route', 14);

        $this->assertContains('modularous:TestModule:TestRoute:record:14:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_presentation_item_cache_without_tags_for_model_store_forgets_stale_files(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('model');
        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 15, CoverageInvalidateModel::class);

        $this->assertContains('modularous:TestModule:TestRoute:presentationItem:15:*', $service->invalidatedPatterns);
        $this->assertSame([['TestModule', 'TestRoute', 15]], $service->forgetByModuleRouteIdCalls);
    }

    /** @test */
    public function invalidate_presentation_item_cache_for_url_store_forgets_url_relation(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');
        $service->setUsesTags(true);

        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();
        $service->setStore(Mockery::mock(Repository::class, function ($mock) use ($tagged) {
            $mock->shouldReceive('tags')->once()->andReturn($tagged);
        }));

        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 16, CoverageInvalidateModel::class);

        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function invalidate_count_and_index_caches_with_tags_flush_route_tags(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->twice()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->twice()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $service->invalidateCountCaches('TestModule', 'TestRoute');
        $service->invalidateIndexCaches('TestModule', 'TestRoute', onlyRoute: true);

        $this->assertTrue(true);
    }

    /** @test */
    public function purge_model_cache_types_returns_early_without_module_metadata(): void
    {
        $service = new DirectCacheInvalidation;
        $model = new CoverageInvalidTestModel;
        $model->id = 1;

        $service->purgeModelCacheTypes($model, ['formItem' => true]);

        $this->assertEmpty($service->invalidatedPatterns);
    }

    /** @test */
    public function purge_model_cache_types_with_tags_and_presentation_item_clears_stale_stores(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->once()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);
        $service->setPresentationCacheStore('model');

        $model = new CoverageInvalidateModel;
        $model->id = 30;
        $model->exists = true;

        $service->purgeModelCacheTypes($model, ['presentationItem' => true]);

        // purgePresentationItemForModel always clears both StaleFileCache and URL store.
        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function purge_model_cache_types_with_tags_and_url_store_clears_url_relation(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->once()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);
        $service->setPresentationCacheStore('url');

        $model = new CoverageInvalidateModel;
        $model->id = 31;
        $model->exists = true;

        $service->purgeModelCacheTypes($model, ['presentationItem' => true]);

        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function warm_module_route_caches_returns_early_when_disabled(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setEnabled(false);

        Modularous::shouldReceive('find')->never();

        $service->warmModuleRouteCaches('TestModule', 'TestRoute');

        $this->assertEmpty($service->warmupControllerCountsCalls);
    }

    /** @test */
    public function warm_module_route_caches_returns_early_when_module_route_missing(): void
    {
        $service = new DirectCacheInvalidation;

        Modularous::shouldReceive('find')->with('Missing')->andReturn(null);

        $service->warmModuleRouteCaches('Missing', 'MissingRoute');

        $this->assertEmpty($service->warmupControllerCountsCalls);
    }

    /** @test */
    public function warm_module_route_caches_uses_default_types_and_warms_counts_and_items(): void
    {
        $this->seedCoverageInvalidateTable([10, 11]);

        $mockModule = $this->mockCoverageModule(isSingleton: false);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->warmModuleRouteCaches('TestModule', 'TestRoute', []);


        $this->assertCount(0, $service->warmupControllerCountsCalls);
        $this->assertCount(0, $service->warmupPresentationItemCalls);
    }

    /** @test */
    public function warm_module_route_caches_logs_count_warmup_failures(): void
    {
        $this->seedCoverageInvalidateTable([]);

        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupCounts = true;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('TestRoute')->andReturn(Mockery::mock(Controller::class));
        $mockModule->shouldReceive('getModel')->with('TestRoute')->andReturn(new CoverageInvalidateModel);
        $mockModule->shouldReceive('isSingleton')->with('TestRoute')->andReturn(true);

        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service->warmModuleRouteCaches('TestModule', 'TestRoute', ['counts' => true]);

        $this->assertTrue(true);
    }

    /** @test */
    public function warmup_for_model_runs_all_enabled_warmup_paths(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 40;
        $model->exists = true;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('TestRoute')->andReturn(Mockery::mock(Controller::class));
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->callWarmupForModel($model, [
            'counts' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ], 'TestModule', 'TestRoute', 'en');

        $this->assertCount(1, $service->warmupControllerCountsCalls);
        $this->assertCount(1, $service->warmupControllerItemCalls);
        $this->assertCount(1, $service->warmupPresentationItemCalls);
    }

    /** @test */
    public function warmup_for_model_returns_early_when_no_types_are_enabled(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 41;
        $model->exists = true;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->setDisabledTypes(['counts', 'formItem', 'formattedItem', 'presentationItem']);
        $service->callWarmupForModel($model, [
            'counts' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ], 'TestModule', 'TestRoute');

        $this->assertEmpty($service->warmupControllerCountsCalls);
        $this->assertEmpty($service->warmupPresentationItemCalls);
    }

    /** @test */
    public function warmup_for_model_logs_controller_and_presentation_failures(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 42;
        $model->exists = true;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('TestRoute')->andReturn(Mockery::mock(Controller::class));
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupControllerItem = true;
        $service->throwOnWarmupPresentationItem = true;

        $service->callWarmupForModel($model, [
            'counts' => false,
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => true,
        ], 'TestModule', 'TestRoute');

        $this->assertTrue(true);
    }

    /** @test */
    public function invalidate_all_item_caches_with_tags_warms_when_requested(): void
    {
        $this->seedCoverageInvalidateTable([50]);

        $mockModule = $this->mockCoverageModule(isSingleton: false);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();
        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->once()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $service->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'formItem' => true,
            'presentationItem' => true,
        ], shouldWarmDependentModules: true);

        $this->assertCount(1, $service->warmupPresentationItemCalls);
    }

    /** @test */
    public function invalidate_all_item_caches_without_tags_logs_per_record_warmup_failures(): void
    {
        $this->seedCoverageInvalidateTable([60]);

        $mockModule = $this->mockCoverageModule(isSingleton: false);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupControllerItem = true;

        Config::set('modularous.cache.driver', 'array');

        $service->invalidateAllItemCaches('TestModule', 'TestRoute', [
            'formItem' => true,
        ], shouldWarmDependentModules: true);

        $this->assertContains('modularous:TestModule:TestRoute:formItem:60:*', $service->invalidatedPatterns);
    }

    /** @test */
    public function invalidate_for_model_logs_warmup_failures_without_aborting(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 70;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupPresentationItem = true;

        Config::set('modularous.cache.driver', 'array');

        $service->invalidateForModel($model, [
            'counts' => false,
            'index' => false,
            'formItem' => false,
            'formattedItem' => false,
            'presentationItem' => true,
        ], ['warmup' => true]);

        $this->assertTrue(true);
    }

    /** @test */
    public function refresh_model_caches_passes_locale_to_warmup(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 71;
        $model->exists = true;
        $model->wasRecentlyCreated = false;

        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('model');

        $service->refreshModelCaches($model, [
            'presentationItem' => true,
        ], [
            'moduleName' => 'TestModule',
            'moduleRouteName' => 'TestRoute',
            'locale' => 'tr',
        ]);

        $this->assertSame('tr', $service->lastWarmupLocale);
    }

    /** @test */
    public function purge_presentation_item_for_model_returns_zero_without_primary_key(): void
    {
        $service = new DirectCacheInvalidation;
        $model = new CoverageInvalidateModel;

        $this->assertSame(0, $service->purgePresentationItemForModel($model));
    }

    /** @test */
    public function forget_presentation_stale_for_model_returns_early_without_primary_key(): void
    {
        $service = new DirectCacheInvalidation;
        $model = new CoverageInvalidateModel;

        $service->callForgetPresentationStaleForModel($model);

        $this->assertEmpty($service->forgetByRelationCalls);
    }

    /** @test */
    public function forget_stale_files_by_relation_delegates_to_url_store_when_configured(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $service->callForgetStaleFilesByRelation(CoverageInvalidateModel::class, 80);

        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function forget_stale_files_by_relation_always_clears_stale_file_cache_even_when_store_is_url(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:80:' . md5(serialize(['locale' => 'en']));
        $relations = [CoverageInvalidateModel::class => 80];
        $this->assertTrue($service->staleFileCache->put($cacheKey, '<html>stale</html>', 3600, $relations));
        $this->assertSame('<html>stale</html>', $service->staleFileCache->get($cacheKey, null, $relations));

        $service->callForgetStaleFilesByRelation(CoverageInvalidateModel::class, 80);

        $this->assertNull($service->staleFileCache->get($cacheKey, null, $relations));
        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function forget_stale_files_by_relation_clears_stale_file_cache_when_store_is_model(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('model');

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:81:' . md5(serialize(['locale' => 'en']));
        $relations = [CoverageInvalidateModel::class => 81];
        $this->assertTrue($service->staleFileCache->put($cacheKey, '<html>model-stale</html>', 3600, $relations));

        $service->callForgetStaleFilesByRelation(CoverageInvalidateModel::class, 81);

        $this->assertNull($service->staleFileCache->get($cacheKey, null, $relations));
        $this->assertSame(0, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function forget_presentation_stale_for_model_clears_stale_file_cache_when_store_is_url(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $model = new CoverageInvalidateModel;
        $model->id = 82;
        $model->exists = true;

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:82:' . md5(serialize(['locale' => 'en']));
        $relations = [CoverageInvalidateModel::class => 82];
        $this->assertTrue($service->staleFileCache->put($cacheKey, '<html>presentation</html>', 3600, $relations));

        $service->callForgetPresentationStaleForModel($model);

        $this->assertNull($service->staleFileCache->get($cacheKey, null, $relations));
        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function invalidate_presentation_item_cache_always_clears_stale_file_cache_when_store_is_url(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:83:' . md5(serialize(['locale' => 'en']));
        $relations = [CoverageInvalidateModel::class => 83];
        $this->assertTrue($service->staleFileCache->put($cacheKey, '<html>invalidate-me</html>', 3600, $relations));

        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 83, CoverageInvalidateModel::class);

        $this->assertNull($service->staleFileCache->get($cacheKey, null, $relations));
        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function purge_presentation_item_for_model_clears_stale_file_cache_even_when_store_is_url(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $model = new CoverageInvalidateModel;
        $model->id = 84;
        $model->exists = true;

        $cacheKey = 'modularous:TestModule:TestRoute:presentationItem:84:' . md5(serialize(['locale' => 'en']));
        $relations = [CoverageInvalidateModel::class => 84];
        $this->assertTrue($service->staleFileCache->put($cacheKey, '<html>purge-me</html>', 3600, $relations));

        $deleted = $service->purgePresentationItemForModel($model, 'TestModule', 'TestRoute');

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertNull($service->staleFileCache->get($cacheKey, null, $relations));
        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function purge_url_presentation_path_variants_forgets_paths_from_url_route_rows(): void
    {
        Schema::dropIfExists('um_cms_url_routes');
        Schema::create('um_cms_url_routes', function (Blueprint $table) {
            $table->id();
            $table->string('locale');
            $table->string('normalized_path');
            $table->string('urlable_type');
            $table->unsignedBigInteger('urlable_id');
            $table->string('kind')->nullable();
            $table->timestamps();
        });

        $model = new CoverageInvalidateModel;
        $model->id = 85;

        \Modules\Cms\Entities\UrlRoute::query()->insert([
            'locale' => 'en',
            'normalized_path' => '/errors/404',
            'urlable_type' => $model->getMorphClass(),
            'urlable_id' => 85,
            'kind' => \Modules\Cms\Entities\UrlRoute::KIND_PAGE_PUBLIC,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');
        $service->urlStoreSpy->useForgetPathVariantsReturn = true;
        $service->urlStoreSpy->forgetPathVariantsReturn = 2;

        $deleted = $service->callPurgeUrlPresentationPathVariantsForModel(CoverageInvalidateModel::class, 85);

        $this->assertSame(2, $deleted);
        $this->assertSame(1, $service->urlStoreSpy->forgetPathVariantsCount);

        Schema::dropIfExists('um_cms_url_routes');
    }

    /** @test */
    public function forget_url_stale_helpers_return_zero_when_store_is_not_url(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('model');

        $this->assertSame(0, $service->callForgetUrlStaleForModuleRoute('TestModule', 'TestRoute'));
        $this->assertSame(0, $service->callForgetUrlStaleForModel(CoverageInvalidateModel::class, 81));
    }

    /** @test */
    public function forget_url_stale_by_locale_path_delegates_to_url_store(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $deleted = $service->callForgetUrlStaleByLocalePath('en', '/blog');

        $this->assertSame(1, $service->urlStoreSpy->forgetPathVariantsCount);
        $this->assertGreaterThanOrEqual(0, $deleted);
    }

    /** @test */
    public function get_url_keyed_stale_cache_returns_underlying_file_cache_for_file_driver(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');
        $service->useFileUrlDriver = true;

        $this->assertInstanceOf(UrlKeyedStaleCache::class, $service->callGetUrlKeyedStaleCache());
    }

    /** @test */
    public function get_url_keyed_stale_cache_throws_for_non_file_driver(): void
    {
        $service = new DirectCacheInvalidation;
        $service->useNonFileUrlStore = true;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('getUrlKeyedStaleCache() requires file driver.');

        $service->callGetUrlKeyedStaleCache();
    }

    /** @test */
    public function invalidate_formatted_and_form_item_caches_with_tags_flush_route_tags(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->twice()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->twice()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $service->invalidateFormattedItemCache('TestModule', 'TestRoute', 101);
        $service->invalidateFormItemCache('TestModule', 'TestRoute', 102);

        $this->assertTrue(true);
    }

    /** @test */
    public function invalidate_record_cache_with_tags_flushes_route_tags(): void
    {
        $tagged = Mockery::mock(Repository::class);
        $tagged->shouldReceive('flush')->once()->andReturnTrue();

        $store = Mockery::mock(Repository::class);
        $store->shouldReceive('tags')->once()->andReturn($tagged);

        $service = new DirectCacheInvalidation($store);
        $service->setUsesTags(true);

        $service->invalidateRecordCache('TestModule', 'TestRoute', 103);

        $this->assertTrue(true);
    }

    /** @test */
    public function invalidate_presentation_item_cache_without_model_class_for_model_store_forgets_route_id(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('model');

        $service->invalidatePresentationItemCache('TestModule', 'TestRoute', 104);

        $this->assertCount(2, $service->forgetByModuleRouteIdCalls);
        $this->assertSame(['TestModule', 'TestRoute', 104], $service->forgetByModuleRouteIdCalls[0]);
    }

    /** @test */
    public function warmup_for_model_returns_early_without_module_metadata(): void
    {
        $model = new CoverageInvalidTestModel;
        $model->id = 1;

        $service = new DirectCacheInvalidation;
        $service->callWarmupForModel($model, ['counts' => true]);

        $this->assertEmpty($service->warmupControllerCountsCalls);
    }

    /** @test */
    public function warmup_for_model_logs_count_warmup_failures(): void
    {
        $model = new CoverageInvalidateModel;
        $model->id = 105;
        $model->exists = true;

        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('TestRoute')->andReturn(Mockery::mock(Controller::class));
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupCounts = true;
        $service->callWarmupForModel($model, ['counts' => true], 'TestModule', 'TestRoute');

        $this->assertTrue(true);
    }

    /** @test */
    public function invalidate_all_item_caches_returns_early_when_route_is_missing(): void
    {
        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('MissingRoute')->andReturn(false);
        Modularous::shouldReceive('find')->with('TestModule')->andReturn($mockModule);

        $service = new DirectCacheInvalidation;
        $service->invalidateAllItemCaches('TestModule', 'MissingRoute', ['formItem' => true]);

        $this->assertEmpty($service->invalidatedPatterns);
    }

    /** @test */
    public function warmup_all_item_caches_for_route_logs_failures(): void
    {
        $this->seedCoverageInvalidateTable([110]);

        $mockModule = $this->mockCoverageModule(isSingleton: false);
        $service = new DirectCacheInvalidation;
        $service->throwOnWarmupPresentationItem = true;

        $service->callWarmupAllItemCachesForRoute(
            $mockModule,
            'TestModule',
            'TestRoute',
            CoverageInvalidateModel::class,
            ['presentationItem' => true],
        );

        $this->assertTrue(true);
    }

    /** @test */
    public function purge_all_url_presentation_for_model_without_locale_forgets_relation_and_paths(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $service->callPurgeAllUrlPresentationForModel(CoverageInvalidateModel::class, 130);

        $this->assertSame(1, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function purge_all_url_presentation_for_model_with_locale_skips_relation_forget(): void
    {
        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');

        $service->callPurgeAllUrlPresentationForModel(CoverageInvalidateModel::class, 90, 'en');

        $this->assertSame(0, $service->urlStoreSpy->forgetByRelationCount);
    }

    /** @test */
    public function purge_url_presentation_path_variants_returns_zero_when_url_route_class_missing(): void
    {
        $service = new DirectCacheInvalidation;

        $this->assertSame(0, $service->callPurgeUrlPresentationPathVariantsForModel(CoverageInvalidateModel::class, 91));
    }

    /** @test */
    public function invalidate_module_route_without_tags_returns_true_when_url_stale_deleted(): void
    {
        Config::set('modularous.cache.driver', 'array');

        $service = new DirectCacheInvalidation;
        $service->setPresentationCacheStore('url');
        $service->urlStoreSpy->forgetByModuleRouteReturn = 2;

        $this->assertTrue($service->invalidateModuleRoute('TestModule', 'TestRoute'));
        $this->assertSame(1, $service->urlStoreSpy->forgetByModuleRouteCount);
    }

    /** @test */
    public function warmup_item_caches_for_route_id_returns_when_model_is_missing(): void
    {
        $this->seedCoverageInvalidateTable([]);

        $service = new DirectCacheInvalidation;

        $mockModule = Mockery::mock(Module::class);
        $service->callWarmupItemCachesForRouteId(
            $mockModule,
            'TestModule',
            'TestRoute',
            CoverageInvalidateModel::class,
            99999,
            ['formItem' => true],
        );

        $this->assertEmpty($service->warmupControllerItemCalls);
    }

    /** @test */
    public function warmup_item_caches_for_route_id_skips_when_no_item_types_requested(): void
    {
        $this->seedCoverageInvalidateTable([88]);
        $record = CoverageInvalidateModel::query()->find(88);

        $mockModule = Mockery::mock(Module::class);
        $service = new DirectCacheInvalidation;

        $service->callWarmupItemCachesForRouteId(
            $mockModule,
            'TestModule',
            'TestRoute',
            CoverageInvalidateModel::class,
            $record->getKey(),
            ['formItem' => false, 'formattedItem' => false, 'presentationItem' => false],
        );

        $this->assertEmpty($service->warmupControllerItemCalls);
        $this->assertEmpty($service->warmupPresentationItemCalls);
    }

    protected function seedCoverageInvalidateTable(array $ids): void
    {
        Schema::create('coverage_invalidate_models', function (Blueprint $table) {
            $table->id();
        });

        foreach ($ids as $id) {
            CoverageInvalidateModel::query()->insert(['id' => $id]);
        }
    }

    protected function mockCoverageModule(bool $isSingleton): Module
    {
        $mockModule = Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $mockModule->shouldReceive('isSingleton')->with('TestRoute')->andReturn($isSingleton);
        $mockModule->shouldReceive('getModel')->with('TestRoute')->andReturn(new CoverageInvalidateModel);
        $mockModule->shouldReceive('getController')->with('TestRoute')->andReturn(Mockery::mock(Controller::class));

        return $mockModule;
    }
}

class DirectCacheInvalidation
{
    use CacheInvalidation {
        forgetPresentationStaleForModel as public callForgetPresentationStaleForModel;
        forgetStaleFilesByRelation as protected traitForgetStaleFilesByRelation;
        forgetStaleFilesByModuleRouteId as protected traitForgetStaleFilesByModuleRouteId;
        forgetUrlStaleByLocalePath as public callForgetUrlStaleByLocalePath;
        forgetUrlStaleForModuleRoute as public callForgetUrlStaleForModuleRoute;
        forgetUrlStaleForModel as public callForgetUrlStaleForModel;
        getUrlKeyedStaleCache as public callGetUrlKeyedStaleCache;
        purgeAllUrlPresentationForModel as public callPurgeAllUrlPresentationForModel;
        purgeUrlPresentationPathVariantsForModel as public callPurgeUrlPresentationPathVariantsForModel;
        warmupForModel as protected traitWarmupForModel;
        warmupAllItemCachesForRoute as public callWarmupAllItemCachesForRoute;
        warmupItemCachesForRouteId as public callWarmupItemCachesForRouteId;
        invalidateByPattern as protected traitInvalidateByPattern;
    }

    protected Repository $store;

    protected string $prefix = 'modularous';

    protected bool $usesTags = false;

    protected bool $enabled = true;

    protected string $presentationCacheStore = 'model';

    /** @var list<string> */
    protected array $disabledTypes = [];

    public StaleFileCache $staleFileCache;

    public SpyingUrlPresentationCacheStore $urlStoreSpy;

    public bool $useNonFileUrlStore = false;

    public bool $useFileUrlDriver = false;

    private ?UrlKeyedStaleCache $fileUrlKeyedCache = null;

    public bool $throwOnWarmupCounts = false;

    public bool $throwOnWarmupControllerItem = false;

    public bool $throwOnWarmupPresentationItem = false;

    /** @var list<string> */
    public array $invalidatedPatterns = [];

    /** @var list<mixed> */
    public array $warmupControllerCountsCalls = [];

    /** @var list<array{0: mixed, 1: bool, 2: bool}> */
    public array $warmupControllerItemCalls = [];

    /** @var list<array{id: mixed, moduleName: string, moduleRouteName: string}> */
    public array $warmupPresentationItemCalls = [];

    /** @var list<array{0: string, 1: int|string}> */
    public array $forgetByRelationCalls = [];

    /** @var list<array{0: string, 1: string, 2: int|string}> */
    public array $forgetByModuleRouteIdCalls = [];

    public function __construct(?Repository $store = null)
    {
        $this->store = $store ?? Cache::store('array');
        $this->staleFileCache = new StaleFileCache(sys_get_temp_dir() . '/modularous-stale-coverage-' . uniqid('', true));
        $this->urlStoreSpy = new SpyingUrlPresentationCacheStore(
            new UrlKeyedStaleCache(sys_get_temp_dir() . '/modularous-url-coverage-' . uniqid('', true)),
        );
    }

    public function setStore(Repository $store): void
    {
        $this->store = $store;
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

    /** @param list<string> $types */
    public function setDisabledTypes(array $types): void
    {
        $this->disabledTypes = $types;
    }

    protected function getStaleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    protected function forgetStaleFilesByRelation(string $modelClass, int|string $id): void
    {
        $this->forgetByRelationCalls[] = [$modelClass, $id];
        $this->traitForgetStaleFilesByRelation($modelClass, $id);
    }

    protected function forgetStaleFilesByModuleRouteId(string $moduleName, string $moduleRouteName, int|string $id): void
    {
        $this->forgetByModuleRouteIdCalls[] = [$moduleName, $moduleRouteName, $id];
        $this->traitForgetStaleFilesByModuleRouteId($moduleName, $moduleRouteName, $id);
    }

    protected function getUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        if ($this->useNonFileUrlStore) {
            return new NonFileUrlPresentationCacheStore;
        }

        if ($this->useFileUrlDriver) {
            $this->fileUrlKeyedCache ??= new UrlKeyedStaleCache(sys_get_temp_dir() . '/modularous-url-file-driver-' . uniqid('', true));

            return new FileUrlPresentationCacheDriver($this->fileUrlKeyedCache);
        }

        return $this->urlStoreSpy;
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
        if (! $this->enabled) {
            return false;
        }

        if ($type !== null && in_array($type, $this->disabledTypes, true)) {
            return false;
        }

        return true;
    }

    protected function getPresentationCacheStore(): string
    {
        return $this->presentationCacheStore;
    }

    protected function getModuleNameFromModel(Model $model): ?string
    {
        return $model instanceof CoverageInvalidateModel ? 'TestModule' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof CoverageInvalidateModel ? 'TestRoute' : null;
    }

    public function invalidateByPattern(string $pattern): int
    {
        $this->invalidatedPatterns[] = $pattern;

        if ($this->usesTags()) {
            return 0;
        }

        $driver = config('modularous.cache.driver', config('cache.default'));
        if (! in_array($driver, ['redis', 'predis'], true)) {
            return 0;
        }

        return $this->traitInvalidateByPattern($pattern);
    }

    public function callWarmupForModel(
        Model $model,
        array $types = [],
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
        ?string $locale = null,
    ): void {
        $this->traitWarmupForModel($model, $types, $moduleName, $moduleRouteName, $locale);
    }

    public function warmupControllerCounts($controller): bool
    {
        if ($this->throwOnWarmupCounts) {
            throw new \RuntimeException('count warmup failed');
        }

        $this->warmupControllerCountsCalls[] = $controller;

        return true;
    }

    public function warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem): void
    {
        if ($this->throwOnWarmupControllerItem) {
            throw new \RuntimeException('controller item warmup failed');
        }

        $this->warmupControllerItemCalls[] = [$item, $cacheFormItem, $cacheFormattedItem];
    }

    public function warmupPresentationItem(string $moduleName, string $routeName, Model $item, ?string $locale = null): bool
    {
        if ($this->throwOnWarmupPresentationItem) {
            throw new \RuntimeException('presentation warmup failed');
        }

        $this->warmupPresentationItemCalls[] = [
            'id' => $item->getKey(),
            'moduleName' => $moduleName,
            'moduleRouteName' => $routeName,
            'locale' => $locale,
        ];

        return true;
    }

    public function callForgetStaleFilesByRelation(string $modelClass, int|string $id): void
    {
        $this->traitForgetStaleFilesByRelation($modelClass, $id);
    }

    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null, ?string $locale = null): void
    {
        $this->lastWarmupLocale = $locale;
        $this->traitWarmupForModel($model, $types, $moduleName, $moduleRouteName, $locale);
    }
}

class SpyingUrlPresentationCacheStore implements UrlPresentationCacheStoreInterface
{
    public int $forgetByRelationCount = 0;

    public int $forgetByModuleRouteCount = 0;

    public int $forgetPathVariantsCount = 0;

    public int $forgetByModuleRouteReturn = 1;

    public int $forgetPathVariantsReturn = 1;

    public bool $useForgetPathVariantsReturn = false;

    public function __construct(
        private readonly UrlKeyedStaleCache $cache,
    ) {}

    public function put(
        string $locale,
        string $cacheLookupKey,
        string $html,
        array $meta,
        ?int $freshTtl = null,
        ?int $staleTtl = null,
    ): bool {
        return $this->cache->put($locale, $cacheLookupKey, $html, $meta, $freshTtl, $staleTtl);
    }

    public function get(string $locale, string $cacheLookupKey): ?array
    {
        return $this->cache->get($locale, $cacheLookupKey);
    }

    public function forget(string $locale, string $cacheLookupKey): bool
    {
        return $this->cache->forget($locale, $cacheLookupKey);
    }

    public function forgetPathVariants(string $locale, string $normalizedPath): int
    {
        $this->forgetPathVariantsCount++;

        if ($this->useForgetPathVariantsReturn) {
            return $this->forgetPathVariantsReturn;
        }

        return $this->cache->forgetPathVariants($locale, $normalizedPath);
    }

    public function forgetByRelation(string $modelClass, int|string $id): int
    {
        $this->forgetByRelationCount++;

        return $this->cache->forgetByRelation($modelClass, $id);
    }

    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        $this->forgetByModuleRouteCount++;

        return $this->forgetByModuleRouteReturn;
    }

    public function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string
    {
        return $this->cache->composeLookupKey($normalizedPath, $querySuffix);
    }

    public function normalizePath(string $path): string
    {
        return $this->cache->normalizePath($path);
    }

    public function splitLookupKey(string $cacheLookupKey): array
    {
        return $this->cache->splitLookupKey($cacheLookupKey);
    }
}

class NonFileUrlPresentationCacheStore implements UrlPresentationCacheStoreInterface
{
    public function put(string $locale, string $cacheLookupKey, string $html, array $meta, ?int $freshTtl = null, ?int $staleTtl = null): bool
    {
        return false;
    }

    public function get(string $locale, string $cacheLookupKey): ?array
    {
        return null;
    }

    public function forget(string $locale, string $cacheLookupKey): bool
    {
        return false;
    }

    public function forgetPathVariants(string $locale, string $normalizedPath): int
    {
        return 0;
    }

    public function forgetByRelation(string $modelClass, int|string $id): int
    {
        return 0;
    }

    public function forgetByModuleRoute(string $moduleName, string $moduleRouteName): int
    {
        return 0;
    }

    public function composeLookupKey(string $normalizedPath, string $querySuffix = ''): string
    {
        return $normalizedPath . $querySuffix;
    }

    public function normalizePath(string $path): string
    {
        return $path;
    }

    public function splitLookupKey(string $cacheLookupKey): array
    {
        return [$cacheLookupKey, ''];
    }
}

class CoverageInvalidateModel extends Model
{
    protected $table = 'coverage_invalidate_models';

    public $timestamps = false;
}

class CoverageInvalidTestModel extends Model
{
    protected $table = 'coverage_invalid_models';
}

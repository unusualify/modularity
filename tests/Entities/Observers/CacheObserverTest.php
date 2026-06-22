<?php

namespace Unusualify\Modularous\Tests\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Entities\Observers\CacheObserver;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Facades\RelationshipGraph;
use Unusualify\Modularous\Tests\TestCase;

class CacheObserverTest extends TestCase
{
    protected CacheObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.enabled', false);

        $this->observer = new CacheObserver;
    }

    public function test_created_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->created($model);

        $this->assertTrue(true);
    }

    public function test_updated_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->updated($model);

        $this->assertTrue(true);
    }

    public function test_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->deleted($model);

        $this->assertTrue(true);
    }

    public function test_restored_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->restored($model);

        $this->assertTrue(true);
    }

    public function test_force_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->forceDeleted($model);

        $this->assertTrue(true);
    }

    public function test_created_invalidates_model_cache_when_enabled()
    {
        // Covers CacheObserver lines 37-39: when caching is enabled for the
        // model's module, created() must invalidate the model's cache.
        $model = $this->createTestModel();

        // getCacheDependents() (used by shouldInvalidate) touches the graph.
        RelationshipGraph::shouldReceive('getAffectedModuleRoutes')->andReturn([]);
        RelationshipGraph::shouldReceive('getAffectedModuleRoutesByTable')->andReturn([]);

        // Enabled both globally (no args) and for the module/route (two args).
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        // Granular invalidation "succeeds" so the dependent-module fallback is skipped.
        ModularousCache::shouldReceive('invalidateByRelatedModel')->andReturn(true);

        // The assertion: line 38 runs exactly once.
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $this->observer->created($model);
    }

    public function test_updated_invalidates_refreshed_model_cache_when_enabled()
    {
        // Covers CacheObserver lines 59-63: when caching is enabled for the
        // model's module, updated() clones + refreshes the model and invalidates
        // the clone's cache. refresh() is a no-op here to avoid a DB round-trip.
        $model = new class extends Model
        {
            protected $table = 'test_models';

            public function getKey()
            {
                return 1;
            }

            public function refresh()
            {
                return $this;
            }
        };
        $model->setRawAttributes(['id' => 1]);

        // getCacheDependents() (used by shouldInvalidate) touches the graph.
        RelationshipGraph::shouldReceive('getAffectedModuleRoutes')->andReturn([]);
        RelationshipGraph::shouldReceive('getAffectedModuleRoutesByTable')->andReturn([]);

        // Enabled both globally (no args) and for the module/route (two args).
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        // Granular invalidation "succeeds" so the dependent-module fallback is skipped.
        ModularousCache::shouldReceive('invalidateByRelatedModel')->andReturn(true);

        // The assertion: line 62 runs exactly once (on the refreshed clone).
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $this->observer->updated($model);
    }

    public function test_deleted_invalidates_model_cache_when_enabled()
    {
        // Covers CacheObserver lines 84-86: when caching is enabled for the
        // model's module, deleted() must invalidate the model's cache.
        $model = $this->createTestModel();

        // getCacheDependents() (used by shouldInvalidate) touches the graph.
        RelationshipGraph::shouldReceive('getAffectedModuleRoutes')->andReturn([]);
        RelationshipGraph::shouldReceive('getAffectedModuleRoutesByTable')->andReturn([]);

        // Enabled both globally (no args) and for the module/route (two args).
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        // Granular invalidation "succeeds" so the dependent-module fallback is skipped.
        ModularousCache::shouldReceive('invalidateByRelatedModel')->andReturn(true);

        // The assertion: line 85 runs exactly once.
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $this->observer->deleted($model);
    }

    public function test_restored_invalidates_model_cache_when_enabled()
    {
        // Covers CacheObserver lines 106-108: when caching is enabled for the
        // model's module, restored() must invalidate the model's cache.
        $model = $this->createTestModel();

        // getCacheDependents() (used by shouldInvalidate) touches the graph.
        RelationshipGraph::shouldReceive('getAffectedModuleRoutes')->andReturn([]);
        RelationshipGraph::shouldReceive('getAffectedModuleRoutesByTable')->andReturn([]);

        // Enabled both globally (no args) and for the module/route (two args).
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        // Granular invalidation "succeeds" so the dependent-module fallback is skipped.
        ModularousCache::shouldReceive('invalidateByRelatedModel')->andReturn(true);

        // The assertion: line 107 runs exactly once.
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $this->observer->restored($model);
    }

    public function test_force_deleted_invalidates_model_cache_when_enabled()
    {
        // Covers CacheObserver lines 125-127: when caching is enabled for the
        // model's module, forceDeleted() must invalidate the model's cache.
        $model = $this->createTestModel();

        // getCacheDependents() (used by shouldInvalidate) touches the graph.
        RelationshipGraph::shouldReceive('getAffectedModuleRoutes')->andReturn([]);
        RelationshipGraph::shouldReceive('getAffectedModuleRoutesByTable')->andReturn([]);

        // Enabled both globally (no args) and for the module/route (two args).
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        // Granular invalidation "succeeds" so the dependent-module fallback is skipped.
        ModularousCache::shouldReceive('invalidateByRelatedModel')->andReturn(true);

        // The assertion: line 126 runs exactly once.
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $this->observer->forceDeleted($model);
    }

    private function createTestModel(): Model
    {
        $model = new class extends Model
        {
            protected $table = 'test_models';

            public function getKey()
            {
                return 1;
            }
        };
        $model->setRawAttributes(['id' => 1]);

        return $model;
    }
}

<?php

namespace Unusualify\Modularous\Tests\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Entities\Observers\CacheObserver;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Tests\TestCase;

class CacheObserverInvalidationOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.observer.queue', false);
    }

    protected function mockAutoInvalidationEnabled(): void
    {
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
    }

    /** @test */
    public function it_flushes_relation_tags_before_self_invalidation_warmup_on_update(): void
    {
        $this->mockAutoInvalidationEnabled();
        $model = new CacheObserverOrderStub;
        $model->exists = true;
        $model->setRawAttributes(['id' => 44]);

        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('invalidateByRelatedModel')
            ->once()
            ->ordered()
            ->with(CacheObserverOrderStub::class, 44);
        ModularousCache::shouldReceive('invalidateForModel')
            ->once()
            ->ordered()
            ->withArgs(function ($passedModel) use ($model) {
                return $passedModel instanceof CacheObserverOrderStub
                    && $passedModel->getKey() === $model->getKey();
            });

        $observer = new CacheObserver;
        $observer->updated($model);
    }
}

class CacheObserverOrderStub extends Model
{
    protected $table = 'cache_observer_order_stubs';

    public $timestamps = false;

    public function getModuleName(): string
    {
        return 'BusinessPackage';
    }

    public function getRouteName(): string
    {
        return 'PackageCountry';
    }

    public function getCacheDependents(): array
    {
        return [];
    }

    public function refresh(): static
    {
        return $this;
    }
}

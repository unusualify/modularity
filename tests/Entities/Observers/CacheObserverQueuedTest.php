<?php

namespace Unusualify\Modularous\Tests\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Unusualify\Modularous\Entities\Observers\CacheObserver;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\InvalidateDependentCachesJob;
use Unusualify\Modularous\Jobs\Cache\InvalidateModelCacheJob;
use Unusualify\Modularous\Tests\TestCase;

class CacheObserverQueuedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.observer.queue', true);
        Config::set('modularous.cache.queue.connection', 'redis');
        Config::set('queue.default', 'redis');
    }

    /** @test */
    public function it_dispatches_cache_jobs_when_async_queue_is_enabled(): void
    {
        Queue::fake();

        $model = new CacheObserverQueueStub;
        $model->exists = true;
        $model->setRawAttributes(['id' => 12]);

        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('invalidateByRelatedModel')->never();
        ModularousCache::shouldReceive('invalidateForModel')->never();

        $observer = new CacheObserver;
        $observer->updated($model);

        Queue::assertPushed(InvalidateModelCacheJob::class, function (InvalidateModelCacheJob $job) use ($model) {
            return $job->model->getKey() === $model->getKey()
                && $job->event === 'updated';
        });

        Queue::assertPushed(InvalidateDependentCachesJob::class, function (InvalidateDependentCachesJob $job) use ($model) {
            return $job->model->getKey() === $model->getKey();
        });
    }

    /** @test */
    public function it_runs_sync_path_when_observer_queue_is_disabled(): void
    {
        Config::set('modularous.cache.observer.queue', false);

        $model = new CacheObserverQueueStub;
        $model->exists = true;
        $model->setRawAttributes(['id' => 15]);

        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('invalidateByRelatedModel')
            ->once()
            ->with(CacheObserverQueueStub::class, 15);
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
        ModularousCache::shouldReceive('invalidateForModel')->once();

        $observer = new CacheObserver;
        $observer->updated($model);
    }
}

class CacheObserverQueueStub extends Model
{
    protected $table = 'cache_observer_queue_stubs';

    public $timestamps = false;

    public function getModuleName(): string
    {
        return 'BusinessPackage';
    }

    public function getRouteName(): string
    {
        return 'PackageCountry';
    }

    public function refresh(): static
    {
        return $this;
    }
}

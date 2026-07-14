<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use TestModules\TestModule\Entities\Item;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmModuleRouteCachesJob;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestCase;

class WarmModuleRouteCachesJobTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();
        $app['config']->set('modules.paths.modules', $fixturesPath);
        $app['config']->set('modules.scan.paths', [$fixturesPath]);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.paths.generator.model', [
            'path' => 'Entities',
            'namespace' => 'Entities',
            'generate' => false,
        ]);

        Modularous::boot();
    }

    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();
        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);

        Schema::dropIfExists('test_module_items');
        Schema::create('test_module_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.queue.name', 'modularous-cache-test');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.formItem', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.counts', true);
        Config::set('modularous.cache.observer.queue', true);
        Config::set('queue.default', 'sync');

        $this->app->forgetInstance('modularous.cache');

        Item::query()->create(['name' => 'Alpha']);
        Item::query()->create(['name' => 'Beta']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_module_items');

        parent::tearDown();
    }

    /** @test */
    public function it_returns_early_when_route_caching_is_disabled(): void
    {
        ModularousCache::shouldReceive('isEnabled')
            ->with('TestModule', 'Item')
            ->andReturn(false);
        ModularousCache::shouldReceive('warmupModuleRouteCacheCounts')->never();
        ModularousCache::shouldReceive('warmupModelCaches')->never();
        Bus::fake();

        (new WarmModuleRouteCachesJob('TestModule', 'Item'))->handle();

        Bus::assertNothingDispatched();
    }

    /** @test */
    public function it_warms_counts_before_iterating_records(): void
    {
        ModularousCache::partialMock()
            ->shouldReceive('isEnabled')
            ->andReturn(true)
            ->shouldReceive('warmupModuleRouteCacheCounts')
            ->once()
            ->with('TestModule', 'Item')
            ->shouldReceive('warmupModelCaches')
            ->twice();

        (new WarmModuleRouteCachesJob('TestModule', 'Item', ['counts' => true, 'formItem' => true]))->handle();
    }

    /** @test */
    public function it_warms_records_synchronously_when_queue_connection_is_sync(): void
    {
        Config::set('queue.default', 'sync');
        Bus::fake();

        ModularousCache::partialMock()
            ->shouldReceive('isEnabled')
            ->andReturn(true)
            ->shouldReceive('refreshModelCaches')
            ->twice();

        (new WarmModuleRouteCachesJob('TestModule', 'Item', ['presentationItem' => true]))->handle();

        Bus::assertNotDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function it_dispatches_presentation_warm_jobs_when_queue_is_async(): void
    {
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);
        Bus::fake();

        ModularousCache::partialMock()
            ->shouldReceive('isEnabled')
            ->andReturn(true);

        (new WarmModuleRouteCachesJob('TestModule', 'Item', ['presentationItem' => true]))->handle();

        Bus::assertDispatchedTimes(WarmPresentationItemJob::class, 2);
    }
}

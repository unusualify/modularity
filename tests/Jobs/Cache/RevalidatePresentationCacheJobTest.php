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
use Unusualify\Modularous\Jobs\Cache\RevalidatePresentationCacheJob;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestCase;

class RevalidatePresentationCacheJobTest extends TestCase
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

        $this->app->forgetInstance('modularous.cache');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_module_items');

        parent::tearDown();
    }

    /** @test */
    public function it_configures_the_modularous_cache_queue(): void
    {
        $job = new RevalidatePresentationCacheJob('TestModule', 'Item', 'warm');

        $this->assertSame('modularous-cache-test', $job->queue);
    }

    /** @test */
    public function it_skips_when_the_target_record_cannot_be_resolved(): void
    {
        ModularousCache::shouldReceive('purgeModelCacheTypes')->never();
        ModularousCache::shouldReceive('invalidateAllItemCaches')->never();

        (new RevalidatePresentationCacheJob('TestModule', 'Item', 'purge', 99999))->handle();
    }

    /** @test */
    public function it_purges_model_cache_types_for_record_level_purge_action(): void
    {
        $item = Item::query()->create(['name' => 'Alpha']);

        ModularousCache::shouldReceive('purgeModelCacheTypes')
            ->once()
            ->withArgs(function ($model, $types, $moduleName, $routeName) use ($item) {
                return $model->is($item)
                    && ($types['presentationItem'] ?? false) === true
                    && $moduleName === 'TestModule'
                    && $routeName === 'Item';
            });
        ModularousCache::shouldReceive('invalidateByRelatedModel')
            ->once()
            ->with(Item::class, $item->getKey());
        ModularousCache::shouldReceive('warmupModelCaches')->never();
        Bus::fake();

        (new RevalidatePresentationCacheJob(
            'test-module',
            'item',
            'purge',
            (int) $item->getKey(),
            ['presentationItem'],
        ))->handle();

        Bus::assertNotDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function it_dispatches_warm_presentation_item_for_record_level_warm_action(): void
    {
        Bus::fake();

        $item = Item::query()->create(['name' => 'Beta']);

        (new RevalidatePresentationCacheJob(
            'TestModule',
            'Item',
            'warm',
            (int) $item->getKey(),
            ['presentationItem'],
        ))->handle();

        Bus::assertDispatched(WarmPresentationItemJob::class, function (WarmPresentationItemJob $job) use ($item) {
            return $job->model->is($item)
                && $job->moduleName === 'TestModule'
                && $job->moduleRouteName === 'Item';
        });
    }

    /** @test */
    public function it_warms_non_presentation_types_via_warmup_model_caches(): void
    {
        $item = Item::query()->create(['name' => 'Gamma']);

        ModularousCache::shouldReceive('warmupModelCaches')
            ->once()
            ->withArgs(function ($model, $types, $moduleName, $routeName) use ($item) {
                return $model->is($item)
                    && ($types['formItem'] ?? false) === true
                    && ($types['presentationItem'] ?? true) === false
                    && $moduleName === 'TestModule'
                    && $routeName === 'Item';
            });
        Bus::fake();

        (new RevalidatePresentationCacheJob(
            'TestModule',
            'Item',
            'warm',
            (int) $item->getKey(),
            ['formItem'],
        ))->handle();
    }

    /** @test */
    public function it_invalidates_all_items_for_route_level_purge_action(): void
    {
        ModularousCache::shouldReceive('invalidateAllItemCaches')
            ->once()
            ->with('TestModule', 'Item', \Mockery::on(function (array $types) {
                return ($types['counts'] ?? false) === true
                    && ($types['presentationItem'] ?? false) === false;
            }), false);
        ModularousCache::shouldReceive('warmModuleRouteCaches')->never();

        (new RevalidatePresentationCacheJob(
            'TestModule',
            'Item',
            'purge',
            null,
            ['counts'],
        ))->handle();
    }

    /** @test */
    public function it_warms_route_caches_for_route_level_warm_action(): void
    {
        ModularousCache::shouldReceive('warmModuleRouteCaches')
            ->once()
            ->with('TestModule', 'Item', \Mockery::on(function (array $types) {
                return ($types['presentationItem'] ?? false) === true;
            }));
        ModularousCache::shouldReceive('invalidateAllItemCaches')->never();

        (new RevalidatePresentationCacheJob('TestModule', 'Item', 'warm'))->handle();
    }

    /** @test */
    public function it_runs_both_purge_and_warm_for_route_level_both_action(): void
    {
        ModularousCache::shouldReceive('invalidateAllItemCaches')->once();
        ModularousCache::shouldReceive('warmModuleRouteCaches')->once();

        (new RevalidatePresentationCacheJob('TestModule', 'Item', 'both'))->handle();
    }
}

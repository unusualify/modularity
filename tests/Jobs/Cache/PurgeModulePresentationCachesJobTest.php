<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\PurgeModulePresentationCachesJob;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestCase;

class PurgeModulePresentationCachesJobTest extends TestCase
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

        Config::set('modularous.cache.queue.name', 'modularous-cache-test');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', true);

        $this->app->forgetInstance('modularous.cache');
    }

    /** @test */
    public function it_returns_early_when_presentation_item_cache_is_disabled(): void
    {
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', false);
        $this->app->forgetInstance('modularous.cache');

        ModularousCache::partialMock()
            ->shouldReceive('isEnabled')
            ->with('TestModule', 'Item', 'presentationItem')
            ->andReturn(false)
            ->shouldReceive('purgePresentationItemForModuleRoute')
            ->never();

        (new PurgeModulePresentationCachesJob('TestModule', 'Item'))->handle();
    }

    /** @test */
    public function it_returns_early_when_the_module_route_cannot_be_resolved(): void
    {
        ModularousCache::shouldReceive('isEnabled')
            ->with('MissingModule', 'MissingRoute', 'presentationItem')
            ->andReturn(true);
        ModularousCache::shouldReceive('purgePresentationItemForModuleRoute')->never();

        (new PurgeModulePresentationCachesJob('MissingModule', 'MissingRoute'))->handle();
    }

    /** @test */
    public function it_purges_presentation_files_for_a_resolved_module_route(): void
    {
        ModularousCache::shouldReceive('isEnabled')
            ->with('TestModule', 'Item', 'presentationItem')
            ->andReturn(true);
        ModularousCache::shouldReceive('purgePresentationItemForModuleRoute')
            ->once()
            ->with('TestModule', 'Item')
            ->andReturn(4);

        (new PurgeModulePresentationCachesJob('test-module', 'item'))->handle();
    }
}

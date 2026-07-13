<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;
use Unusualify\Modularous\Tests\Services\Cache\Stubs\StubModelWithoutDependents;
use Unusualify\Modularous\Tests\TestModulesCase;

class DependentCacheInvalidatorModulesTest extends TestModulesCase
{
    private DependentCacheInvalidator $invalidator;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.manual_purge', false);
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.dependencies', []);

        $this->app->forgetInstance('modularous.cache');
        $this->invalidator = new DependentCacheInvalidator;
    }

    /** @test */
    public function it_runs_invalidation_for_configured_test_module_dependent(): void
    {
        Config::set('modularous.cache.dependencies', [
            StubModelWithoutDependents::class => [
                ['moduleName' => 'TestModule', 'moduleRouteName' => 'Item'],
            ],
        ]);

        $model = new StubModelWithoutDependents;
        $model->id = 11;
        $model->exists = true;

        $this->invalidator->invalidateForModel($model);

        $this->assertTrue($this->invalidator->hasDependents($model));
    }
}

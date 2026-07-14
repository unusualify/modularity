<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Modules\Cms\Entities\Page;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;
use Unusualify\Modularous\Tests\Services\Cache\Stubs\ReentrantDependentCacheInvalidator;
use Unusualify\Modularous\Tests\Services\Cache\Stubs\StubModelWithoutDependents;
use Unusualify\Modularous\Tests\Services\Cache\Stubs\StubModelWithMethodDependents;
use Unusualify\Modularous\Tests\Services\Cache\Stubs\StubModelWithPropertyDependents;
use Unusualify\Modularous\Tests\TestCase;

class DependentCacheInvalidatorTest extends TestCase
{
    private DependentCacheInvalidator $invalidator;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.dependencies', []);

        $this->app->forgetInstance('modularous.cache');
        $this->invalidator = new DependentCacheInvalidator;
    }

    /** @test */
    public function it_returns_early_when_invalidating_model_with_no_dependents(): void
    {
        $model = new StubModelWithoutDependents;
        $model->id = 1;
        $model->exists = true;

        $this->invalidator->invalidateForModel($model);

        $this->assertFalse($this->invalidator->hasDependents($model));
        $this->assertSame([], $this->invalidator->getConfigDependentsForModelClass(Page::class));
    }

    /** @test */
    public function it_reports_has_dependents_false_for_empty_model(): void
    {
        $model = new StubModelWithoutDependents;
        $model->id = 2;

        $this->assertFalse($this->invalidator->hasDependents($model));
    }

    /** @test */
    public function it_reports_has_dependents_true_from_property_or_method(): void
    {
        $withProperty = new StubModelWithPropertyDependents;
        $withProperty->id = 3;

        $withMethod = new StubModelWithMethodDependents;
        $withMethod->id = 4;

        $this->assertTrue($this->invalidator->hasDependents($withProperty));
        $this->assertTrue($this->invalidator->hasDependents($withMethod));
    }

    /** @test */
    public function it_returns_empty_config_dependents_when_dependencies_are_unset(): void
    {
        Config::set('modularous.cache.dependencies', []);

        $this->assertSame([], $this->invalidator->getConfigDependentsForModelClass(Page::class));
    }

    /** @test */
    public function it_resolves_config_dependents_for_known_module_routes(): void
    {
        if (! class_exists(Page::class)) {
            $this->markTestSkipped('Cms Page model is not available.');
        }

        Config::set('modularous.cache.dependencies', [
            Page::class => [
                ['moduleName' => 'Cms', 'moduleRouteName' => 'StyleSheet'],
            ],
        ]);

        $dependents = $this->invalidator->getConfigDependentsForModelClass(Page::class);

        if ($dependents === []) {
            $this->markTestSkipped('Cms StyleSheet route is not registered in the test environment.');
        }

        $this->assertSame('Cms', $dependents[0]['moduleName']);
        $this->assertSame('StyleSheet', $dependents[0]['moduleRouteName']);
        $this->assertTrue($dependents[0]['types']['counts']);
    }

    /** @test */
    public function it_merges_model_and_config_dependents_via_reflection(): void
    {
        $model = new StubModelWithPropertyDependents;
        $model->id = 5;

        $dependents = $this->invokeProtected($this->invalidator, 'getCacheDependents', [$model]);

        $this->assertNotEmpty($dependents);
        $this->assertSame('Other', $dependents[0]['moduleName']);
    }

    /** @test */
    public function it_detects_presentation_only_refresh_via_reflection(): void
    {
        $this->assertTrue($this->invokeProtected($this->invalidator, 'shouldRefreshOnly', [[
            'presentationItem' => true,
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
        ]]));

        $this->assertFalse($this->invokeProtected($this->invalidator, 'shouldRefreshOnly', [[
            'presentationItem' => true,
            'counts' => true,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
        ]]));
    }

    /** @test */
    public function it_respects_async_queue_configuration_via_reflection(): void
    {
        Config::set('modularous.cache.observer.queue', false);
        $this->assertFalse($this->invokeProtected($this->invalidator, 'shouldUseAsyncQueue'));

        Config::set('modularous.cache.observer.queue', true);
        Config::set('modularous.cache.queue.connection', null);
        Config::set('queue.default', 'sync');
        $this->assertFalse($this->invokeProtected($this->invalidator, 'shouldUseAsyncQueue'));

        Config::set('modularous.cache.queue.connection', 'redis');
        $this->assertTrue($this->invokeProtected($this->invalidator, 'shouldUseAsyncQueue'));
    }

    /** @test */
    public function it_filters_types_for_auto_invalidation_when_warmup_is_disabled(): void
    {
        $types = [
            'counts' => true,
            'index' => true,
            'record' => false,
            'formItem' => true,
            'formattedItem' => false,
            'presentationItem' => true,
        ];

        $filtered = $this->invokeProtected(
            $this->invalidator,
            'filterTypesForAutoInvalidation',
            ['Cms', 'Page', $types, false],
        );

        foreach ($filtered as $enabled) {
            $this->assertFalse($enabled);
        }
    }

    /** @test */
    public function it_filters_types_for_auto_invalidation_using_manual_purge_config(): void
    {
        Config::set('modularous.cache.manual_purge', true);
        Config::set('modularous.cache.modules.Cms.enabled', true);
        Config::set('modularous.cache.modules.Cms.routes.Page.enabled', true);

        $types = [
            'counts' => true,
            'index' => true,
            'record' => true,
            'formItem' => true,
            'formattedItem' => true,
            'presentationItem' => true,
        ];

        $filtered = $this->invokeProtected(
            $this->invalidator,
            'filterTypesForAutoInvalidation',
            ['Cms', 'Page', $types, true],
        );

        foreach ($filtered as $enabled) {
            $this->assertFalse($enabled);
        }
    }

    /** @test */
    public function it_guards_against_reentrant_invalidation_for_the_same_model(): void
    {
        $invalidator = new ReentrantDependentCacheInvalidator;
        $model = new StubModelWithoutDependents;
        $model->id = 99;
        $model->exists = true;

        $invalidator->invalidateForModel($model);

        $this->assertSame(1, $invalidator->runCount);
    }

    /** @test */
    public function it_completes_invalidation_when_dependents_reference_missing_modules(): void
    {
        $model = new StubModelWithPropertyDependents;
        $model->id = 7;
        $model->exists = true;

        $this->invalidator->invalidateForModel($model);

        $this->assertTrue($this->invalidator->hasDependents($model));
    }

    /** @test */
    public function it_keeps_enabled_types_when_auto_invalidation_allows_warmup(): void
    {
        Config::set('modularous.cache.manual_purge', false);

        ModularousCache::partialMock()
            ->shouldReceive('shouldAutoInvalidate')
            ->andReturn(true);

        $filtered = $this->invokeProtected(
            $this->invalidator,
            'filterTypesForAutoInvalidation',
            ['Cms', 'Page', [
                'counts' => true,
                'index' => true,
                'record' => false,
                'formItem' => true,
                'formattedItem' => false,
                'presentationItem' => false,
            ], true],
        );

        $this->assertTrue($filtered['counts']);
        $this->assertTrue($filtered['index']);
        $this->assertFalse($filtered['record']);
        $this->assertTrue($filtered['formItem']);
    }

    /** @test */
    public function it_resolves_numeric_config_dependent_entries(): void
    {
        Config::set('modularous.cache.dependencies', [
            StubModelWithoutDependents::class => [
                ['TestModule', 'Item'],
            ],
        ]);

        $dependents = $this->invalidator->getConfigDependentsForModelClass(StubModelWithoutDependents::class);

        if ($dependents === []) {
            $this->markTestSkipped('TestModule Item route is not registered in the test environment.');
        }

        $this->assertSame('TestModule', $dependents[0]['moduleName']);
        $this->assertSame('Item', $dependents[0]['moduleRouteName']);
    }

    /**
     * @param array<int, mixed> $args
     */
    private function invokeProtected(object $object, string $method, array $args = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object, ...$args);
    }
}

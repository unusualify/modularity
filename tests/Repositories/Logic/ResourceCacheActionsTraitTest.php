<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Logic;

use Mockery;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Logic\ResourceCacheActionsTrait;
use Unusualify\Modularous\Tests\TestCase;

class ResourceCacheActionsTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_resource_cache_action_schema_returns_empty_when_disabled(): void
    {
        $repository = new RepositoryUsingResourceCacheActions;

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(false);

        $this->assertSame([], $repository->getResourceCacheActionSchema());
    }

    public function test_get_resource_cache_action_schema_returns_actions_when_enabled(): void
    {
        $repository = new RepositoryUsingResourceCacheActions;

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);
        ModularousCache::shouldReceive('resolveManualCacheTypes')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(['record' => true, 'index' => false]);

        $schema = $repository->getResourceCacheActionSchema();

        $this->assertCount(4, $schema);
        $this->assertSame('cachePurge', $schema[0]['name']);
        $this->assertSame(['types' => ['record']], $schema[0]['params']);
        $this->assertSame('cacheWarmAll', $schema[3]['name']);
        $this->assertSame('table', $schema[2]['scope']);
    }

    public function test_get_resource_cache_table_action_schema_filters_table_scope_only(): void
    {
        $repository = new RepositoryUsingResourceCacheActionsForTableSchema;

        $tableActions = $repository->getResourceCacheTableActionSchema();

        $this->assertCount(2, $tableActions);
        $this->assertSame('cachePurgeAll', $tableActions[0]['name']);
    }

    public function test_get_form_actions_resource_cache_actions_trait_maps_form_actions(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('isParentRoute')->with('TestRoute')->andReturn(false);
        $module->shouldReceive('panelRouteNamePrefix')->with(false)->andReturn('admin.test_module.');

        $repository = new RepositoryUsingResourceCacheActionsForFormActions($module);

        $actions = $repository->getFormActionsResourceCacheActionsTrait();

        $this->assertArrayHasKey('cacheWarm', $actions);
        $this->assertSame('admin.test_module.test_route.cacheWarm', $actions['cacheWarm']['endpoint']);
        $this->assertTrue($actions['cacheWarm']['reloadOnSuccess']);
        $this->assertArrayNotHasKey('cachePurgeAll', $actions);
    }

    public function test_map_resource_cache_form_action_returns_null_for_invalid_name(): void
    {
        $repository = new RepositoryUsingResourceCacheActions;

        $this->assertNull($repository->invokeMapResourceCacheFormAction([], 'admin.'));
        $this->assertNull($repository->invokeMapResourceCacheFormAction(['name' => ''], 'admin.'));
    }

    public function test_map_resource_cache_form_action_maps_optional_attributes(): void
    {
        $repository = new RepositoryUsingResourceCacheActions;

        $action = $repository->invokeMapResourceCacheFormAction([
            'name' => 'cachePurge',
            'label' => 'Purge',
            'icon' => 'mdi-delete',
            'color' => 'warning',
            'params' => ['types' => ['record']],
            'confirmationModalAttributes' => ['title' => 'Confirm'],
            'reloadOnSuccess' => true,
        ], 'admin.test_module.test_route.');

        $this->assertSame([
            'name' => 'cachePurge',
            'label' => 'Purge',
            'icon' => 'mdi-delete',
            'color' => 'warning',
            'variant' => 'tonal',
            'type' => 'request',
            'method' => 'post',
            'endpoint' => 'admin.test_module.test_route.cachePurge',
            'params' => ['types' => ['record']],
            'creatable' => false,
            'editable' => true,
            'hasConfirmation' => true,
            'confirmationModalAttributes' => ['title' => 'Confirm'],
            'reloadOnSuccess' => true,
        ], $action);
    }
}

final class RepositoryUsingResourceCacheActions
{
    use ResourceCacheActionsTrait;

    public function getModuleName(): string
    {
        return 'TestModule';
    }

    public function getRouteName(): string
    {
        return 'TestRoute';
    }

    public function getModule(): ?Module
    {
        return null;
    }

    /**
     * @param array<string, mixed> $def
     * @return array<string, mixed>|null
     */
    public function invokeMapResourceCacheFormAction(array $def, string $routePrefix): ?array
    {
        return $this->mapResourceCacheFormAction($def, $routePrefix);
    }
}

class RepositoryUsingResourceCacheActionsForTableSchema
{
    use ResourceCacheActionsTrait;

    public function getModuleName(): ?string
    {
        return 'TestModule';
    }

    public function getRouteName(): ?string
    {
        return 'TestRoute';
    }

    public function getModule(): ?Module
    {
        return null;
    }

    public function getResourceCacheActionSchema(): array
    {
        return [
            ['name' => 'cachePurge', 'scope' => 'form'],
            ['name' => 'cachePurgeAll', 'scope' => 'table'],
            ['name' => 'cacheWarmAll', 'scope' => 'table'],
        ];
    }
}

class RepositoryUsingResourceCacheActionsForFormActions
{
    use ResourceCacheActionsTrait;

    public function __construct(private Module $module) {}

    public function getModuleName(): ?string
    {
        return 'TestModule';
    }

    public function getRouteName(): ?string
    {
        return 'TestRoute';
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    protected function resourceCacheActionsEnabled(): bool
    {
        return true;
    }

    public function getResourceCacheActionSchema(): array
    {
        return [
            [
                'name' => 'cacheWarm',
                'label' => 'Warm',
                'icon' => 'mdi-refresh',
                'params' => ['types' => ['record']],
                'reloadOnSuccess' => true,
            ],
            [
                'name' => 'cachePurgeAll',
                'label' => 'Purge all',
                'scope' => 'table',
            ],
        ];
    }
}

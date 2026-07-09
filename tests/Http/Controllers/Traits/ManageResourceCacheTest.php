<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmModuleRouteCachesJob;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Logic\ResourceCacheActionsTrait;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingManageResourceCache;
use Unusualify\Modularous\Tests\TestCase;

class ManageResourceCacheTest extends TestCase
{
    protected ControllerUsingManageResourceCache $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingManageResourceCache;
        $this->controller->setModuleName('TestModule');
        $this->controller->setRouteName('TestRoute');
        $this->controller->setModule($this->makeModuleMock());
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_set_table_actions_manage_resource_cache_skips_without_trait(): void
    {
        $this->controller->repository = new RepositoryWithoutResourceCacheActions;
        $this->controller->tableActions = [['name' => 'existing']];

        ModularousCache::shouldReceive('hasAdminCacheActions')->never();

        $this->controller->invokeSetTableActionsManageResourceCache();

        $this->assertSame([['name' => 'existing']], $this->controller->tableActions);
    }

    public function test_set_table_actions_manage_resource_cache_skips_when_admin_actions_disabled(): void
    {
        $this->controller->repository = new RepositoryWithResourceCacheActions([
            [
                'name' => 'cachePurgeAll',
                'label' => 'Purge all',
                'scope' => 'table',
            ],
        ]);
        $this->controller->tableActions = [['name' => 'existing']];

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(false);

        $this->controller->invokeSetTableActionsManageResourceCache();

        $this->assertSame([['name' => 'existing']], $this->controller->tableActions);
    }

    public function test_set_table_actions_manage_resource_cache_merges_table_actions(): void
    {
        $this->controller->repository = new RepositoryWithResourceCacheActions([
            [
                'name' => 'cachePurgeAll',
                'label' => 'Purge all',
                'icon' => 'mdi-delete-sweep-outline',
                'color' => 'warning',
                'scope' => 'table',
                'params' => ['types' => ['record']],
            ],
            [
                'name' => 'cacheWarm',
                'label' => 'Warm record',
                'scope' => 'form',
            ],
        ]);
        $this->controller->tableActions = [['name' => 'existing']];

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);

        $this->controller->invokeSetTableActionsManageResourceCache();

        $this->assertCount(2, $this->controller->tableActions);
        $this->assertSame('existing', $this->controller->tableActions[0]['name']);
        $this->assertSame('cachePurgeAll', $this->controller->tableActions[1]['name']);
        $this->assertSame('admin.test_module.test_route.cachePurgeAll', $this->controller->tableActions[1]['endpoint']);
        $this->assertSame(['types' => ['record']], $this->controller->tableActions[1]['params']);
    }

    public function test_map_resource_cache_table_action_returns_null_for_invalid_name(): void
    {
        $this->assertNull($this->controller->invokeMapResourceCacheTableAction([], 'admin.test_module.test_route.'));
        $this->assertNull($this->controller->invokeMapResourceCacheTableAction(['name' => ''], 'admin.test_module.test_route.'));
    }

    public function test_map_resource_cache_table_action_maps_defaults_and_optional_keys(): void
    {
        $action = $this->controller->invokeMapResourceCacheTableAction([
            'name' => 'cacheWarmAll',
            'label' => 'Warm all',
            'icon' => 'mdi-refresh-circle',
            'color' => 'primary',
            'params' => ['types' => ['record']],
            'reloadOnSuccess' => true,
            'confirmationModalAttributes' => ['title' => 'Confirm warm'],
        ], 'admin.test_module.test_route.');

        $this->assertSame([
            'name' => 'cacheWarmAll',
            'label' => 'Warm all',
            'icon' => 'mdi-refresh-circle',
            'color' => 'primary',
            'variant' => 'tonal',
            'type' => 'request',
            'method' => 'post',
            'endpoint' => 'admin.test_module.test_route.cacheWarmAll',
            'params' => ['types' => ['record']],
            'hasConfirmation' => true,
            'noSuperAdmin' => false,
            'confirmationModalAttributes' => ['title' => 'Confirm warm'],
            'reloadOnSuccess' => true,
        ], $action);
    }

    public function test_cache_purge_purges_selected_types_for_record(): void
    {
        $this->authorizeAsSuperadmin();
        $model = $this->makeModel(42);
        $types = ['record' => true, 'index' => false];

        $this->controller->repository = Mockery::mock();
        $this->controller->repository->shouldReceive('getById')->once()->with(42)->andReturn($model);

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);
        ModularousCache::shouldReceive('normalizeCacheTypesInput')
            ->once()
            ->with('record', 'TestModule', 'TestRoute')
            ->andReturn($types);
        ModularousCache::shouldReceive('purgeModelCacheTypes')
            ->once()
            ->with($model, $types, 'TestModule', 'TestRoute');

        $response = $this->controller->cachePurge(new Request(['types' => 'record']), 42);
        $payload = $response->getData(true);

        $this->assertSame(['record'], $payload['types']);
        $this->assertFalse($payload['queued']);
    }

    public function test_cache_warm_refreshes_selected_types_for_record(): void
    {
        $this->authorizeAsSuperadmin();
        $model = $this->makeModel(7);
        $types = ['record' => true, 'presentationItem' => true, 'index' => false];

        $this->controller->repository = Mockery::mock();
        $this->controller->repository->shouldReceive('getById')->once()->with(7)->andReturn($model);

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);
        ModularousCache::shouldReceive('normalizeCacheTypesInput')
            ->once()
            ->with(['record', 'presentationItem'], 'TestModule', 'TestRoute')
            ->andReturn($types);
        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->with($model, $types, [
                'moduleName' => 'TestModule',
                'moduleRouteName' => 'TestRoute',
            ]);

        $response = $this->controller->cacheWarm(new Request(['types' => ['record', 'presentationItem']]), 7);
        $payload = $response->getData(true);

        $this->assertSame(['record', 'presentationItem'], $payload['types']);
        $this->assertFalse($payload['queued']);
    }

    public function test_cache_purge_all_invalidates_module_route(): void
    {
        $this->authorizeAsSuperadmin();

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);
        ModularousCache::shouldReceive('invalidateModuleRoute')
            ->once()
            ->with('TestModule', 'TestRoute');

        $response = $this->controller->cachePurgeAll(new Request);
        $payload = $response->getData(true);

        $this->assertFalse($payload['queued']);
    }

    public function test_cache_warm_all_dispatches_warm_module_route_job(): void
    {
        Bus::fake();

        $this->authorizeAsSuperadmin();
        $types = ['record' => true, 'index' => false];

        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);
        ModularousCache::shouldReceive('normalizeCacheTypesInput')
            ->once()
            ->with('all', 'TestModule', 'TestRoute')
            ->andReturn($types);

        $response = $this->controller->cacheWarmAll(new Request(['types' => 'all']));
        $payload = $response->getData(true);

        $this->assertTrue($payload['queued']);
        $this->assertSame(['record'], $payload['types']);

        Bus::assertDispatched(WarmModuleRouteCachesJob::class, function (WarmModuleRouteCachesJob $job) use ($types): bool {
            return $job->moduleName === 'TestModule'
                && $job->moduleRouteName === 'TestRoute'
                && $job->types === $types;
        });
    }

    public function test_authorize_resource_cache_action_aborts_with_404_when_admin_actions_disabled(): void
    {
        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(false);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->invokeAuthorizeResourceCacheAction();
    }

    public function test_authorize_resource_cache_action_aborts_with_403_when_not_superadmin(): void
    {
        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);

        $user = new \stdClass;
        $user->is_superadmin = false;
        $this->controller->user = $user;

        try {
            $this->controller->invokeAuthorizeResourceCacheAction();
            $this->fail('Expected HttpException with status 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_authorize_resource_cache_action_aborts_with_403_when_user_missing(): void
    {
        ModularousCache::shouldReceive('hasAdminCacheActions')
            ->once()
            ->with('TestModule', 'TestRoute')
            ->andReturn(true);

        $this->controller->user = null;

        try {
            $this->controller->invokeAuthorizeResourceCacheAction();
            $this->fail('Expected HttpException with status 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_resolve_resource_cache_types_throws_when_types_null(): void
    {
        ModularousCache::shouldReceive('normalizeCacheTypesInput')->never();

        $this->expectException(ValidationException::class);

        $this->controller->invokeResolveResourceCacheTypes(new Request(['types' => null]));
    }

    public function test_resolve_resource_cache_types_throws_when_types_empty_array(): void
    {
        ModularousCache::shouldReceive('normalizeCacheTypesInput')->never();

        $this->expectException(ValidationException::class);

        $this->controller->invokeResolveResourceCacheTypes(new Request(['types' => []]));
    }

    public function test_resolve_resource_cache_types_defaults_to_all_when_types_missing(): void
    {
        $types = ['record' => true, 'index' => true];

        ModularousCache::shouldReceive('normalizeCacheTypesInput')
            ->once()
            ->with('all', 'TestModule', 'TestRoute')
            ->andReturn($types);

        $resolved = $this->controller->invokeResolveResourceCacheTypes(new Request);

        $this->assertSame($types, $resolved);
    }

    public function test_resolve_resource_cache_types_returns_normalized_types(): void
    {
        $types = ['record' => true, 'index' => false];

        ModularousCache::shouldReceive('normalizeCacheTypesInput')
            ->once()
            ->with('record', 'TestModule', 'TestRoute')
            ->andReturn($types);

        $resolved = $this->controller->invokeResolveResourceCacheTypes(new Request(['types' => 'record']));

        $this->assertSame($types, $resolved);
    }

    protected function authorizeAsSuperadmin(): void
    {
        $user = new \stdClass;
        $user->is_superadmin = true;
        $this->controller->user = $user;
    }

    protected function makeModuleMock(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('isParentRoute')
            ->with('TestRoute')
            ->andReturn(false);
        $module->shouldReceive('panelRouteNamePrefix')
            ->with(false)
            ->andReturn('admin.test_module.');

        return $module;
    }

    protected function makeModel(int $id): Model
    {
        $model = new class extends Model
        {
            protected $table = 'manage_resource_cache_test_models';
        };
        $model->setRawAttributes(['id' => $id]);
        $model->exists = true;

        return $model;
    }
}

final class RepositoryWithoutResourceCacheActions
{
}

final class RepositoryWithResourceCacheActions
{
    use ResourceCacheActionsTrait;

    /**
     * @param array<int, array<string, mixed>> $tableSchema
     */
    public function __construct(private array $tableSchema = [])
    {
    }

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
     * @return array<int, array<string, mixed>>
     */
    public function getResourceCacheTableActionSchema(): array
    {
        return $this->tableSchema;
    }
}

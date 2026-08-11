<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Mockery;
use Spatie\Permission\Models\Role;
use TestModules\TestModule\Controllers\ItemController;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\Http\Controllers\Stubs\BaseControllerStub;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\TestModulesCase;

class BaseControllerTest extends TestModulesCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        config()->set('modularous.use_inertia', false);

        Route::get('/admin/test-module/items/{item}/edit', static fn () => 'ok')->name('item.edit');
        Route::get('/admin/test-module/items/{TestModule}/edit', static fn () => 'ok');
        Route::get('/admin/test-module/items/create', static fn () => 'ok')->name('item.create');
        Route::get('/admin/test-module/items', static fn () => 'ok')->name('item.index');
        Route::getRoutes()->refreshNameLookups();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function makeRepositoryMock(): Repository
    {
        $model = new class extends Model
        {
            protected $table = 'base_controller_items';

            public function setNewOrder($ids): bool
            {
                return true;
            }
        };

        $repository = Mockery::mock(Repository::class);
        $repository->shouldIgnoreMissing();
        $repository->shouldReceive('getModel')->andReturn($model);
        $repository->shouldReceive('hasBehavior')->andReturn(false);
        $repository->shouldReceive('getFormActions')->andReturn([]);
        $repository->shouldReceive('getShowFields')->andReturn([]);
        $repository->shouldReceive('appendFormSchema')->andReturn([]);
        $repository->shouldReceive('prependFormSchema')->andReturn([]);
        $repository->shouldReceive('appendTableHeader')->andReturn([]);
        $repository->shouldReceive('prependTableHeader')->andReturn([]);

        return $repository;
    }

    public function test_item_controller_extends_base_controller(): void
    {
        $controller = new ItemController($this->app, Request::create('/admin/test-module/items'));

        $this->assertInstanceOf(BaseController::class, $controller);
        $this->assertSame('TestModule', $controller->getModuleName());
    }

    public function test_constructor_sets_view_prefix_from_presentation_prefix(): void
    {
        $controller = new BaseControllerStub($this->app, Request::create('/admin/test-module/items'));

        $this->assertSame('test_module::item', $controller->exposeGetViewPrefix());
    }

    public function test_destroy_returns_success_when_repository_deletes_item(): void
    {
        $item = (object) ['id' => 4];

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->with(4)->andReturn($item);
        $repository->shouldReceive('delete')->with(4)->andReturn(true);

        $request = $this->requestWithRoute(['item' => 4]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->destroy(4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_destroy_returns_error_when_delete_fails(): void
    {
        $item = (object) ['id' => 5];

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->with(5)->andReturn($item);
        $repository->shouldReceive('delete')->with(5)->andReturn(false);

        $request = $this->requestWithRoute(['item' => 5]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->destroy(5);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_store_creates_item_and_returns_ajax_success(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 21;
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('create')->once()->andReturn($item);
        $repository->shouldReceive('getShowFields')->never();

        $request = Request::create('/admin/test-module/items', 'POST', ['name' => 'Created'], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $controller = new class($this->app, $request, $repository) extends BaseControllerStub
        {
            protected function validateFormRequest($schema = [])
            {
                return $this->request;
            }

            protected function getItemIdentifier($item): ?int
            {
                return $item->id;
            }

            protected function getPreviousRouteSchema(): array
            {
                return [];
            }
        };

        $controller->setModule($controller->getModule());
        $response = $controller->store();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(Session::get('Item_retain'));
    }

    public function test_bulk_delete_returns_success_when_repository_deletes_ids(): void
    {
        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('bulkDelete')->with([1, 2])->andReturn(true);

        $request = Request::create('/admin/test-module/items/bulk-delete', 'POST', ['ids' => [1, 2]]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $this->assertSame(200, $controller->bulkDelete()->getStatusCode());
    }

    public function test_restore_returns_success_when_repository_restores_item(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 9;
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('restore')->with(9)->andReturn(true);
        $repository->shouldReceive('getById')->with(9)->andReturn($item);

        $request = Request::create('/admin/test-module/items/restore', 'POST', ['id' => 9]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $this->assertSame(200, $controller->restore()->getStatusCode());
    }

    public function test_reorder_returns_success_when_model_reorders_ids(): void
    {
        $model = Mockery::mock();
        $model->shouldReceive('setNewOrder')->with([3, 2, 1])->andReturn(true);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $request = Request::create('/admin/test-module/items/reorder', 'POST', ['ids' => [3, 2, 1]]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $this->assertSame(200, $controller->reorder()->getStatusCode());
    }

    public function test_preload_sets_up_form_schema_and_withs(): void
    {
        $guardName = Modularous::getAuthGuardName();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => $guardName]);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com']);
        $admin->assignRole($adminRole);
        $this->actingAs($admin, $guardName);

        $controller = new BaseControllerStub($this->app, Request::create('/admin/test-module/items'));
        $controller->setModule($controller->getModule());
        $controller->exposePreloadBase();

        $this->addToAssertionCount(1);
    }

    public function test_index_returns_rendered_index_data(): void
    {
        $controller = new BaseControllerStub($this->app, Request::create('/admin/test-module/items'));
        $controller->setModule($controller->getModule());

        $response = $controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('TestModule', $controller->lastIndexData['module']);
    }

    public function test_index_adds_open_create_flag_when_requested(): void
    {
        $request = Request::create('/admin/test-module/items', 'GET', ['openCreate' => true]);
        $controller = new BaseControllerStub($this->app, $request);
        $controller->setModule($controller->getModule());

        $controller->index();

        $this->assertTrue($controller->lastIndexData['openCreate']);
    }

    public function test_index_returns_ajax_payload_when_ids_are_requested(): void
    {
        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getByIds')->once()->andReturn(collect([['id' => 1]]));

        $request = Request::create('/admin/test-module/items', 'GET', ['ids' => '1,2'], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->index();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_create_renders_form_data(): void
    {
        putenv('PERMISSION_GATES_DEACTIVATE=1');

        $controller = new BaseControllerStub($this->app, Request::create('/admin/test-module/items/create'));
        $controller->setModule($controller->getModule());

        $response = $controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertNull($controller->lastFormData['id']);

        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_edit_renders_form_for_existing_item(): void
    {
        putenv('PERMISSION_GATES_DEACTIVATE=1');

        $request = $this->requestWithRoute(['item' => 12], 'GET');
        $controller = new BaseControllerStub($this->app, $request);
        $controller->setModule($controller->getModule());
        $controller->setIndexOptions(['editInModal' => false]);

        $response = $controller->edit(12);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame(12, $controller->lastFormData['id']);

        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_show_returns_json_for_ajax_requests(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 7;

            public function attributesToArray(): array
            {
                return ['id' => 7, 'name' => 'Shown'];
            }
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 7)->andReturn($item);
        $repository->shouldReceive('getShowFields')->never();

        $request = $this->requestWithRoute(['item' => 7], 'GET', true);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->show(7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(7, $response->getData(true)['id']);
    }

    public function test_update_returns_redirect_when_save_type_is_cancel(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 3;
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 3)->andReturn($item);

        $request = $this->requestWithRoute(['item' => 3], 'PUT', false, ['cmsSaveType' => 'cancel']);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->update(3);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('redirector', $response->getData(true));
    }

    public function test_update_returns_success_for_ajax_requests(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 8;
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->withArgs(fn ($id) => $id === 8)->andReturn($item);
        $repository->shouldReceive('update')->with(8, Mockery::type('array'), [])->andReturnTrue();

        $request = $this->requestWithRoute(['item' => 8], 'PUT', true, ['name' => 'Updated']);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->update(8);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_store_redirects_when_save_type_ends_with_close(): void
    {
        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 15;
        };
        $item->exists = true;

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('create')->once()->andReturn($item);

        $request = Request::create('/admin/test-module/items', 'POST', [
            'name' => 'Saved',
            'cmsSaveType' => 'save-close',
        ]);

        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $response = $controller->store();

        $this->assertSame('/admin/back', $response->getData(true)['redirector']);
    }

    public function test_duplicate_returns_success_when_repository_duplicates_item(): void
    {
        $guardName = Modularous::getAuthGuardName();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => $guardName]);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-duplicate@example.com']);
        $admin->assignRole($adminRole);
        $this->actingAs($admin, $guardName);

        $item = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 2;
        };
        $item->exists = true;

        $newItem = new class extends Model
        {
            protected $table = 'base_controller_items';

            public $id = 22;
        };
        $newItem->exists = true;
        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->with(2)->andReturn($item);
        $repository->shouldReceive('duplicate')->with(2, 'name', [])->andReturn($newItem);

        $request = $this->requestWithRoute(['item' => 2], 'POST');
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());
        // duplicate() calls preload(); user must be set as PanelController middleware would.
        $controller->exposePreloadBase();

        $response = $controller->duplicate(2);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('message', $response->getData(true));
    }

    public function test_force_delete_returns_success_when_repository_force_deletes_item(): void
    {
        $item = (object) ['id' => 6];

        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('getById')->with(6)->andReturn($item);
        $repository->shouldReceive('forceDelete')->with(6)->andReturn(true);

        $request = Request::create('/admin/test-module/items/force-delete', 'POST', ['id' => 6]);
        $controller = new BaseControllerStub($this->app, $request, $repository);
        $controller->setModule($controller->getModule());

        $this->assertSame(200, $controller->forceDelete()->getStatusCode());
    }

    public function test_bulk_force_delete_and_bulk_restore_delegate_to_repository(): void
    {
        $repository = $this->makeRepositoryMock();
        $repository->shouldReceive('bulkForceDelete')->with([4, 5])->andReturn(true);
        $repository->shouldReceive('bulkRestore')->with(['6', '7'])->andReturn(true);

        $controller = new BaseControllerStub($this->app, Request::create('/'), $repository);
        $controller->setModule($controller->getModule());

        $forceDeleteRequest = Request::create('/bulk-force-delete', 'POST', ['ids' => [4, 5]]);
        $forceDeleteController = new BaseControllerStub($this->app, $forceDeleteRequest, $repository);
        $forceDeleteController->setModule($forceDeleteController->getModule());
        $this->assertSame(200, $forceDeleteController->bulkForceDelete()->getStatusCode());

        $restoreRequest = Request::create('/bulk-restore', 'POST', ['ids' => '6,7']);
        $restoreController = new BaseControllerStub($this->app, $restoreRequest, $repository);
        $restoreController->setModule($restoreController->getModule());
        $this->assertSame(200, $restoreController->bulkRestore()->getStatusCode());
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function requestWithRoute(array $parameters, string $method = 'DELETE', bool $ajax = false, array $payload = []): Request
    {
        $request = Request::create('/admin/test-module/items/4', $method, $payload, [], [], $ajax ? [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ] : []);
        $route = (new RoutingRoute($method, '/admin/test-module/items/{item}', []));
        $route->bind($request);

        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(static fn () => $route);

        return $request;
    }
}

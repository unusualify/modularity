<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingTableRows;
use Unusualify\Modularous\Tests\TestCase;

class TableRowsTest extends TestCase
{
    protected ControllerUsingTableRows $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingTableRows;
        $this->controller->module = $this->makeModuleMock();
        $this->controller->user = (object) ['is_superadmin' => true];
        $this->controller->setAllowableUser($this->controller->user);

        Modularous::shouldReceive('find')
            ->with('TestModule')
            ->andReturnUsing(fn () => $this->controller->module);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_table_row_actions_returns_empty_without_module(): void
    {
        $this->controller->module = null;

        $this->assertSame([], $this->controller->invokeGetTableRowActions());
    }

    public function test_get_table_row_actions_includes_edit_and_delete_when_enabled(): void
    {
        $this->controller->indexOptions = [
            'edit' => true,
            'delete' => true,
        ];
        $this->controller->repository = $this->makeRepositoryMock();

        $actions = $this->controller->invokeGetTableRowActions();
        $names = array_column($actions, 'name');

        $this->assertContains('edit', $names);
        $this->assertContains('delete', $names);
    }

    public function test_get_table_row_actions_includes_duplicate_show_and_activity(): void
    {
        $this->controller->indexOptions = [
            'duplicate' => true,
            'show' => true,
            'activity' => true,
        ];
        $this->controller->repository = $this->makeRepositoryMock();

        $names = array_column($this->controller->invokeGetTableRowActions(), 'name');

        $this->assertContains('duplicate', $names);
        $this->assertContains('Show', $names);
        $this->assertContains('Last Operations', $names);
    }

    public function test_get_table_row_actions_skips_defaults_when_configured(): void
    {
        $this->controller->configFieldsByRoute = [
            'no_default_table_row_actions' => true,
        ];
        $this->controller->indexOptions = ['edit' => true];
        $this->controller->repository = $this->makeRepositoryMock();

        $this->assertSame([], $this->controller->invokeGetTableRowActions());
    }

    public function test_get_table_row_actions_merges_navigation_actions(): void
    {
        $this->controller->indexOptions = [];
        $this->controller->repository = $this->makeRepositoryMock();
        $this->controller->module = $this->makeModuleMock([
            [
                'name' => 'customExport',
                'label' => 'Export',
            ],
        ]);

        $names = array_column($this->controller->invokeGetTableRowActions(), 'name');

        $this->assertContains('customExport', $names);
    }

    public function test_get_table_row_actions_sets_dropdown_type_when_more_than_three_actions(): void
    {
        $this->controller->indexOptions = [
            'edit' => true,
            'delete' => true,
            'restore' => true,
            'forceDelete' => true,
        ];
        $this->controller->repository = $this->makeRepositoryMock();

        $this->controller->invokeGetTableRowActions();

        $this->assertSame('dropdown', $this->controller->tableAttributes['rowActionsType']);
    }

    public function test_get_table_row_actions_filters_by_allowed_roles(): void
    {
        $this->controller->indexOptions = ['edit' => true];
        $this->controller->repository = $this->makeRepositoryMock();
        $this->controller->module = $this->makeModuleMock([
            [
                'name' => 'restricted',
                'allowedRoles' => ['super-admin-only'],
            ],
        ]);
        Modularous::shouldReceive('find')
            ->with('TestModule')
            ->andReturnUsing(fn () => $this->controller->module);

        $this->controller->user = Mockery::mock();
        $this->controller->user->is_superadmin = false;
        $this->controller->user->shouldReceive('hasRole')->andReturn(false);
        $this->controller->setAllowableUser($this->controller->user);

        $names = array_column($this->controller->invokeGetTableRowActions(), 'name');

        $this->assertNotContains('restricted', $names);
    }

    protected function makeModuleMock(array $navigationActions = []): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('generatePermissionMiddlewareDefinition')
            ->with(Permission::EDIT->value, 'TestRoute')
            ->andReturn('can-edit-test-route');
        $module->shouldReceive('generatePermissionMiddlewareDefinition')
            ->with(Permission::DELETE->value, 'TestRoute')
            ->andReturn('can-delete-test-route');
        $module->shouldReceive('getNavigationActions')
            ->with('TestRoute')
            ->andReturn($navigationActions);

        return $module;
    }

    protected function makeRepositoryMock(): object
    {
        $model = new class extends Model
        {
            protected $table = 'table_rows_test_models';
        };

        $repository = Mockery::mock();
        $repository->shouldReceive('getModel')->andReturn($model);
        $repository->shouldReceive('getPaymentFormSchema')->never();

        return $repository;
    }
}

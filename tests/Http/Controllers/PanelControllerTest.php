<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\Http\Controllers\Stubs\PanelControllerStub;
use Unusualify\Modularous\Tests\TestModulesCase;

class PanelControllerTest extends TestModulesCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_constructor_initializes_panel_state(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/admin/test-module/items'));

        $this->assertSame('Item', $controller->getRouteName());
        $this->assertNotEmpty($controller->exposeModelTitle());
        $this->assertIsBool($controller->exposeIsParent());
    }

    public function test_preload_sets_title_column_and_route_prefix(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/admin/test-module/items'));
        $controller->preload();

        $this->assertSame('name', $controller->getRouteTitleColumnKey());
        $this->assertNotEmpty($controller->exposeGetRoutePrefix());
    }

    public function test_generate_route_prefix_includes_module_segment_for_child_routes(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $controller->setIsParent(false);

        $prefix = $controller->exposeGenerateRoutePrefix();

        $this->assertStringContainsString('test_module', $prefix);
    }

    public function test_get_index_option_returns_default_when_not_overridden(): void
    {
        putenv('PERMISSION_GATES_DEACTIVATE=1');

        $controller = new PanelControllerStub($this->app, Request::create('/'));

        $this->assertTrue($controller->exposeGetIndexOption('index'));
        $this->assertFalse($controller->exposeGetIndexOption('publish'));

        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_remove_middleware_unsets_matching_entry(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $controller->middleware('modularous.panel');
        $controller->middleware('auth');

        $controller->exposeRemoveMiddleware('auth');

        $middlewareNames = array_column($controller->getMiddleware(), 'middleware');

        $this->assertNotContains('auth', $middlewareNames);
        $this->assertContains('modularous.panel', $middlewareNames);
    }

    public function test_get_json_data_uses_light_list_mode_when_requested(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(['data' => [['id' => 1]]]);

        $request = Request::create('/items', 'GET', [
            'light' => true,
            'columns' => ['name'],
            'itemsPerPage' => 25,
        ]);

        $controller = new PanelControllerStub($this->app, $request, $repository);
        $controller->preload();

        $this->assertSame(['data' => [['id' => 1]]], $controller->exposeGetJSONData());
    }

    public function test_get_json_data_returns_formatted_index_items_by_default(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 2]], 1, 10);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getPaginator')->once()->andReturn($paginator);

        $controller = new PanelControllerStub($this->app, Request::create('/items'), $repository);
        $controller->preload();

        $this->assertSame($paginator, $controller->exposeGetJSONData());
    }

    public function test_get_form_request_class_falls_back_to_request_when_form_request_missing(): void
    {
        $request = Request::create('/items', 'POST', ['name' => 'Example']);
        $controller = new PanelControllerStub($this->app, $request);

        $this->assertSame($request, $controller->exposeGetFormRequestClass());
    }

    public function test_nested_parent_scopes_returns_empty_when_route_is_not_nested(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));

        $this->assertSame([], $controller->exposeNestedParentScopes());
    }

    public function test_get_replace_url_honors_request_flag(): void
    {
        $controller = new PanelControllerStub(
            $this->app,
            Request::create('/items', 'GET', ['replaceUrl' => 'false'])
        );

        $this->assertFalse($controller->exposeGetReplaceUrl());
    }

    public function test_is_relation_field_detects_defined_relations(): void
    {
        $model = new class extends Model
        {
            protected $table = 'relation_field_models';

            public function definedRelations(): array
            {
                return ['author'];
            }
        };

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $controller = new PanelControllerStub($this->app, Request::create('/'), $repository);

        $this->assertTrue($controller->exposeIsRelationField('author'));
        $this->assertFalse($controller->exposeIsRelationField('missing'));
    }

    public function test_is_gateable_reflects_permission_gate_env_flag(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));

        putenv('PERMISSION_GATES_DEACTIVATE=1');
        $this->assertFalse($controller->exposeIsGateable());
        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_get_formatted_index_items_returns_paginator_instance(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->assertSame($paginator, $controller->exposeGetFormattedIndexItems($paginator));
    }

    public function test_validate_form_request_strips_unauthorized_fields(): void
    {
        putenv('PERMISSION_GATES_DEACTIVATE=1');

        $user = Mockery::mock();
        $user->shouldReceive('cannot')->with('restricted_field')->andReturn(true);

        Auth::shouldReceive('guard')->with(Modularous::getAuthGuardName())->andReturnSelf();
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/items', 'POST', [
            'name' => 'Allowed',
            'restricted_field' => 'Denied',
        ]);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn(new class extends Model
        {
            protected $table = 'panel_form_models';
        });

        $controller = new class($this->app, $request, $repository) extends PanelControllerStub
        {
            protected $fieldsPermissions = [
                'restricted_field' => 'restricted_field',
            ];
        };

        $formRequest = $controller->exposeValidateFormRequest();

        $this->assertSame('Allowed', $formRequest->input('name'));
        $this->assertNull($formRequest->input('restricted_field'));

        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_nested_parent_scopes_returns_foreign_key_scope_for_belongs_to_routes(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('hasColumn')->with('category_id')->andReturn(true);
        $repository->shouldReceive('getModel')->andReturn(new class extends Model
        {
            protected $table = 'nested_scope_models';
        });

        $controller = new PanelControllerStub($this->app, Request::create('/'), $repository);
        $controller->configureNested(10, 'category');

        $this->assertSame(['category_id' => 10], $controller->exposeNestedParentScopes());
    }

    public function test_get_parent_module_foreign_key_uses_nested_parent_name(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $controller->configureNested(1, 'blog_post');

        $this->assertSame('blog_post_id', $controller->exposeGetParentModuleForeignKey());
    }

    public function test_get_module_route_builds_edit_route_for_item(): void
    {
        putenv('PERMISSION_GATES_DEACTIVATE=1');

        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $controller->setModule($controller->getModule());
        $controller->preload();

        $url = $controller->exposeGetModuleRoute(5, 'edit');

        $this->assertStringContainsString('edit', $url);
        $this->assertStringContainsString('5', $url);

        putenv('PERMISSION_GATES_DEACTIVATE');
    }

    public function test_title_is_translatable_delegates_to_repository(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('isTranslatable')->with('name')->andReturn(true);

        $controller = new PanelControllerStub($this->app, Request::create('/'), $repository);

        $this->assertTrue($controller->exposeTitleIsTranslatable());
    }

    public function test_is_parent_route_defaults_to_module_route_config(): void
    {
        $controller = new PanelControllerStub($this->app, Request::create('/'));
        $controller->setModule($controller->getModule());

        $this->assertIsBool($controller->exposeIsParentRoute());
    }

    public function test_is_relation_field_matches_id_suffix_columns(): void
    {
        $model = new class extends Model
        {
            protected $table = 'relation_field_models';

            public function definedRelations(): array
            {
                return ['authors'];
            }
        };

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $controller = new PanelControllerStub($this->app, Request::create('/'), $repository);

        $this->assertTrue($controller->exposeIsRelationField('author_id'));
    }
}

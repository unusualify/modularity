<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\Http\Controllers\ControllerUsingManageUtilities;
use Unusualify\Modularous\Tests\TestCase;

class ManageUtilitiesTest extends TestCase
{
    protected ControllerUsingManageUtilities $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingManageUtilities;
        $this->controller->module = $this->makeModuleMock();
        $this->controller->setFormSchema([]);
        $this->controller->setRequest(Request::create('/'));

        Route::shouldReceive('current')->andReturn(
            (new RoutingRoute('GET', '/test', ['uses' => 'Controller@index']))
        );
        Route::shouldReceive('has')->andReturn(false);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_index_data_form_data_and_index_item_data_default_to_empty_extensions(): void
    {
        $this->assertSame([], $this->controller->indexData($this->controller->request));
        $this->assertSame([], $this->controller->formData($this->controller->request));
        $this->assertSame([], $this->controller->indexItemData(new \stdClass));
    }

    public function test_get_index_data_builds_table_payload(): void
    {
        $data = $this->controller->invokeGetIndexData();

        $this->assertArrayHasKey('tableAttributes', $data);
        $this->assertTrue($data['tableAttributes']['isModuleTable']);
        $this->assertSame('Test Route', $data['tableAttributes']['name']);
        $this->assertSame('/admin/test', $data['endpoints']['index']);
        $this->assertSame([['slug' => 'all', 'name' => 'All']], $data['tableAttributes']['filterList']);
    }

    public function test_get_index_data_includes_search_from_request(): void
    {
        $request = Request::create('/', 'GET', ['search' => 'press']);
        $this->app->instance('request', $request);
        $this->controller->setRequest($request);

        $data = $this->controller->invokeGetIndexData();

        $this->assertSame('press', $data['tableAttributes']['searchInitialValue']);
    }

    public function test_get_form_data_for_create_action(): void
    {
        $data = $this->controller->invokeGetFormData();

        $this->assertFalse($data['formAttributes']['isEditing']);
        $this->assertSame('/admin/test/form', $data['formAttributes']['actionUrl']);
        $this->assertSame('/admin/test/form', $data['endpoints']['store']);
    }

    public function test_get_view_layout_variables_for_create_action(): void
    {
        $this->controller->setCurrentRouteAction('create');

        $layout = $this->controller->invokeGetViewLayoutVariables();

        $this->assertNotEmpty($layout['pageTitle']);
        $this->assertNotEmpty($layout['headerTitle']);
    }

    public function test_get_view_layout_variables_uses_custom_title_from_table_attributes(): void
    {
        $this->controller->setTableAttributes(['customTitle' => 'Custom listing']);
        $this->controller->setCurrentRouteAction('edit');

        $layout = $this->controller->invokeGetViewLayoutVariables();

        $this->assertSame('Custom listing', $layout['headerTitle']);
    }

    public function test_add_index_withs_nested_data_collects_relations(): void
    {
        $controller = new class extends ControllerUsingManageUtilities
        {
            protected function getConfigFieldsByRoute($fieldName, $default = null)
            {
                if ($fieldName === 'modules') {
                    return [
                        (object) [
                            'type' => 'formWrapper',
                            'elements' => [
                                (object) ['relation' => 'settings'],
                                (object) ['relation' => 'metadata'],
                            ],
                        ],
                    ];
                }

                return $default;
            }
        };

        $this->assertSame(['settings', 'metadata'], $controller->invokeAddIndexWithsNestedData());
    }

    public function test_get_nested_data_returns_empty_without_modules_config(): void
    {
        $this->assertSame([], $this->controller->getNestedData());
    }

    protected function makeModuleMock(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getSnakeName')->andReturn('test_module');

        return $module;
    }
}

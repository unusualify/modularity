<?php

namespace Unusualify\Modularous\Tests\Services;

use TestModules\TestModule\Entities\Item;
use Unusualify\Modularous\Exceptions\ModuleNotFoundException;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\Tests\TestModulesCase;

class ConnectorTest extends TestModulesCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureConnectorEndpointRoute();
    }

    private function ensureConnectorEndpointRoute(): void
    {
        $module = Modularous::find('TestModule');

        if (! $module instanceof Module) {
            return;
        }

        if ($this->moduleRouteActionIsAvailable($module, 'Item', 'index')) {
            return;
        }

        $this->registerStubModuleRouteAction($module, 'Item', 'index');

        $this->assertTrue(
            $this->moduleRouteActionIsAvailable($module, 'Item', 'index'),
            'Unable to register TestModule Item index route required by Connector endpoint tests.'
        );
    }

    /** @test */
    public function it_constructs_without_connector()
    {
        $connector = new Connector;

        $this->assertInstanceOf(Connector::class, $connector);
    }

    /** @test */
    public function it_constructs_with_string_connector()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertInstanceOf(Connector::class, $connector);
        $this->assertEquals('TestModule', $connector->getModuleName());
    }

    /** @test */
    public function it_constructs_with_array_connector()
    {
        $connectorArray = ['module' => 'TestModule'];
        $connector = new Connector($connectorArray);

        $this->assertInstanceOf(Connector::class, $connector);
    }

    /** @test */
    public function it_throws_exception_for_empty_module_name()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid connector');

        new Connector('^endpoint');
    }

    /** @test */
    public function it_throws_exception_for_missing_module_name()
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage('Missing module name');

        new Connector('|TestModule^endpoint');
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_module()
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage('Module NonExistentModule not found');

        new Connector('NonExistentModule^endpoint');
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_route()
    {
        $this->expectException(ModuleNotFoundException::class);
        $this->expectExceptionMessage('Route NonExistentRoute not found');

        new Connector('TestModule|NonExistentRoute^endpoint');
    }

    /** @test */
    public function it_parses_simple_connector_string()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertEquals('TestModule', $connector->getModuleName());
        $this->assertEquals('Item', $connector->getRouteName());
        $this->assertTrue($connector->isLinkTarget());
    }

    /** @test */
    public function it_parses_connector_with_module_and_route()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertEquals('TestModule', $connector->getModuleName());
        $this->assertEquals('Item', $connector->getRouteName());
    }

    /** @test */
    public function it_parses_endpoint_target()
    {
        $connector = new Connector('TestModule|Item^endpoint->index');

        $events = $connector->getEvents();

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertEquals('getRouteActionUrl', $events[0]['name']);
        $this->assertEquals('Item', $events[0]['args'][0]);
        $this->assertEquals('index', $events[0]['args'][1]);
    }

    /** @test */
    public function it_parses_uri_target()
    {
        $connector = new Connector('TestModule|Item^uri->show');

        $events = $connector->getEvents();

        $this->assertIsArray($events);
        $this->assertEquals('getRouteActionUrl', $events[0]['name']);
        $this->assertEquals('show', $events[0]['args'][1]);
    }

    /** @test */
    public function it_parses_url_target()
    {
        $connector = new Connector('TestModule|Item^url');

        $this->assertEquals('url', $connector->getTargetTypeKey());
        $this->assertTrue($connector->isLinkTarget());
    }

    /** @test */
    public function it_can_get_events()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $events = $connector->getEvents();

        $this->assertIsArray($events);
        $this->assertNotEmpty($events);
    }

    /** @test */
    public function it_can_push_event()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $newEvent = ['name' => 'customMethod', 'args' => ['arg1']];
        $connector->pushEvent($newEvent);

        $events = $connector->getEvents();
        $lastEvent = end($events);

        $this->assertEquals('customMethod', $lastEvent['name']);
        $this->assertEquals(['arg1'], $lastEvent['args']);
    }

    /** @test */
    public function it_can_unshift_event()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $newEvent = ['name' => 'firstMethod', 'args' => []];
        $connector->unshiftEvent($newEvent);

        $events = $connector->getEvents();

        $this->assertEquals('firstMethod', $events[0]['name']);
    }

    /** @test */
    public function it_can_push_multiple_events()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $newEvents = [
            ['name' => 'method1', 'args' => []],
            ['name' => 'method2', 'args' => []],
        ];
        $connector->pushEvents($newEvents);

        $events = $connector->getEvents();

        $this->assertGreaterThanOrEqual(3, count($events)); // Original + 2 new
    }

    /** @test */
    public function it_can_unshift_multiple_events()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $newEvents = [
            ['name' => 'first', 'args' => []],
            ['name' => 'second', 'args' => []],
        ];
        $connector->unshiftEvents($newEvents);

        $events = $connector->getEvents();

        $this->assertEquals('first', $events[0]['name']);
        $this->assertEquals('second', $events[1]['name']);
    }

    /** @test */
    public function it_can_update_event_parameters()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $connector->pushEvent(['name' => 'testMethod', 'args' => ['arg1']]);
        $connector->updateEventParameters('testMethod', ['arg2', 'arg3']);

        $events = $connector->getEvents();
        $testEvent = collect($events)->firstWhere('name', 'testMethod');

        $this->assertContains('arg1', $testEvent['args']);
        $this->assertContains('arg2', $testEvent['args']);
        $this->assertContains('arg3', $testEvent['args']);
    }

    /** @test */
    public function it_returns_module()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $module = $connector->getModule();

        $this->assertNotNull($module);
        $this->assertEquals('TestModule', $module->getName());
    }

    /** @test */
    public function it_returns_module_name()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertEquals('TestModule', $connector->getModuleName());
    }

    /** @test */
    public function it_returns_route_name()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertEquals('Item', $connector->getRouteName());
    }

    /** @test */
    public function it_returns_target()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $target = $connector->getTarget();

        $this->assertNotNull($target);
    }

    /** @test */
    public function it_returns_target_type_key()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertEquals('endpoint', $connector->getTargetTypeKey());
    }

    /** @test */
    public function it_identifies_link_target_for_uri()
    {
        $connector = new Connector('TestModule|Item^uri');

        $this->assertTrue($connector->isLinkTarget());
    }

    /** @test */
    public function it_identifies_link_target_for_url()
    {
        $connector = new Connector('TestModule|Item^url');

        $this->assertTrue($connector->isLinkTarget());
    }

    /** @test */
    public function it_identifies_link_target_for_endpoint()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $this->assertTrue($connector->isLinkTarget());
    }

    /** @test */
    public function it_runs_connector_with_array_item()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $item = ['existing' => 'value'];
        $connector->run($item);

        $this->assertArrayHasKey('endpoint', $item);
    }

    /** @test */
    public function it_runs_connector_with_object_item()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $item = (object) ['existing' => 'value'];
        $connector->run($item);

        $this->assertObjectHasProperty('endpoint', $item);
    }

    /** @test */
    public function it_runs_connector_with_collection_item()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $item = collect(['existing' => 'value']);
        $connector->run($item);

        $this->assertTrue(isset($item->endpoint));
    }

    /** @test */
    public function it_runs_connector_with_custom_set_key()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $item = [];
        $connector->run($item, 'customKey');

        $this->assertArrayHasKey('customKey', $item);
    }

    /** @test */
    public function it_returns_result_from_run()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $item = [];
        $result = $connector->run($item);

        $this->assertNotNull($result);
    }

    /** @test */
    public function it_can_get_repository()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $repository = $connector->getRepository(false);

        $this->assertNotNull($repository);
    }

    /** @test */
    public function it_can_get_repository_class()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $repositoryClass = $connector->getRepository(true);

        $this->assertInstanceOf(Repository::class, $repositoryClass);
    }

    /** @test */
    public function it_can_get_model()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $model = $connector->getModel(false);

        $this->assertNotNull($model);
    }

    /** @test */
    public function it_can_get_model_class()
    {
        $connector = new Connector('TestModule|Item^endpoint');

        $modelClass = $connector->getModel(true);

        $this->assertInstanceOf(Item::class, $modelClass);
    }

    /** @test */
    public function it_parses_model_with_query_builder_chain()
    {
        $connector = new Connector('TestModule|Item^model->query->where?id=1');

        $events = $connector->getEvents();

        // Should have 2 events: query() and where()
        $this->assertCount(2, $events);
        $this->assertEquals('query', $events[0]['name']);
        $this->assertEquals('where', $events[1]['name']);
        $this->assertEquals(['id' => '1'], $events[1]['args']);
    }

    /** @test */
    public function it_parses_repository_method_with_parameters()
    {
        $connector = new Connector('TestModule|Item^repository->list?column=name&scopes=[enabled]');

        $events = $connector->getEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('list', $events[0]['name']);
        $this->assertEquals('name', $events[0]['args']['column']);
        $this->assertEquals(['enabled'], $events[0]['args']['scopes']);
    }

    /** @test */
    public function it_parses_multiple_method_chain()
    {
        $connector = new Connector('TestModule|Item^model->query->where?status=active->orderBy?created_at=desc');

        $events = $connector->getEvents();

        $this->assertCount(3, $events);
        $this->assertEquals('query', $events[0]['name']);
        $this->assertEquals('where', $events[1]['name']);
        $this->assertEquals('orderBy', $events[2]['name']);
    }

    /** @test */
    public function it_parses_array_parameters_with_escaped_commas()
    {
        $connector = new Connector('TestModule|Item^repository->find?columns=[id,name,status]');

        $events = $connector->getEvents();

        $this->assertEquals('find', $events[0]['name']);
        $this->assertIsArray($events[0]['args']['columns']);
        $this->assertContains('id', $events[0]['args']['columns']);
        $this->assertContains('name', $events[0]['args']['columns']);
        $this->assertContains('status', $events[0]['args']['columns']);
    }

    /** @test */
    public function it_parses_object_notation_parameters()
    {
        $connector = new Connector('TestModule|Item^repository->update?data={name:test,status:active}');

        $events = $connector->getEvents();

        $this->assertEquals('update', $events[0]['name']);
        $this->assertIsArray($events[0]['args']['data']);
        $this->assertEquals('test', $events[0]['args']['data']['name']);
        $this->assertEquals('active', $events[0]['args']['data']['status']);
    }

    /** @test */
    public function it_not_found_class()
    {
        $this->expectExceptionMessageMatches('/not found for connector TestModule\|Item\^trial/');
        new Connector('TestModule|Item^trial');
    }

    /** @test */
    public function it_parses_ordered_arguments()
    {
        $connector = new Connector('TestModule|Item^repository->method?arg1&arg2&arg3');

        $events = $connector->getEvents();

        $this->assertEquals('method', $events[0]['name']);
        $this->assertEquals('arg1', $events[0]['args'][0]);
        $this->assertEquals('arg2', $events[0]['args'][1]);
        $this->assertEquals('arg3', $events[0]['args'][2]);
    }

    /** @test */
    public function it_throws_exception_for_mixed_argument_types()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Both ordered and named arguments are not allowed');

        new Connector('TestModule|Item^repository->method?key=value&orderedArg');
    }

    /** @test */
    public function it_throws_exception_for_mixed_argument_types_first_ordered()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Both ordered and named arguments are not allowed');

        new Connector('TestModule|Item^repository->method?orderedArg&key=value');
    }

    /** @test */
    public function it_parses_connector_without_second_part()
    {
        $connector = new Connector('TestModule|Item');

        $events = $connector->getEvents();

        // Should create default uri event
        $this->assertCount(1, $events);
        $this->assertEquals('uri', $events[0]['name']);
        $this->assertEquals(['index'], $events[0]['args']);
    }

    /** @test */
    public function it_handles_complex_query_builder_pattern()
    {
        $connector = new Connector('TestModule|Item^model->query->where?status=active&type=user->orderBy?created_at=desc->limit?count=10');

        $events = $connector->getEvents();

        // query, where, orderBy, limit
        $this->assertCount(4, $events);
        $this->assertEquals('query', $events[0]['name']);
        $this->assertEquals('where', $events[1]['name']);
        $this->assertEquals(['status' => 'active', 'type' => 'user'], $events[1]['args']);
        $this->assertEquals('orderBy', $events[2]['name']);
        $this->assertEquals(['created_at' => 'desc'], $events[2]['args']);
        $this->assertEquals('limit', $events[3]['name']);
        $this->assertEquals(['count' => '10'], $events[3]['args']);
    }

    /** @test */
    public function it_runs_connector_and_executes_event_chain()
    {
        // This tests the run() method actually executes the event chain
        $connector = new Connector('TestModule|Item^endpoint');

        $item = [];
        $result = $connector->run($item);

        // The result should be from executing getRouteActionUrl on the module
        $this->assertNotNull($result);
        $this->assertArrayHasKey('endpoint', $item);
        $this->assertEquals($result, $item['endpoint']);
    }
}

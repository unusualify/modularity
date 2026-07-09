<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route as RoutingRoute;
use Mockery;
use Unusualify\Modularous\Entities\Enums\AssignmentStatus;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Facades\HostRoutingRegistrar;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\Http\Controllers\Stubs\CoreControllerStub;
use Unusualify\Modularous\Tests\TestModulesCase;

class CoreControllerTest extends TestModulesCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_constructor_resolves_module_and_repository(): void
    {
        $controller = new CoreControllerStub($this->app, Request::create('/'));

        $this->assertSame('TestModule', $controller->getModuleName());
        $this->assertSame('Item', $controller->getRouteName());
        $this->assertNotNull($controller->getModule());
        $this->assertInstanceOf(Repository::class, $controller->getRepository());
    }

    public function test_preload_loads_module_config(): void
    {
        $controller = new CoreControllerStub($this->app, Request::create('/'));
        $controller->exposePreload();

        $this->assertNotNull($controller->exposeGetConfigFieldsByRouteRaw('missing', 'fallback'));
    }

    public function test_get_transformer_returns_raw_data_when_transformer_is_missing(): void
    {
        $controller = new CoreControllerStub($this->app, Request::create('/'));

        $this->assertSame(['id' => 1], $controller->exposeGetTransformer(['id' => 1]));
        $this->assertNull($controller->exposeGetTransformerClass());
    }

    public function test_get_config_fields_by_route_reads_raw_and_object_config(): void
    {
        $controller = new CoreControllerStub($this->app, Request::create('/'));
        $controller->exposePreload();

        $this->assertSame('fallback', $controller->exposeGetConfigFieldsByRoute('missing', 'fallback'));
        $this->assertSame('fallback', $controller->exposeGetConfigFieldsByRouteRaw('missing', 'fallback'));
    }

    public function test_route_argument_helpers_map_route_parameters(): void
    {
        HostRoutingRegistrar::shouldReceive('getRouteArguments')->andReturn([]);

        $request = $this->requestWithRoute(['item' => 7, 'category' => 3]);
        $controller = new CoreControllerStub($this->app, $request);

        $this->assertSame(['Item' => 7, 'Category' => 3], $controller->exposeRouteModuleArguments());
        $this->assertSame(7, $controller->exposeRouteArgument());
        $this->assertSame(['Category' => 3], $controller->exposeParentRouteArguments());
    }

    public function test_route_has_delegates_to_repository_behavior_checks(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('hasBehavior')->with('tags')->andReturn(true);

        $controller = new CoreControllerStub($this->app, Request::create('/'), $repository);

        $this->assertTrue($controller->exposeRouteHas('tags'));
    }

    public function test_tags_returns_tag_names_as_json(): void
    {
        $tag = (object) ['name' => 'Press'];
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getTags')->with('news')->andReturn(collect([$tag]));

        $request = Request::create('/tags', 'GET', ['q' => 'news']);
        $controller = new CoreControllerStub($this->app, $request, $repository);

        $response = $controller->tags();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['Press'], $payload['resource']['data']);
    }

    public function test_tags_defaults_null_query_to_empty_string(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getTags')->with('')->andReturn(collect());

        $controller = new CoreControllerStub($this->app, Request::create('/tags'), $repository);
        $response = $controller->tags();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_tags_update_creates_tag_and_returns_success_payload(): void
    {
        $createdTag = (object) ['id' => 12];
        $tagModel = Mockery::mock();
        $tagModel->shouldReceive('create')->once()->andReturn($createdTag);

        $model = Mockery::mock();
        $model->shouldReceive('createTagsModel')->once()->andReturn($tagModel);

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getModel')->andReturn($model);

        $request = Request::create('/tags', 'POST', ['value' => 'Breaking News']);
        $controller = new CoreControllerStub($this->app, $request, $repository);

        $response = $controller->tagsUpdate();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(12, $payload['id']);
        $this->assertSame('Tag created successfully', $payload['message']);
    }

    public function test_assignments_returns_repository_payload(): void
    {
        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getAssignments')->with(4)->andReturn(['rows' => []]);

        $controller = new CoreControllerStub($this->app, Request::create('/'), $repository);

        $this->assertSame(['rows' => []], $controller->assignments(4)->getData(true));
    }

    public function test_create_assignment_updates_status_when_status_is_present(): void
    {
        $lastAssignment = Mockery::mock();
        $lastAssignment->shouldReceive('update')->once()->andReturn(true);
        $lastAssignment->shouldReceive('wasChanged')->andReturn(true);

        $assignable = Mockery::mock();
        $assignable->lastAssignment = $lastAssignment;
        $assignable->shouldReceive('touch')->once();

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->with(1)->andReturn($assignable);
        $repository->shouldReceive('getAssignments')->with(1)->andReturn(['updated' => true]);

        $request = Request::create('/assignments/1', 'POST', ['status' => 'completed']);
        $controller = new CoreControllerStub($this->app, $request, $repository);

        $response = $controller->createAssignment(1);
        $payload = $response->getData(true);

        $this->assertSame('Assignment updated successfully!', $payload['message']);
        $this->assertSame(['updated' => true], $payload['assignments']);
    }

    public function test_create_assignment_saves_attachments_when_present(): void
    {
        $lastAssignment = (object) ['id' => 3];
        $assignable = Mockery::mock();
        $assignable->lastAssignment = $lastAssignment;
        $assignable->shouldReceive('touch')->once();

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->with(2)->andReturn($assignable);
        $repository->shouldReceive('getAssignments')->with(2)->andReturn([]);

        Filepond::shouldReceive('saveFile')
            ->once()
            ->with($lastAssignment, ['file-1'], 'attachments');

        $request = Request::create('/assignments/2', 'POST', ['attachments' => ['file-1']]);
        $controller = new CoreControllerStub($this->app, $request, $repository);

        $response = $controller->createAssignment(2);

        $this->assertSame('Attachments saved successfully!', $response->getData(true)['message']);
    }

    public function test_create_assignment_creates_new_assignment_when_payload_is_valid(): void
    {
        $lastAssignment = Mockery::mock();
        $lastAssignment->status = AssignmentStatus::COMPLETED;

        $assignment = Mockery::mock();
        $assignment->assignee_id = 5;
        $assignment->description = 'Review copy';
        $assignment->shouldReceive('refresh')->once();

        $relation = Mockery::mock();
        $relation->shouldReceive('create')->once()->andReturn($assignment);

        $assignable = Mockery::mock();
        $assignable->lastAssignment = $lastAssignment;
        $assignable->shouldReceive('assignments')->andReturn($relation);
        $assignable->shouldReceive('touch')->once();

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->with(9)->andReturn($assignable);

        Filepond::shouldReceive('saveFile')
            ->once()
            ->with($assignment, ['prelim'], 'preliminaries');

        $request = Request::create('/assignments/9', 'POST', [
            'assignee_id' => 5,
            'assignee_type' => 'user',
            'assignable_id' => 9,
            'assignable_type' => 'item',
            'description' => 'Review copy',
            'due_at' => '2026-07-10',
            'preliminaries' => ['prelim'],
        ]);

        $controller = new CoreControllerStub($this->app, $request, $repository);
        $response = $controller->createAssignment(9);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('assignee_id', $response->getData(true));
    }

    public function test_create_assignment_cancels_open_assignment_before_creating_new_one(): void
    {
        $lastAssignment = Mockery::mock();
        $lastAssignment->status = AssignmentStatus::PENDING;
        $lastAssignment->shouldReceive('updateQuietly')->once()->with([
            'status' => AssignmentStatus::CANCELLED,
        ]);

        $assignment = Mockery::mock();
        $assignment->shouldReceive('refresh')->once();

        $relation = Mockery::mock();
        $relation->shouldReceive('create')->once()->andReturn($assignment);

        $assignable = Mockery::mock();
        $assignable->lastAssignment = $lastAssignment;
        $assignable->shouldReceive('assignments')->andReturn($relation);
        $assignable->shouldReceive('touch')->once();

        $repository = Mockery::mock(Repository::class);
        $repository->shouldReceive('getById')->with(11)->andReturn($assignable);

        $request = Request::create('/assignments/11', 'POST', [
            'assignee_id' => 2,
            'assignee_type' => 'user',
            'assignable_id' => 11,
            'assignable_type' => 'item',
            'description' => 'Follow up',
            'due_at' => '2026-07-11',
        ]);

        $controller = new CoreControllerStub($this->app, $request, $repository);
        $response = $controller->createAssignment(11);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_get_transformer_instantiates_single_resource(): void
    {
        $controller = new class($this->app, Request::create('/')) extends CoreControllerStub
        {
            protected function getTransformerClass(): ?string
            {
                return TestJsonResource::class;
            }
        };

        $result = $controller->exposeGetTransformer(['id' => 9, 'name' => 'Single']);

        $this->assertInstanceOf(TestJsonResource::class, $result);
    }

    public function test_get_transformer_wraps_paginator_in_collection(): void
    {
        $controller = new class($this->app, Request::create('/')) extends CoreControllerStub
        {
            protected function getTransformerClass(): ?string
            {
                return TestJsonResource::class;
            }
        };

        $paginator = new LengthAwarePaginator([['id' => 1]], 1, 10);
        $result = $controller->exposeGetTransformer($paginator);

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $result);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function requestWithRoute(array $parameters): Request
    {
        $request = Request::create('/admin/items/7', 'GET');
        $route = (new RoutingRoute('GET', '/admin/items/{item}', []));
        $route->bind($request);

        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(static fn () => $route);

        return $request;
    }
}

class TestJsonResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request): array
    {
        return (array) $this->resource;
    }
}

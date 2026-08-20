<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\View;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Traits\MakesResponses;
use Unusualify\Modularous\Http\Controllers\Traits\ManagePreview;
use Unusualify\Modularous\Services\MessageStage;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Traitify;

class ManagePreviewCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController(Request $request, $repository = null, bool $hasRevisions = true): object
    {
        $repository ??= Mockery::mock();

        return new class($request, $repository, $hasRevisions)
        {
            use ManagePreview;
            use MakesResponses;
            use Traitify;

            public $request;

            public $repository;

            public $module = true;

            public $moduleName = 'Package';

            public $formSchema = [];

            public bool $hasRevisions;

            public array $middlewarePermissions = [];

            public function __construct($request, $repository, bool $hasRevisions)
            {
                $this->request = $request;
                $this->repository = $repository;
                $this->hasRevisions = $hasRevisions;
            }

            protected function routeHasTrait($trait): bool
            {
                return $this->hasRevisions && $trait === 'revisions';
            }

            protected function setMiddlewarePermission($permission, $options = []): void
            {
                $this->middlewarePermissions[$permission] = $options;
            }

            protected function presentationViewName(): string
            {
                return 'modularous::preview.item';
            }

            protected function validateFormRequest($rules = null)
            {
                return Request::create('/', 'POST', ['title' => 'Preview']);
            }

            protected function getPreviousRouteSchema(): array
            {
                return ['title' => ['type' => 'text', 'name' => 'title']];
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    private function bindItemRoute(Request $request, int $itemId): Request
    {
        $route = new Route([$request->method()], '/items/{item}/action', []);
        $request = Request::create('/items/'.$itemId.'/action', $request->method(), $request->all());
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    /** @test */
    public function it_registers_revision_middleware_and_lists_revisions(): void
    {
        $controller = $this->makeController(Request::create('/'), null, true);
        $controller->call('addMiddlewarePermissionsManagePreview');
        $this->assertArrayHasKey('REVISION_RESTORE', $controller->middlewarePermissions);

        $object = Mockery::mock();
        $object->shouldReceive('revisionsArray')->once()->andReturn([['id' => 1]]);
        $query = Mockery::mock();
        $query->shouldReceive('findOrFail')->with(5)->andReturn($object);
        $model = Mockery::mock();
        $model->shouldReceive('newQuery')->andReturn($query);
        $repository = Mockery::mock();
        $repository->shouldReceive('getModel')->andReturn($model);

        $controller = $this->makeController(Request::create('/'), $repository, true);
        $this->assertSame([['id' => 1]], $controller->listRevisions(5));

        $disabled = $this->makeController(Request::create('/'), null, false);
        $response = $disabled->listRevisions(1);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /** @test */
    public function show_view_uses_revision_preview_and_missing_view_fallback(): void
    {
        $item = new class extends Model
        {
            protected $table = 'items';
        };

        $repository = Mockery::mock();
        $repository->shouldReceive('previewForRevision')->once()->with(3, '9', [])->andReturn($item);

        $request = Request::create('/preview/3', 'GET', [
            'revisionId' => 9,
            'activeLanguage' => 'tr',
        ]);

        $view = Mockery::mock(ViewContract::class);
        // Wrapper probes page_layout/{head,body,footer} candidates before falling back.
        View::shouldReceive('exists')->andReturn(false);
        View::shouldReceive('make')
            ->once()
            ->with('twill::errors.preview', Mockery::type('array'))
            ->andReturn($view);

        $controller = $this->makeController($request, $repository);
        $this->assertSame($view, $controller->showView(3));
        $this->assertSame('tr', app()->getLocale());
    }

    /** @test */
    public function restore_approve_reject_cover_validation_and_success_paths(): void
    {
        $item = Mockery::mock();
        $item->shouldReceive('revisionsArray')->andReturn([['id' => 2]]);

        $repository = Mockery::mock();
        $repository->shouldReceive('getRevisionPayload')->once()->with(4, 2)->andReturn(['title' => 'Old']);
        $repository->shouldReceive('restoreRevision')->once()->with(4, 2)->andReturn($item);
        $repository->shouldReceive('approveRevision')->once()->with(4, 2)->andReturn($item);
        $repository->shouldReceive('rejectRevision')->once()->with(4, 2)->andReturn($item);
        $repository->shouldReceive('getFormFields')->andReturn(['title' => 'Old']);

        $previewRequest = $this->bindItemRoute(
            Request::create('/items/4/action', 'POST', ['revisionId' => 2, 'preview' => 1]),
            4
        );
        $controller = $this->makeController($previewRequest, $repository);
        $preview = $controller->restoreRevision(4);
        $this->assertSame(['title' => 'Old'], $preview->getData(true)['form_fields']);

        $restoreRequest = $this->bindItemRoute(
            Request::create('/items/4/action', 'POST', ['revisionId' => 2]),
            4
        );
        $controller = $this->makeController($restoreRequest, $repository);
        $restored = $controller->restoreRevision(4);
        $this->assertSame(MessageStage::SUCCESS->value, $restored->getData(true)['variant']);

        $approveRequest = $this->bindItemRoute(
            Request::create('/items/4/action', 'POST', ['revisionId' => 2]),
            4
        );
        $controller = $this->makeController($approveRequest, $repository);
        $this->assertSame(MessageStage::SUCCESS->value, $controller->approveRevision(4)->getData(true)['variant']);

        $rejectRequest = $this->bindItemRoute(
            Request::create('/items/4/action', 'POST', ['revisionId' => 2]),
            4
        );
        $controller = $this->makeController($rejectRequest, $repository);
        $this->assertSame(MessageStage::SUCCESS->value, $controller->rejectRevision(4)->getData(true)['variant']);

        $missingId = $this->bindItemRoute(
            Request::create('/items/4/action', 'POST', ['revisionId' => 0]),
            4
        );
        $controller = $this->makeController($missingId, $repository);
        $this->assertInstanceOf(JsonResponse::class, $controller->restoreRevision(4));
        $this->assertInstanceOf(JsonResponse::class, $controller->approveRevision(4));
        $this->assertInstanceOf(JsonResponse::class, $controller->rejectRevision(4));
    }
}

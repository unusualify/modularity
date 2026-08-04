<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\ErrorPage;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ErrorPage\Providers\ErrorPageServiceProvider;
use Modules\ErrorPage\Support\ErrorPageRenderer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Unusualify\Modularous\Tests\TestCase;

final class ErrorPageRenderableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['modularous.cms_features.error_pages_enabled' => true]);

        $this->app->register(ErrorPageServiceProvider::class);
    }

    public function test_frontend_404_invokes_error_page_renderer(): void
    {
        $stub = new class
        {
            public int $calls = 0;

            public function toResponse(int $status, $request): Response
            {
                $this->calls++;

                return new Response('error-page-frontend', $status);
            }
        };
        $this->app->instance(ErrorPageRenderer::class, $stub);

        $request = Request::create('http://localhost/missing-page', 'GET');
        $this->app->instance('request', $request);

        $response = $this->app->make(ExceptionHandlerContract::class)
            ->render($request, new NotFoundHttpException);

        $this->assertSame(1, $stub->calls);
        $this->assertSame('error-page-frontend', $response->getContent());
        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_panel_url_skips_error_page_renderer(): void
    {
        $stub = new class
        {
            public int $calls = 0;

            public function toResponse(int $status, $request): Response
            {
                $this->calls++;

                return new Response('error-page-panel', $status);
            }
        };
        $this->app->instance(ErrorPageRenderer::class, $stub);

        $request = Request::create('http://localhost/admin/dashboard', 'GET');
        $this->app->instance('request', $request);

        $response = $this->app->make(ExceptionHandlerContract::class)
            ->render($request, new NotFoundHttpException);

        $this->assertSame(0, $stub->calls);
        $this->assertNotSame('error-page-panel', $response->getContent());
    }
}

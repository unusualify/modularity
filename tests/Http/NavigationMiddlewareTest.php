<?php

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Unusualify\Modularous\Http\Middleware\NavigationMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class NavigationMiddlewareTest extends TestCase
{
    public function test_handle_registers_navigation_view_composer(): void
    {
        View::composer([
            modularousBaseKey() . '::layouts.*',
            'translation::layout',
        ], function () {
        });

        $middleware = new NavigationMiddleware;
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }
}

<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Unusualify\Modularous\Facades\Utm;
use Unusualify\Modularous\Http\Middleware\UtmMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class UtmMiddlewareTest extends TestCase
{
    public function test_registers_utm_view_composer_and_passes_request(): void
    {
        Utm::shouldReceive('getParameters')->once()->andReturn(['utm_source' => 'newsletter']);

        $middleware = new UtmMiddleware;
        $request = Request::create('/landing?utm_source=newsletter', 'GET');

        $response = $middleware->handle($request, fn () => response('utm-ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('utm-ok', $response->getContent());
    }
}

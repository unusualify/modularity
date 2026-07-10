<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Middleware\HostableMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class HostableMiddlewareTest extends TestCase
{
    public function test_passes_request_through_unchanged(): void
    {
        $middleware = new HostableMiddleware;
        $request = Request::create('/example', 'GET');

        $response = $middleware->handle($request, fn ($passed) => response('ok', 200, ['X-Test' => '1']));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
        $this->assertSame('1', $response->headers->get('X-Test'));
    }
}

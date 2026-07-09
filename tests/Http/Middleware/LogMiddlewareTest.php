<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Unusualify\Modularous\Facades\ModularousLog;
use Unusualify\Modularous\Http\Middleware\LogMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class LogMiddlewareTest extends TestCase
{
    public function test_adds_request_id_header_to_response(): void
    {
        ModularousLog::shouldReceive('withContext')
            ->once()
            ->with(\Mockery::on(fn (array $context) => isset($context['request_id']) && $context['request_id'] !== ''));

        $middleware = new LogMiddleware;
        $request = Request::create('/tracked', 'GET');

        $response = $middleware->handle($request, fn () => response('tracked'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($response->headers->get('Request-Id'));
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            (string) $response->headers->get('Request-Id')
        );
    }
}

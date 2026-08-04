<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Middleware\TimezoneMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class TimezoneMiddlewareTest extends TestCase
{
    public function test_sets_timezone_from_x_timezone_header(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('X-Timezone', 'Europe/Istanbul');

        $middleware = new TimezoneMiddleware;
        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('Europe/Istanbul', date_default_timezone_get());
        $this->assertSame('Europe/Istanbul', session('modularous_timezone'));
    }

    public function test_sets_timezone_from_browser_cookie(): void
    {
        $_COOKIE['timezone'] = 'America/New_York';

        try {
            $request = Request::create('/dashboard', 'GET');
            $middleware = new TimezoneMiddleware;
            $middleware->handle($request, fn () => response('ok'));

            $this->assertSame('America/New_York', date_default_timezone_get());
            $this->assertSame('America/New_York', session('modularous_timezone'));
        } finally {
            unset($_COOKIE['timezone']);
        }
    }

    public function test_ignores_invalid_timezone(): void
    {
        $previous = date_default_timezone_get();

        $request = Request::create('/dashboard', 'GET');
        $request->headers->set('X-Timezone', 'Not/A_Real_Zone');

        $middleware = new TimezoneMiddleware;
        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame($previous, date_default_timezone_get());
        $this->assertNull(session('modularous_timezone'));
    }

    public function test_falls_back_to_session_timezone(): void
    {
        session()->put('modularous_timezone', 'Europe/Berlin');

        $request = Request::create('/dashboard', 'GET');
        $middleware = new TimezoneMiddleware;
        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('Europe/Berlin', date_default_timezone_get());
    }
}

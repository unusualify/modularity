<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Middleware\DevAutoLoginMiddleware;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;
use Unusualify\Modularous\Tests\TestCase;

class DevAutoLoginMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        Modularous::swap($modularous);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_authenticates_guest_as_resolved_superadmin_when_enabled(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('guest')->once()->andReturn(true);
        $guard->shouldReceive('onceUsingId')->once()->with(7);

        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn(true);
        $service->shouldReceive('resolveUserId')->once()->andReturn(7);

        $response = $this->middleware($guard, $service)->handle(Request::create('/admin'), fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_marks_response_with_dev_auto_login_header(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('guest')->once()->andReturn(true);
        $guard->shouldReceive('onceUsingId')->once()->with(7)->andReturn((object) ['email' => 'dev@example.test']);

        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn(true);
        $service->shouldReceive('resolveUserId')->once()->andReturn(7);

        $response = $this->middleware($guard, $service)->handle(Request::create('/admin'), fn () => response('ok'));

        $this->assertSame('dev@example.test', $response->headers->get(DevAutoLoginMiddleware::HEADER));
    }

    public function test_keeps_existing_authenticated_user(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('guest')->once()->andReturn(false);
        $guard->shouldNotReceive('onceUsingId');

        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn(true);
        $service->shouldNotReceive('resolveUserId');

        $response = $this->middleware($guard, $service)->handle(Request::create('/admin'), fn () => response('ok'));

        $this->assertFalse($response->headers->has(DevAutoLoginMiddleware::HEADER));
    }

    public function test_does_nothing_when_disabled(): void
    {
        $guard = Mockery::mock();
        $guard->shouldNotReceive('guest');
        $guard->shouldNotReceive('onceUsingId');

        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn(false);

        $this->middleware($guard, $service)->handle(Request::create('/admin'), fn () => response('ok'));

        $this->assertTrue(true);
    }

    public function test_skips_login_when_no_superadmin_is_found(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('guest')->once()->andReturn(true);
        $guard->shouldNotReceive('onceUsingId');

        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn(true);
        $service->shouldReceive('resolveUserId')->once()->andReturn(null);

        $this->middleware($guard, $service)->handle(Request::create('/admin'), fn () => response('ok'));

        $this->assertTrue(true);
    }

    private function middleware(object $guard, DevAutoLoginService $service): DevAutoLoginMiddleware
    {
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('modularous')->andReturn($guard);

        return new DevAutoLoginMiddleware($auth, $service);
    }
}

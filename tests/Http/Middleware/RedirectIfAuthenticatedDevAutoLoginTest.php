<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Mockery;
use Unusualify\Modularous\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;
use Unusualify\Modularous\Tests\TestCase;

class RedirectIfAuthenticatedDevAutoLoginTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_guest_page_redirects_when_dev_auto_login_resolves_a_user(): void
    {
        $this->bindDevAutoLogin(enabled: true, userId: 1);

        $response = $this->middleware()->handle(Request::create('/login'), fn () => response('login form'));

        $this->assertTrue($response->isRedirect());
    }

    public function test_guest_page_renders_when_dev_auto_login_is_disabled(): void
    {
        $this->bindDevAutoLogin(enabled: false, userId: null);

        $response = $this->middleware()->handle(Request::create('/login'), fn () => response('login form'));

        $this->assertSame('login form', $response->getContent());
    }

    public function test_guest_page_renders_when_no_dev_user_is_found(): void
    {
        $this->bindDevAutoLogin(enabled: true, userId: null);

        $response = $this->middleware()->handle(Request::create('/login'), fn () => response('login form'));

        $this->assertSame('login form', $response->getContent());
    }

    private function bindDevAutoLogin(bool $enabled, ?int $userId): void
    {
        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('isEnabled')->andReturn($enabled);
        $service->shouldReceive('resolveUserId')->andReturn($userId);

        $this->app->instance(DevAutoLoginService::class, $service);
    }

    private function middleware(): RedirectIfAuthenticatedMiddleware
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('check')->andReturn(false);

        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->andReturn($guard);

        return new RedirectIfAuthenticatedMiddleware($auth, $this->app->make(Redirector::class), $this->app->make(Config::class));
    }
}

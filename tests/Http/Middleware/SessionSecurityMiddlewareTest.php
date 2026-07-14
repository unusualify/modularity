<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Http\Middleware\SessionSecurityMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class SessionSecurityMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/admin/login', fn () => 'login')->name('admin.login.form');
    }

    public function test_passes_through_when_security_disabled(): void
    {
        Config::set('modularous.security.enabled', false);

        $middleware = new SessionSecurityMiddleware;
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, fn () => response('allowed'));

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_passes_through_for_guest_when_security_enabled(): void
    {
        Config::set('modularous.security.enabled', true);
        Auth::shouldReceive('check')->andReturn(false);

        $middleware = new SessionSecurityMiddleware;
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, fn () => response('guest'));

        $this->assertSame('guest', $response->getContent());
    }

    public function test_logs_out_on_idle_timeout_for_json_requests(): void
    {
        Config::set('modularous.security.enabled', true);
        Config::set('modularous.security.session.idle_timeout_minutes', 30);

        $user = new User;
        $user->id = 5;

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('logout')->once();
        Auth::shouldReceive('user')->andReturn($user);

        $middleware = new SessionSecurityMiddleware;
        $request = Request::create('/api/resource', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('security_last_seen_at', time() - 3600);

        $response = $middleware->handle($request, fn () => response('should-not-reach'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Session timed out. Please login again.', $response->getData(true)['message']);
    }

    public function test_updates_last_seen_timestamp_for_active_session(): void
    {
        Config::set('modularous.security.enabled', true);
        Config::set('modularous.security.session.idle_timeout_minutes', 60);

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn(new User);

        $middleware = new SessionSecurityMiddleware;
        $request = Request::create('/admin', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('security_last_seen_at', time() - 30);

        $middleware->handle($request, fn () => response('active'));

        $this->assertGreaterThan(time() - 5, (int) $request->session()->get('security_last_seen_at'));
    }

    public function test_redirects_to_login_when_session_times_out_for_html_requests(): void
    {
        Config::set('modularous.security.enabled', true);
        Config::set('modularous.security.session.idle_timeout_minutes', 30);

        Route::get('/admin/login', fn () => 'login')->name('admin.login.form');

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('logout')->once();
        Auth::shouldReceive('user')->andReturn(new User);

        $middleware = new SessionSecurityMiddleware;
        $request = Request::create('/admin/dashboard', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('security_last_seen_at', time() - 3600);

        $response = $middleware->handle($request, fn () => response('should-not-reach'));

        $this->assertTrue($response->isRedirect(route('admin.login.form')));
        $this->assertSame(
            'Session timed out. Please login again.',
            $response->getSession()->get('errors')->get('session')[0]
        );
    }
}

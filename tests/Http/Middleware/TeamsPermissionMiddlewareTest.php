<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Http\Middleware\TeamsPermissionMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class TeamsPermissionMiddlewareTest extends TestCase
{
    public function test_sets_permissions_team_id_when_user_is_authenticated(): void
    {
        $user = new User;
        $user->id = 42;
        $user->email = 'team-user@example.com';

        Auth::shouldReceive('user')->andReturn($user);

        $middleware = new TeamsPermissionMiddleware;
        $request = Request::create('/admin', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('team_id', 7);

        $response = $middleware->handle($request, fn () => response('team-ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(7, getPermissionsTeamId());
    }

    public function test_passes_through_when_guest(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $middleware = new TeamsPermissionMiddleware;
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, fn () => response('guest-ok'));

        $this->assertSame('guest-ok', $response->getContent());
    }
}

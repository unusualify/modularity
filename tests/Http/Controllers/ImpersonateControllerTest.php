<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Mockery;
use Modules\SystemUser\Repositories\UserRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Utility\ImpersonateController;
use Unusualify\Modularous\Tests\TestCase;

class ImpersonateControllerTest extends TestCase
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

    public function test_impersonator_attribute_takes_precedence_over_guard_user(): void
    {
        $impersonatedUser = Mockery::mock();
        $impersonatedUser->shouldNotReceive('can');

        $impersonator = Mockery::mock();
        $impersonator->shouldReceive('can')->with('impersonate')->once()->andReturn(true);
        $impersonator->shouldReceive('setImpersonating')->once()->with(42);

        $request = $this->jsonRequest('/users/impersonate/42');
        $request->attributes->set('impersonator', $impersonator);

        $response = $this->controller($impersonatedUser)->impersonate($request, 42, $this->users(42));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(['impersonating' => true, 'user_id' => 42], $response->getData(true));
        $this->assertSame([42], Session::get('impersonate_recent'));
    }

    public function test_forbidden_when_actor_cannot_impersonate(): void
    {
        $actor = Mockery::mock();
        $actor->shouldReceive('can')->with('impersonate')->andReturn(false);
        $actor->shouldNotReceive('setImpersonating');

        $users = Mockery::mock(UserRepository::class);
        $users->shouldNotReceive('getById');

        try {
            $this->controller($actor)->impersonate($this->jsonRequest('/users/impersonate/42'), 42, $users);
            $this->fail('Expected a 403 HttpException.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_forbidden_when_no_user_is_authenticated(): void
    {
        $this->expectException(HttpException::class);

        $this->controller(null)->impersonate($this->jsonRequest('/users/impersonate/42'), 42, Mockery::mock(UserRepository::class));
    }

    public function test_stop_impersonate_returns_json_for_json_requests(): void
    {
        $actor = Mockery::mock();
        $actor->shouldReceive('stopImpersonating')->once();

        $request = $this->jsonRequest('/users/impersonate/stop');
        $request->attributes->set('impersonator', $actor);

        $response = $this->controller(null)->stopImpersonate($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(['impersonating' => false], $response->getData(true));
    }

    public function test_stop_impersonate_redirects_back_for_browser_requests(): void
    {
        $actor = Mockery::mock();
        $actor->shouldReceive('stopImpersonating')->once();

        $request = Request::create('/users/impersonate/stop', 'POST');
        $request->headers->set('referer', 'https://example.test/admin');
        $this->app->instance('request', $request);

        $this->assertTrue($this->controller($actor)->stopImpersonate($request)->isRedirect());
    }

    private function controller(?object $guardUser): ImpersonateController
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('user')->andReturn($guardUser);

        $authManager = Mockery::mock(AuthManager::class);
        $authManager->shouldReceive('guard')->with('modularous')->andReturn($guard);

        return new ImpersonateController($authManager);
    }

    private function users(int $id): UserRepository
    {
        $users = Mockery::mock(UserRepository::class);
        $users->shouldReceive('getById')->once()->with($id)->andReturn((object) ['id' => $id]);

        return $users;
    }

    private function jsonRequest(string $uri): Request
    {
        $request = Request::create($uri, 'POST');
        $request->headers->set('Accept', 'application/json');

        return $request;
    }
}

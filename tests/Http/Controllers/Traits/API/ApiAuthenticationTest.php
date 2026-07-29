<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\API;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Traits\API\ApiAuthentication;
use Unusualify\Modularous\Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_handles_unauthenticated_api_requests(): void
    {
        $guard = Mockery::mock();
        $guard->shouldReceive('check')->andReturn(false);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('sanctum')->andReturn($guard);

        $controller = $this->makeController();

        $this->assertFalse($controller->callIsApiAuthenticated());
        $this->assertNull($controller->callGetApiUser());
        $this->assertSame(401, $controller->callRequireApiAuthentication()->getStatusCode());
        $this->assertFalse($controller->callHasApiPermission('edit'));
        $this->assertSame(403, $controller->callRequireApiPermission('edit')->getStatusCode());
    }

    /** @test */
    public function it_handles_authenticated_api_permissions(): void
    {
        $user = new class extends Authenticatable
        {
            public function can($abilities, $arguments = []): bool
            {
                return $abilities === 'edit';
            }
        };

        $guard = Mockery::mock();
        $guard->shouldReceive('check')->andReturn(true);
        $guard->shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('guard')->with('sanctum')->andReturn($guard);

        $controller = $this->makeController();

        $this->assertTrue($controller->callIsApiAuthenticated());
        $this->assertSame($user, $controller->callGetApiUser());
        $this->assertNull($controller->callRequireApiAuthentication());
        $this->assertTrue($controller->callHasApiPermission('edit'));
        $this->assertNull($controller->callRequireApiPermission('edit'));
        $this->assertFalse($controller->callHasApiPermission('delete'));
        $this->assertSame(403, $controller->callRequireApiPermission('delete')->getStatusCode());
    }

    private function makeController(): object
    {
        return new class
        {
            use ApiAuthentication;

            public function respondUnauthorized(): JsonResponse
            {
                return response()->json(['message' => 'unauthorized'], 401);
            }

            public function respondForbidden(): JsonResponse
            {
                return response()->json(['message' => 'forbidden'], 403);
            }

            public function callIsApiAuthenticated(): bool
            {
                return $this->isApiAuthenticated();
            }

            public function callGetApiUser()
            {
                return $this->getApiUser();
            }

            public function callRequireApiAuthentication()
            {
                return $this->requireApiAuthentication();
            }

            public function callHasApiPermission(string $permission): bool
            {
                return $this->hasApiPermission($permission);
            }

            public function callRequireApiPermission(string $permission)
            {
                return $this->requireApiPermission($permission);
            }
        };
    }
}

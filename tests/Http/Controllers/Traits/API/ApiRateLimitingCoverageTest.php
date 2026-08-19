<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\API\ApiRateLimiting;
use Unusualify\Modularous\Tests\TestCase;

class ApiRateLimitingCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController(?Request $request = null, $user = null): object
    {
        $request ??= Request::create('/api/items', 'GET', [], [], [], ['HTTP_HOST' => 'api.example.test']);

        return new class($request, $user)
        {
            use ApiRateLimiting;

            public Request $request;

            public $apiUser;

            public function __construct(Request $request, $apiUser)
            {
                $this->request = $request;
                $this->apiUser = $apiUser;
            }

            protected function getApiUser()
            {
                return $this->apiUser;
            }

            protected function respondWithError(string $message, int $status = 400, array $errors = []): JsonResponse
            {
                return response()->json(['message' => $message, 'errors' => $errors], $status);
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function getters_and_keys_respect_config_and_user(): void
    {
        config([
            'modularous.api.rate_limiting.enabled' => true,
            'modularous.api.rate_limiting.per_minute' => 30,
            'modularous.api.rate_limiting.per_hour' => 500,
            'modularous.api.rate_limiting.blocking_time' => 120,
            'modularous.api.rate_limiting.blocking_maximum_attempts' => 10,
            'modularous.api.rate_limiting.blocking_time_threshold' => 60,
        ]);

        $user = (object) ['id' => 42];
        $controller = $this->makeController(null, $user);

        $this->assertTrue($controller->call('isRateLimitingEnabled'));
        $this->assertSame(30, $controller->call('getRateLimit'));
        $this->assertSame(500, $controller->call('getRateLimitPerHour'));
        $this->assertSame(120, $controller->call('getRateLimitBlockingTime'));
        $this->assertSame(10, $controller->call('getBlockingMaximumAttempts'));
        $this->assertSame(60, $controller->call('getBlockingTimeThreshold'));
        $this->assertSame('api_rate_limit_minute:42', $controller->call('getRateLimitKey', 'minute'));

        $guest = $this->makeController();
        $this->assertSame('api_rate_limit_minute:127.0.0.1', $guest->call('getRateLimitKey', 'minute'));
    }

    /** @test */
    public function blocking_rate_limit_headers_and_apply_paths(): void
    {
        config(['modularous.api.rate_limiting.enabled' => true]);

        RateLimiter::clear('api_rate_limit_blocked:127.0.0.1');
        RateLimiter::clear('api_rate_limit_minute:127.0.0.1');
        RateLimiter::clear('api_rate_limit_hour:127.0.0.1');
        RateLimiter::clear('api_rate_limit_blocking_threshold:127.0.0.1');

        $controller = $this->makeController();

        $this->assertFalse($controller->call('isUserBlocked'));
        $this->assertFalse($controller->call('isRateLimited'));
        $this->assertSame(0, $controller->call('getRemainingBlockTime'));

        $controller->call('blockUser');
        $this->assertTrue($controller->call('isUserBlocked'));
        $this->assertTrue($controller->call('isRateLimited'));
        $this->assertGreaterThan(0, $controller->call('getRemainingBlockTime'));

        $headers = $controller->call('getRateLimitHeaders');
        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Blocked', $headers);

        Modularous::shouldReceive('getAppHost')->andReturn('app.test');
        Modularous::shouldReceive('getAdminAppHost')->andReturn('admin.test');

        $blockedResponse = $controller->call('applyRateLimit');
        $this->assertInstanceOf(JsonResponse::class, $blockedResponse);
        $this->assertSame(429, $blockedResponse->getStatusCode());

        config(['modularous.api.rate_limiting.enabled' => false]);
        $disabled = $this->makeController();
        $this->assertFalse($disabled->call('isUserBlocked'));
        $this->assertFalse($disabled->call('isRateLimited'));
        $disabled->call('blockUser');
        $this->assertSame(0, $disabled->call('getRemainingBlockTime'));
        $this->assertSame($disabled->call('getRateLimit'), $disabled->call('getRemainingAttempts'));
        $this->assertSame(0, $disabled->call('getRateLimitResetTime'));
        $this->assertSame([], $disabled->call('getRateLimitHeaders'));
        $this->assertNull($disabled->call('applyRateLimit'));
    }

    /** @test */
    public function apply_rate_limit_skips_app_hosts_and_hits_limiters(): void
    {
        config(['modularous.api.rate_limiting.enabled' => true]);

        Modularous::shouldReceive('getAppHost')->andReturn('app.test');
        Modularous::shouldReceive('getAdminAppHost')->andReturn('admin.test');

        $appHostRequest = Request::create('/api', 'GET', [], [], [], ['HTTP_HOST' => 'app.test']);
        $appController = $this->makeController($appHostRequest);
        $this->assertNull($appController->call('applyRateLimit'));

        RateLimiter::clear('api_rate_limit_blocked:127.0.0.1');
        RateLimiter::clear('api_rate_limit_minute:127.0.0.1');
        RateLimiter::clear('api_rate_limit_hour:127.0.0.1');
        RateLimiter::clear('api_rate_limit_blocking_threshold:127.0.0.1');

        $external = $this->makeController();
        $this->assertNull($external->call('applyRateLimit'));
        $this->assertLessThanOrEqual($external->call('getRateLimit'), $external->call('getRemainingAttempts'));
        $this->assertIsInt($external->call('getRateLimitResetTime'));
        $this->assertArrayHasKey('X-RateLimit-Hourly-Limit', $external->call('getRateLimitHeaders'));
    }
}

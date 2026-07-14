<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Support\Facades\Http;
use Mockery;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiClient;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiConfiguration;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiLogger;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiLoggerTest extends TestCase
{
    public function test_logs_successful_http_request_with_structured_context(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::response([
                'data' => ['id' => 1, 'name' => 'Premium'],
            ]),
        ]);

        $logger = Mockery::mock(RemoteApiLogger::class);
        $logger->shouldReceive('logHttpRequest')
            ->once()
            ->withArgs(function (string $url, string $method, int $durationMs, ?int $status) {
                return $method === 'GET'
                    && str_contains($url, '/packages')
                    && $status === 200
                    && $durationMs >= 0;
            });

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
            logger: $logger,
        );

        $client->get('packages', ['page' => 1]);
    }

    public function test_logs_rate_limit_response_as_warning_context(): void
    {
        config(['modularous.remote_api.base_url' => 'http://app.b2press.test/api/v1']);

        Http::fake([
            'http://app.b2press.test/api/v1/packages*' => Http::response([], 429, ['Retry-After' => '30']),
        ]);

        $logger = Mockery::mock(RemoteApiLogger::class);
        $logger->shouldReceive('logHttpRequest')
            ->once()
            ->withArgs(function (string $url, string $method, int $durationMs, ?int $status, $tracker, $rateLimiter, $exception, ?int $retryAfter) {
                return $status === 429 && $retryAfter === 30;
            });

        $client = new RemoteApiClient(
            $this->makeConfiguration(),
            new RemoteApiRateLimiter(['enabled' => false]),
            logger: $logger,
        );

        try {
            $client->get('packages');
        } catch (\Throwable) {
            // expected
        }
    }

    private function makeConfiguration(): RemoteApiConfiguration
    {
        return new RemoteApiConfiguration($this->makeModule(), 'package', [
            'enabled' => true,
            'endpoint' => 'packages',
            'response' => [
                'list_path' => 'data.data',
                'item_path' => 'data',
                'meta_path' => 'data',
            ],
        ]);
    }

    private function makeModule(): Module
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getName')->andReturn('BusinessPackage');

        return $module;
    }
}

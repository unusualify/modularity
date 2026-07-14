<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Illuminate\Support\Facades\RateLimiter;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;
use Unusualify\Modularous\Services\RemoteApi\RemoteApiRateLimiter;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiRateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:minute');
        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:hour');
    }

    public function test_assert_can_request_allows_requests_under_limit(): void
    {
        $limiter = new RemoteApiRateLimiter([
            'enabled' => true,
            'per_minute' => 2,
            'per_hour' => 10,
        ]);

        $url = 'http://app.b2press.test/api/v1/packages';

        $limiter->assertCanRequest($url);
        $limiter->hit($url);
        $limiter->assertCanRequest($url);
        $limiter->hit($url);

        $this->expectException(RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API rate limit exceeded');

        $limiter->assertCanRequest($url);
    }

    public function test_rate_limit_can_be_disabled(): void
    {
        $limiter = new RemoteApiRateLimiter([
            'enabled' => false,
            'per_minute' => 1,
            'per_hour' => 1,
        ]);

        $url = 'http://app.b2press.test/api/v1/packages';

        $limiter->hit($url);
        $limiter->hit($url);
        $limiter->assertCanRequest($url);

        $this->assertTrue(true);
    }

    public function test_item_show_requests_share_collection_rate_limit_bucket(): void
    {
        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:minute');
        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:hour');

        $limiter = new RemoteApiRateLimiter([
            'enabled' => true,
            'per_minute' => 2,
            'per_hour' => 10,
        ]);

        $listUrl = 'http://app.b2press.test/api/v1/packages';
        $itemUrl = 'http://app.b2press.test/api/v1/packages/274';

        $limiter->assertCanRequest($listUrl);
        $limiter->hit($listUrl);
        $limiter->assertCanRequest($itemUrl);
        $limiter->hit($itemUrl);

        $this->expectException(RemoteApiSyncException::class);
        $this->expectExceptionMessage('Remote API rate limit exceeded');

        $limiter->assertCanRequest('http://app.b2press.test/api/v1/packages/275');
    }

    public function test_hour_limit_is_enforced(): void
    {
        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:minute');
        RateLimiter::clear('remote-api-outgoing:app.b2press.test:/api/v1/packages:hour');

        $limiter = new RemoteApiRateLimiter([
            'enabled' => true,
            'per_minute' => 100,
            'per_hour' => 1,
        ]);

        $url = 'http://app.b2press.test/api/v1/packages';

        $limiter->assertCanRequest($url);
        $limiter->hit($url);

        $this->expectException(RemoteApiSyncException::class);
        $limiter->assertCanRequest($url);
    }

    public function test_resolve_key_falls_back_when_parse_url_fails(): void
    {
        $limiter = new RemoteApiRateLimiter([
            'enabled' => true,
            'per_minute' => 5,
            'per_hour' => 5,
        ]);

        $limiter->hit('://broken');
        $limiter->assertCanRequest('://broken');

        $this->assertTrue(true);
    }
}

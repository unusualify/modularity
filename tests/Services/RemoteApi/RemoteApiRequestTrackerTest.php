<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\RemoteApiRequestTracker;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiRequestTrackerTest extends TestCase
{
    public function test_tracks_requests_per_url_and_flushes(): void
    {
        $tracker = new RemoteApiRequestTracker;

        $tracker->record('http://app.b2press.test/api/v1/packages?page=1');
        $tracker->record('http://app.b2press.test/api/v1/packages?page=2');
        $tracker->record('http://app.b2press.test/api/v1/packages/42');

        $this->assertSame(3, $tracker->total());
        $this->assertSame(2, $tracker->countsByUrl()['http://app.b2press.test/api/v1/packages']);
        $this->assertSame(1, $tracker->countsByUrl()['http://app.b2press.test/api/v1/packages/42']);

        $flushed = $tracker->flush();

        $this->assertSame(3, $flushed['total']);
        $this->assertSame(0, $tracker->total());
    }

    public function test_to_array_and_reset_clear_state(): void
    {
        $tracker = new RemoteApiRequestTracker;

        $tracker->record('http://app.b2press.test/api/v1/packages');

        $this->assertSame([
            'total' => 1,
            'by_url' => ['http://app.b2press.test/api/v1/packages' => 1],
        ], $tracker->toArray());

        $tracker->reset();

        $this->assertSame(0, $tracker->total());
        $this->assertSame([], $tracker->countsByUrl());
    }

    public function test_normalize_url_returns_original_when_parse_fails(): void
    {
        $tracker = new RemoteApiRequestTracker;
        $method = new \ReflectionMethod(RemoteApiRequestTracker::class, 'normalizeUrl');
        $method->setAccessible(true);

        $malformed = 'http:///';
        $this->assertSame($malformed, $method->invoke($tracker, $malformed));
    }
}

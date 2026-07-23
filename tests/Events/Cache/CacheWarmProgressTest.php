<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Events\Cache;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Unusualify\Modularous\Events\Cache\CacheWarmProgress;
use Unusualify\Modularous\Support\BroadcastAvailability;
use Unusualify\Modularous\Tests\TestCase;

class CacheWarmProgressTest extends TestCase
{
    protected function tearDown(): void
    {
        BroadcastAvailability::clearFake();

        parent::tearDown();
    }

    public function test_implements_should_broadcast(): void
    {
        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_STARTED, 5);

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
    }

    public function test_broadcast_when_requires_initiator_user_id(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $withUser = new CacheWarmProgress(CacheWarmProgress::STATUS_STARTED, 7);
        $withoutUser = new CacheWarmProgress(CacheWarmProgress::STATUS_STARTED, null);

        $this->assertTrue($withUser->broadcastWhen());
        $this->assertFalse($withoutUser->broadcastWhen());
    }

    public function test_broadcast_when_false_when_broadcasting_disabled(): void
    {
        config([
            'modularous.broadcasting.enabled' => false,
            'broadcasting.default' => 'null',
        ]);

        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_STARTED, 7);

        $this->assertFalse($event->broadcastWhen());
    }

    public function test_broadcast_when_false_when_pusher_sdk_missing_for_reverb(): void
    {
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'reverb',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(false);

        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_STARTED, 7);

        $this->assertFalse($event->broadcastWhen());
    }

    public function test_broadcast_on_private_user_channel(): void
    {
        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_PROGRESS, 11, [
            'processed' => 3,
        ]);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-users.11', $channels[0]->name);
        $this->assertSame('modularous.cache.warm.progress', $event->broadcastAs());
        $this->assertSame(3, $event->broadcastWith()['processed']);
    }

    public function test_broadcast_with_includes_structured_toast_payload(): void
    {
        $toast = [
            'title' => 'Cache warm',
            'description' => 'Warmed 3 records.',
            'detail' => 'Blog:Post',
            'variant' => 'success',
        ];

        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_COMPLETED, 9, [
            'job' => 'WarmModuleRouteCachesJob',
            'moduleName' => 'Blog',
            'moduleRouteName' => 'Post',
            'processed' => 3,
            'toast' => $toast,
        ]);

        $payload = $event->broadcastWith();

        $this->assertSame(CacheWarmProgress::STATUS_COMPLETED, $payload['status']);
        $this->assertSame(9, $payload['initiator_user_id']);
        $this->assertSame($toast, $payload['toast']);
        $this->assertSame('Blog:Post', $payload['toast']['detail']);
    }

    public function test_skipped_status_broadcasts_as_skipped_event(): void
    {
        $event = new CacheWarmProgress(CacheWarmProgress::STATUS_SKIPPED, 3, [
            'skipped' => true,
            'reason' => 'cache_disabled',
            'toast' => [
                'title' => 'Cache warm',
                'description' => 'Skipped: cache is disabled for this route',
                'detail' => 'Blog:Post',
                'variant' => 'warning',
            ],
        ]);

        $this->assertSame('modularous.cache.warm.skipped', $event->broadcastAs());
        $this->assertSame(CacheWarmProgress::STATUS_SKIPPED, $event->broadcastWith()['status']);
        $this->assertSame('warning', $event->broadcastWith()['toast']['variant']);
    }
}

<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Modules\SystemNotification\Events\ChatableMessageSynced;
use Unusualify\Modularous\Support\BroadcastAvailability;
use Unusualify\Modularous\Tests\TestCase;

class ChatableMessageSyncedTest extends TestCase
{
    protected function tearDown(): void
    {
        BroadcastAvailability::clearFake();

        parent::tearDown();
    }

    public function test_broadcast_on_uses_private_chats_channel(): void
    {
        $event = new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_CREATED,
            42,
            ['id' => 1, 'content' => 'Hello'],
        );

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-chats.42', $channels[0]->name);
    }

    public function test_broadcast_as_and_with_payload(): void
    {
        $message = ['id' => 9, 'content' => 'Synced'];
        $event = new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_UPDATED,
            7,
            $message,
        );

        $this->assertSame('modularous.chatable.message.synced', $event->broadcastAs());
        $this->assertSame([
            'action' => 'updated',
            'chat_id' => 7,
            'message' => $message,
        ], $event->broadcastWith());
    }

    public function test_broadcast_when_respects_availability(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $event = new ChatableMessageSynced(ChatableMessageSynced::ACTION_DELETED, 1, ['id' => 1]);
        $this->assertTrue($event->broadcastWhen());

        config(['modularous.broadcasting.enabled' => false]);
        $this->assertFalse($event->broadcastWhen());
    }

    public function test_broadcast_when_false_when_pusher_sdk_missing_for_reverb(): void
    {
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'reverb',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(false);

        $event = new ChatableMessageSynced(ChatableMessageSynced::ACTION_CREATED, 1, ['id' => 1]);

        $this->assertFalse($event->broadcastWhen());
    }
}

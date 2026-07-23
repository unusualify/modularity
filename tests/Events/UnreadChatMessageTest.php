<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Events;

use Modules\SystemNotification\Events\UnreadChatMessage;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Tests\TestCase;

class UnreadChatMessageTest extends TestCase
{
    public function test_broadcast_payload_is_domain_signal_without_user_id(): void
    {
        $message = new ChatMessage;
        $message->id = 10;
        $message->chat_id = 3;

        $event = new UnreadChatMessage($message);
        $payload = $event->broadcastWith();

        $this->assertSame('unread-chat-message', $event->broadcastOn()[0]->name);
        $this->assertSame('modularous.unread.chat.message', $event->broadcastAs());
        $this->assertSame([
            'id' => 10,
            'model_type' => ChatMessage::class,
            'model_id' => 10,
            'chat_id' => 3,
        ], $payload);
        $this->assertArrayNotHasKey('user_id', $payload);
    }
}

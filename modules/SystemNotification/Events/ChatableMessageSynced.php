<?php

declare(strict_types=1);

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

/**
 * Live chat message sync for open Chat.vue clients on private chats.{chatId}.
 */
class ChatableMessageSynced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, GatesBroadcastAvailability;

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    /**
     * @param  array<string, mixed>|null  $message  Serialized ChatMessage (or snapshot before delete)
     */
    public function __construct(
        public string $action,
        public int|string $chatId,
        public ?array $message = null,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chats.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.chatable.message.synced';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'chat_id' => $this->chatId,
            'message' => $this->message,
        ];
    }
}

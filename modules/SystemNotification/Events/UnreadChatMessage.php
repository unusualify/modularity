<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

class UnreadChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, GatesBroadcastAvailability;

    public function __construct(public ChatMessage $model)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new Channel('unread-chat-message')];
    }

    public function broadcastAs(): string
    {
        return 'modularous.unread.chat.message';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->model->id,
            'model_type' => ChatMessage::class,
            'model_id' => $this->model->id,
            'chat_id' => $this->model->chat_id ?? null,
            'user_id' => $this->model->user_id ?? null,
        ];
    }
}

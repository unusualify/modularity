<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadChatMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public \Unusualify\Modularity\Entities\ChatMessage $model)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new Channel('unread-chat-message')];
    }
}

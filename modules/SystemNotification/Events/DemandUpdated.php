<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DemandUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public \Unusualify\Modularity\Entities\Demand $model)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('demandable'),
        ];
    }
}

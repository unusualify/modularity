<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularity\Events\ModelEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ModelRestored extends ModelEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue connection to use when broadcasting the event.
     *
     * @var string
     */
    public $connection = 'redis';

    /**
     * The name of the queue on which to place the broadcasting job.
     *
     * @var string
     */
    public $queue = 'default';
    /**
     * Create a new event instance.
     */
    public function __construct(public $model)
    {
        parent::__construct($model);
    }


}

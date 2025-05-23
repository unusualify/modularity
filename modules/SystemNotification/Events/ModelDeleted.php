<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularity\Events\ModelEvent;

class ModelDeleted extends ModelEvent
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
    public function __construct(public $model, public $serializedData = null)
    {
        parent::__construct($model, $serializedData);
    }
}

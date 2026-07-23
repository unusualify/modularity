<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\ModelEvent;

class ModelCreated extends ModelEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $model)
    {
        parent::__construct($model);
    }
}

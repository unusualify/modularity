<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Unusualify\Modularity\Events\ModelEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class ModelCreated extends ModelEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $model)
    {
        //
        parent::__construct($model);
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    // public function broadcastOn(): array
    // {
    //     // dd($this->type);
    //     return [
    //         // new PrivateChannel('models.'.$this->type.'.'.$this->model->id),
    //         new PrivateChannel('models.'. $this->model->id),
    //         new Channel('model'),
    //     ];
    // }

    // public function broadcastAs()
    // {
    //     return 'model.created';
    // }


}

<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

class StateableUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels, GatesBroadcastAvailability;

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

    public function __construct(public $model, public $newState, public $oldState)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('stateable'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.stateable.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->model->id ?? null,
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id ?? null,
            'new_state' => is_object($this->newState) ? ($this->newState->code ?? $this->newState->id ?? null) : $this->newState,
            'old_state' => is_object($this->oldState) ? ($this->oldState->code ?? $this->oldState->id ?? null) : $this->oldState,
        ];
    }
}

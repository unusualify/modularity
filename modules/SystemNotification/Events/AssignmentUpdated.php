<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Entities\Assignment;
use Unusualify\Modularous\Events\Traits\EventChanges;
use Unusualify\Modularous\Events\Traits\EventUser;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

class AssignmentUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels, EventChanges, EventUser, GatesBroadcastAvailability;

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

    public function __construct(public Assignment $model)
    {
        $this->setupEventUser();
        $this->setupEventChanges();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('assignable'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.assignment.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->model->id,
            'model_type' => Assignment::class,
            'model_id' => $this->model->id,
            'assignee_id' => $this->model->assignee_id ?? null,
            'assigner_id' => $this->model->assigner_id ?? null,
            'user_id' => $this->hasUser() ? $this->getUser()?->id : null,
        ];
    }
}

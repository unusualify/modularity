<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

class PaymentCompleted implements ShouldBroadcast, ShouldDispatchAfterCommit
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

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Payment $model)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('payment'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.payment.completed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->model->id,
            'model_type' => Payment::class,
            'model_id' => $this->model->id,
            'status' => $this->model->status ?? null,
        ];
    }
}

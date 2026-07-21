<?php

namespace Unusualify\Modularous\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Str;
use Unusualify\Modularous\Events\Traits\EventChanges;
use Unusualify\Modularous\Events\Traits\EventStateable;
use Unusualify\Modularous\Events\Traits\EventUrls;
use Unusualify\Modularous\Events\Traits\EventUser;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

abstract class ModelEvent implements ShouldBroadcast
{
    use EventUrls, EventChanges, EventStateable, EventUser, GatesBroadcastAvailability;

    /**
     * The class of the model.
     *
     * @var string
     */
    public $modelType;

    /**
     * The channel name.
     *
     * @var string
     */
    public $broadcastService = 'reverb';

    /**
     * Create a new event instance.
     */
    public function __construct(public $model, public $serializedData = null)
    {
        $this->modelType = get_class($this->model);

        $this->setupEventUser();
        $this->setupEventUrls();
        $this->setupEventChanges();
        $this->setupEventStateable();

        if (in_array(InteractsWithBroadcasting::class, class_uses_recursive($this))) {
            $this->broadcastVia($this->broadcastService);
        }
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('models.' . $this->model->id),
            new Channel('model'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.' . Str::replace('_', '.', Str::replace('_event', '', Str::snake(get_class_short_name($this))));
    }

    /**
     * Minimal payload for frontend listeners (PII stays on notification mail/database).
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->model->id ?? null,
            'model_type' => $this->modelType,
            'model_id' => $this->model->id ?? null,
            'user_id' => $this->hasUser() ? $this->getUser()?->id : null,
        ];
    }
}

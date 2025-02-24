<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Str;

abstract class ModelEvent
{
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

        if (in_array(InteractsWithBroadcasting::class, class_uses_recursive($this))) {
            $this->broadcastVia($this->broadcastService);
        }
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        // dd(
        //     $this->model,
        //     $this->modelType,
        //     $this->broadcastService
        // );
        return [
            // new PrivateChannel('models.'.$this->type.'.'.$this->model->id),
            // new PresenceChannel('models.'.$this->model->id),
            new PrivateChannel('models.' . $this->model->id),
            new Channel('model'),
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return true;
        // return $this->order->value > 100;
    }

    public function broadcastAs()
    {
        // dd(
        //     'modularity.' . Str::replace('_', '.', Str::replace('_event', '', Str::snake(get_class_short_name($this))))
        // );
        return 'modularity.' . Str::replace('_', '.', Str::replace('_event', '', Str::snake(get_class_short_name($this))));
    }
}

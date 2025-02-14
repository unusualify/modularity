<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;

/**
 * BroadcastManager
 *
 * This service is responsible for extracting dynamic broadcast configuration from Modularity
 * events based on a given model. It instantiates event classes (which extend the common ModelEvent)
 * and groups their broadcast channel names (from broadcastOn) together with the events (from broadcastAs).
 *
 * Usage:
 *   // For example, using events from SystemNotification module:
 *   use Modules\SystemNotification\Events\ModelCreated;
 *   use Modules\SystemNotification\Events\ModelUpdated;
 *
 *   $config = BroadcastManage::forModel($model, [
 *       ModelCreated::class,
 *       ModelUpdated::class,
 *   ]);
 *
 * The resulting $config array can then be passed to your global JS store (for example via the Blade footer)
 * to let laravel-echo subscribe dynamically.
 */
class BroadcastManager
{
    protected $model;
    protected $eventClasses = [];

    /**
     * Constructor.
     *
     * @param mixed $model         The model instance.
     * @param array $eventClasses  The list of event class names to extract broadcasting info from.
     */
    public function __construct($model, array $eventClasses = [])
    {
        $this->model = $model;
        $this->eventClasses = $eventClasses;
    }

    /**
     * Get the complete broadcast configuration for the defined events.
     *
     * It groups the broadcast channel names (as returned by each event’s broadcastOn)
     * and attaches the associated broadcast event names (coming from broadcastAs).
     *
     * @return array An array of broadcast channel configurations.
     */
    public function getBroadcastConfiguration(): array
    {
        $channelEvents = [];

        foreach ($this->eventClasses as $eventClass) {
            if (class_exists($eventClass)) {
                // Instantiate the event with the model and extract information
                $event = new $eventClass($this->model);
                $channels = $event->broadcastOn();
                $broadcastName = method_exists($event, 'broadcastAs') ? $event->broadcastAs() : class_basename($event);

                foreach ($channels as $channel) {
                    // Get the channel name.
                    // Both PrivateChannel and Channel have a public "name" property.
                    $channelName = (is_object($channel) && property_exists($channel, 'name'))
                        ? $channel->name
                        : (string)$channel;
                    // Determine channel type by instance.
                    $channelType = ($channel instanceof PrivateChannel) ? 'private' : 'public';

                    // Group events by channel name.
                    if (!isset($channelEvents[$channelName])) {
                        $channelEvents[$channelName] = [
                            'type' => $channelType,
                            'events' => [],
                        ];
                    }
                    $channelEvents[$channelName]['events'][] = [
                        'event' => $broadcastName,
                    ];
                }
            }
        }

        $config = [];
        foreach ($channelEvents as $channelName => $details) {
            $config[] = [
                'name'   => $channelName,
                'type'   => $details['type'],
                'events' => $details['events'],
            ];
        }

        return $config;
    }

    /**
     * A static helper method to obtain the broadcast configuration for a given model.
     *
     * @param mixed $model         The model instance.
     * @param array $eventClasses  An array of event class names.
     *
     * @return array
     */
    public static function forModel($model, array $eventClasses = []): array
    {
        $instance = new static($model, $eventClasses);
        return $instance->getBroadcastConfiguration();
    }
}

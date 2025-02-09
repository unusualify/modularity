<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Modules\Notification\Events\ModelCreatedEvent;

trait ManageEvents
{
    protected $events = [
        'create' => ModelCreatedEvent::class,
        'store' => ModelCreatedEvent::class,
        // 'edit' => ModelUpdatedEvent::class,
        // 'update' => ModelUpdatedEvent::class,
        // 'destroy' => ModelDeletedEvent::class,
        // 'forceDelete' => ModelDeletedEvent::class,
        // 'restore' => ModelRestoredEvent::class,
    ];

    public function fireEvent($event, $data = [])
    {
        event($event, $data);
    }

    public function fireEventIf($condition, $event, $data = [])
    {
        event(new ModelCreatedEvent($model));
        ModelCreatedEvent::dispatch($model);
        ModelCreatedEvent::dispatchIf($this->request->ajax(), $model);
        if ($condition) {
            $this->fireEvent($event, $data);
        }
    }

    public function handleActionEvent($model, $action)
    {
        if (!isset($this->events[$action])) {
            return;
        }

        $event = $this->events[$action];

        dd($action, $model, $event);

        $this->fireEvent($event, $model);
    }



}

<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelDeleted;
use Modules\SystemNotification\Events\ModelForceDeleted;
use Modules\SystemNotification\Events\ModelRestored;
use Modules\SystemNotification\Events\ModelUpdated;

trait ManageEvents
{
    protected $events = [
        'create' => ModelCreated::class,
        'store' => ModelCreated::class,
        'edit' => ModelUpdated::class,
        'update' => ModelUpdated::class,
        'destroy' => ModelDeleted::class,
        'forceDelete' => ModelForceDeleted::class,
        'restore' => ModelRestored::class,
    ];

    public function fireEvent($event, $data = [])
    {
        // event($event, $data);
        $event::dispatch($data);
    }

    public function fireEventIf($condition, $event, $data = [])
    {
        if ($condition) {
            $this->fireEvent($event, $data);
        }
    }

    public function handleActionEvent($model, $action)
    {
        // dd($model->getChanges(), $model->isDirty(), $model->getDirty());

        // return;
        if (! isset($this->events[$action])) {
            return;
        }

        $event = $this->events[$action];

        $this->fireEvent($event, $model);
    }
}

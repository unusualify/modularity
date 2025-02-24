<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Facades\DB;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelDeleted;
use Modules\SystemNotification\Events\ModelForceDeleted;
use Modules\SystemNotification\Events\ModelRestored;
use Modules\SystemNotification\Events\ModelUpdated;

trait DispatchEvents
{
    protected $events = [
        'create' => ModelCreated::class,
        'store' => ModelCreated::class,
        'edit' => ModelUpdated::class,
        'update' => ModelUpdated::class,
        'delete' => ModelDeleted::class,
        'destroy' => ModelDeleted::class,
        'forceDelete' => ModelForceDeleted::class,
        'restore' => ModelRestored::class,
    ];

    public function commitEvent($event, $model, $serializedData = null)
    {
        DB::afterCommit(function () use ($event, $model, $serializedData) {
            if ($serializedData) {
                $event::dispatch($model, $serializedData);
            } else {
                $event::dispatch($model);
            }
        });
    }

    public function fireEventIf($condition, $event, $data = [])
    {
        if ($condition) {
            $this->fireEvent($event, $data);
        }
    }

    public function dispatchEvent($model, $action)
    {
        if (! isset($this->events[$action])) {
            return;
        }

        $event = $this->events[$action];
        $serializedData = null;

        if (in_array($action, ['delete', 'destroy', 'forceDelete'])) {
            $serializedData = $model->toArray();
        }

        $this->commitEvent($event, $model, $serializedData);
    }
}

<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\SystemNotification\Events\StateableUpdated;
use Modules\SystemNotification\Notifications\StateableUpdatedNotification;

class StateableListener implements ShouldHandleEventsAfterCommit
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Handle the event.
     */
    public function handle(StateableUpdated $event): void
    {
        $model = $event->model;
        $newState = $event->newState;
        $oldState = $event->oldState;

        if ($model->creator) {
            $model->creator->notify(new StateableUpdatedNotification($model, $newState, $oldState));
        }
    }
}

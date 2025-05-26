<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Notification;
use Modules\SystemNotification\Events\StateableUpdated;
use Modules\SystemNotification\Notifications\StateableUpdatedNotification;
use Unusualify\Modularity\Entities\User;

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
        } else {
            // Notification::route('mail', 'oguz.bukcuoglu@gmail.com')
            //     ->notifyNow(new StateableUpdatedNotification($model, $newState, $oldState));

            // Notification::send(
            //     User::role('superadmin')->get(),
            //     new StateableUpdatedNotification($model, $newState, $oldState)
            // );
        }

        // $model->notify(new StateableUpdatedNotification($model, $newState, $oldState));
    }
}

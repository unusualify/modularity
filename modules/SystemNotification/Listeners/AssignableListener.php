<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Notifications\TaskAssignedNotification;
use Modules\SystemNotification\Notifications\TaskUpdatedNotification;

class AssignableListener implements ShouldHandleEventsAfterCommit
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $isCreated = get_class($event) === AssignmentCreated::class;
        $model = $event->model;
        $assignee = $model->assignee;
        $assigner = $model->assigner;

        $activeUser = auth()->user();

        if($assignee){
            if($isCreated){
                // $assignee->notify(new TaskAssignedNotification($model));
            } else {
                if($assignee->id == $activeUser->id){
                    $assigner->notify(new TaskUpdatedNotification($model));
                }else if($assigner->id == $activeUser->id){
                    $assignee->notify(new TaskUpdatedNotification($model));
                } else {
                    $assigner->notify(new TaskUpdatedNotification($model));
                    $assignee->notify(new TaskUpdatedNotification($model));
                }
            }
        }
    }
}

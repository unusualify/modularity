<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Events\PaymentCompleted;
use Modules\SystemNotification\Notifications\PaymentCompletedNotification;
use Modules\SystemNotification\Notifications\PaymentFailedNotification;
use Modules\SystemNotification\Notifications\TaskAssignedNotification;
use Modules\SystemNotification\Notifications\TaskUpdatedNotification;

class PaymentListener implements ShouldHandleEventsAfterCommit
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $activeUser = auth()->user();
        $isSuccess = get_class($event) === PaymentCompleted::class;

        $payment = $event->model;

        if($isSuccess){
            try {
                $user = $payment->price->priceable->creator;
                $user->notify(new PaymentCompletedNotification($payment));
            } catch (\Throwable $th) {
                //throw $th;
            }
        } else {
            $superadmin = \Unusualify\Modularity\Entities\User::role('superadmin')->first();
            $superadmin->notify(new PaymentFailedNotification($payment));
        }
    }
}

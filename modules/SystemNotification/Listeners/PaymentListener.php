<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\SystemNotification\Events\PaymentCompleted;
use Modules\SystemNotification\Notifications\PaymentCompletedNotification;
use Modules\SystemNotification\Notifications\PaymentFailedNotification;

class PaymentListener implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(\Modules\SystemNotification\Events\PaymentCompleted|\Modules\SystemNotification\Events\PaymentFailed $event): void
    {
        $activeUser = auth()->user();
        $isSuccess = get_class($event) === PaymentCompleted::class;

        $payment = $event->model;

        if ($isSuccess) {
            try {
                $user = $payment->price->priceable->creator;
                $user->notify(new PaymentCompletedNotification($payment));
            } catch (\Throwable $th) {
                // throw $th;
            }
        } else {
            $superadmins = \Unusualify\Modularity\Entities\User::role('superadmin')->get();
            foreach ($superadmins as $superadmin) {
                $superadmin->notify(new PaymentFailedNotification($payment));
            }
        }
    }
}

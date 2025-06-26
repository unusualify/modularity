<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Notifications\Events\NotificationSent;

class ModularityNotificationSentListener
{
    public function handle(NotificationSent $event)
    {
        // $event->channel
        // $event->notifiable
        // $event->notification
        // $event->response
        $notification = $event->notification;
        $notificationClass = get_class($notification);

        if (str_starts_with($notificationClass, 'Modules\\') || str_starts_with($notificationClass, 'Unusualify\\Modularity\\')) {
            if(method_exists($notification, 'afterNotificationSent')){
                $notification->afterNotificationSent();
            }
        }

    }
}
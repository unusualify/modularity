<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Modules\SystemNotification\Notifications\Contracts\AfterSendable;
use Throwable;

class ModularousNotificationSentListener
{
    public function handle(NotificationSent $event): void
    {
        $notification = $event->notification;
        $notificationClass = get_class($notification);

        if (! str_starts_with($notificationClass, 'Modules\\')
            && ! str_starts_with($notificationClass, 'Unusualify\\Modularous\\')) {
            return;
        }

        if (! $notification instanceof AfterSendable && ! method_exists($notification, 'afterNotificationSent')) {
            return;
        }

        // NotificationSender clones the notification per channel and fires
        // NotificationSent after each channel. Prefer the database channel (first
        // durable side-effect); fall back to any channel if database is absent.
        $channels = method_exists($notification, 'via')
            ? (array) $notification->via($event->notifiable)
            : [];

        $preferred = in_array('database', $channels, true) ? 'database' : ($channels[0] ?? $event->channel);

        if ($event->channel !== $preferred) {
            return;
        }

        try {
            $notification->afterNotificationSent($event->notifiable);
        } catch (Throwable) {
            // Never block remaining notification channels if a post-send hook fails.
        }
    }
}

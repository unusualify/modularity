<?php

namespace Modules\SystemNotification\Providers;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\SystemNotification\Events\AssignmentCreated;
use Modules\SystemNotification\Events\AssignmentUpdated;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelDeleted;
use Modules\SystemNotification\Events\ModelForceDeleted;
use Modules\SystemNotification\Events\ModelRestored;
use Modules\SystemNotification\Events\ModelUpdated;
use Modules\SystemNotification\Events\PaymentCompleted;
use Modules\SystemNotification\Events\PaymentFailed;
use Modules\SystemNotification\Events\StateableUpdated;
use Modules\SystemNotification\Listeners\AssignableListener;
use Modules\SystemNotification\Listeners\ModelForceDeletedListener;
use Modules\SystemNotification\Listeners\ModelListener;
use Modules\SystemNotification\Listeners\ModularityNotificationSentListener;
use Modules\SystemNotification\Listeners\PaymentListener;
use Modules\SystemNotification\Listeners\StateableListener;
use Throwable;

use function Illuminate\Events\queueable;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        Event::listen(
            ModelCreated::class,
            ModelListener::class
        );
        Event::listen(
            ModelUpdated::class,
            ModelListener::class
        );
        Event::listen(
            ModelRestored::class,
            ModelListener::class
        );
        Event::listen(
            ModelDeleted::class,
            ModelForceDeletedListener::class
        );
        Event::listen(
            ModelForceDeleted::class,
            ModelForceDeletedListener::class
        );

        Event::listen(
            StateableUpdated::class,
            StateableListener::class
        );

        Event::listen(
            AssignmentCreated::class,
            AssignableListener::class
        );

        Event::listen(
            AssignmentUpdated::class,
            AssignableListener::class
        );

        Event::listen(
            PaymentCompleted::class,
            PaymentListener::class
        );

        Event::listen(
            PaymentFailed::class,
            PaymentListener::class
        );

        Event::listen(
            NotificationSent::class,
            ModularityNotificationSentListener::class
        );

        // Event::listen(
        //     'eloquent.created: ' . Notification::class,
        //     function ($event) {
        //         dd($event);
        //     }
        // );

        // dd(ModelListener::class);
        // // closure based listener
        // Event::listen(function (ModelUpdated $event) {
        //     dd($event, get_class_methods($event));
        // });

        // // closure based queueable listener
        // Event::listen(queueable(function (ModelCreatedEvent $event) {
        //     dd($event);
        // }));

        // // closure based queueable listener with delay
        // Event::listen(queueable(function (ModelCreatedEvent $event) {
        //     dd($event);
        // })->onConnection('redis')->onQueue('podcasts')->delay(now()->addSeconds(10)));

        // // closure based queueable listener with catch
        // Event::listen(queueable(function (ModelCreatedEvent $event) {
        //     // ...
        // })->catch(function (ModelCreatedEvent $event, Throwable $e) {
        //     // The queued listener failed...
        // }));

        // // wildcard listener
        // Event::listen('event.*', function (string $eventName, array $data) {
        //     dd($eventName, $data);
        // });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}

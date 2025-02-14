<?php

namespace Modules\SystemNotification\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelDeleted;
use Modules\SystemNotification\Events\ModelForceDeleted;
use Modules\SystemNotification\Events\ModelRestored;
use Modules\SystemNotification\Events\ModelUpdated;
use Modules\SystemNotification\Listeners\ModelForceDeletedListener;
use Modules\SystemNotification\Listeners\ModelListener;
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

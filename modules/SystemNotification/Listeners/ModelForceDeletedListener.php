<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Unusualify\Modularity\Listeners\Listener;

class ModelForceDeletedListener extends Listener
{
    protected $notificationPaths = [];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle($event): void
    {
        parent::handle($event);
    }
}

<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Unusualify\Modularity\Listeners\Listener;

class ModelListener extends Listener implements ShouldHandleEventsAfterCommit
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

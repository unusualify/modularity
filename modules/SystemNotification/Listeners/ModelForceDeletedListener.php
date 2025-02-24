<?php

namespace Modules\SystemNotification\Listeners;

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

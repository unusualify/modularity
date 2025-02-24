<?php

namespace Modules\SystemNotification\Repositories;

use Modules\SystemNotification\Entities\Notification;
use Unusualify\Modularity\Repositories\Repository;

class NotificationRepository extends Repository
{
    public function __construct(Notification $model)
    {
        $this->model = $model;
    }
}

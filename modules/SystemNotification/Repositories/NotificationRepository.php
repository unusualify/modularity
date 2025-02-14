<?php

namespace Modules\SystemNotification\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemNotification\Entities\Notification;


class NotificationRepository extends Repository
{


    public function __construct(Notification $model)
    {
        $this->model = $model;
    }
}

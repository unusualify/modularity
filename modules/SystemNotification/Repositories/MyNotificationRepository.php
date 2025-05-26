<?php

namespace Modules\SystemNotification\Repositories;

use Modules\SystemNotification\Entities\MyNotification;
use Unusualify\Modularity\Repositories\Repository;

class MyNotificationRepository extends Repository
{
    public function __construct(MyNotification $model)
    {
        $this->model = $model;
    }
}

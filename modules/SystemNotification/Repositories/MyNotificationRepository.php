<?php

namespace Modules\SystemNotification\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemNotification\Entities\MyNotification;


class MyNotificationRepository extends Repository
{
    

    public function __construct(MyNotification $model)
    {
        $this->model = $model;
    }
}

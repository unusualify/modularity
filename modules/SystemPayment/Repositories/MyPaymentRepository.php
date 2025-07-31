<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\MyPayment;


class MyPaymentRepository extends Repository
{
    

    public function __construct(MyPayment $model)
    {
        $this->model = $model;
    }
}

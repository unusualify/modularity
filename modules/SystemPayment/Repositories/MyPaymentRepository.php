<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\MyPayment;
use Unusualify\Modularity\Repositories\Repository;

class MyPaymentRepository extends Repository
{
    public function __construct(MyPayment $model)
    {
        $this->model = $model;
    }
}

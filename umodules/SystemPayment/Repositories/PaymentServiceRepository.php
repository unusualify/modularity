<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\PaymentService;


class PaymentServiceRepository extends Repository
{
    

    public function __construct(PaymentService $model)
    {
        $this->model = $model;
    }
}

<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\Payment;


class PaymentRepository extends Repository
{
    

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }
}

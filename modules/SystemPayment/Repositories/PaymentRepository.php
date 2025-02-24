<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularity\Repositories\Repository;

class PaymentRepository extends Repository
{
    public function __construct(Payment $model)
    {
        $this->model = $model;
    }
}

<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;

class PaymentRepository extends Repository
{
    use FilepondsTrait;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }
}

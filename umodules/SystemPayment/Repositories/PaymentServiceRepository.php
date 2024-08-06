<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;


class PaymentServiceRepository extends Repository
{
    use ImagesTrait;

    public function __construct(PaymentService $model)
    {
        $this->model = $model;
    }
}

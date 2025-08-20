<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class PaymentServiceRepository extends Repository
{
    use ImagesTrait, SpreadableTrait;

    public function __construct(PaymentService $model)
    {
        $this->model = $model;
    }
}

<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\CreatorTrait;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class PaymentRepository extends Repository
{
    use FilepondsTrait, SpreadableTrait, CreatorTrait;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }
}

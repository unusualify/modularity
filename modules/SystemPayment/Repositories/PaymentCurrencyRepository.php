<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentCurrency;
use Unusualify\Modularity\Repositories\Repository;

class PaymentCurrencyRepository extends Repository
{
    public function __construct(PaymentCurrency $model)
    {
        $this->model = $model;
    }
}

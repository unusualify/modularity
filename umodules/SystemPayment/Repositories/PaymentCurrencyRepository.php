<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\PaymentCurrency;


class PaymentCurrencyRepository extends Repository
{


    public function __construct(PaymentCurrency $model)
    {
        $this->model = $model;
    }
}

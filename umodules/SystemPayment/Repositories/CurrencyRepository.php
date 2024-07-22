<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\Currency;


class CurrencyRepository extends Repository
{
    

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
}

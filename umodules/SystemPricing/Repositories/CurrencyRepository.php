<?php

namespace Modules\SystemPricing\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Priceable\Models\Currency;

class CurrencyRepository extends Repository
{

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
}

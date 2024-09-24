<?php

namespace Modules\SystemPricing\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Oobook\Priceable\Models\Currency;

class CurrencyRepository extends Repository
{

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
}

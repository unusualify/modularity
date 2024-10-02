<?php

namespace Modules\SystemPricing\Repositories;

use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Repositories\Repository;

class CurrencyRepository extends Repository
{
    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
}

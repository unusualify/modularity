<?php

namespace Modules\SystemPricing\Repositories;

use Modules\SystemPricing\Entities\Currency;
use Unusualify\Modularity\Repositories\Repository;

class CurrencyRepository extends Repository
{
    public function __construct(Currency $model)
    {
        $this->model = $model;
    }
}

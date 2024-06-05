<?php

namespace Modules\SystemPricing\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Priceable\Models\PriceType;


class PriceTypeRepository extends Repository
{


    public function __construct(PriceType $model)
    {
        $this->model = $model;
    }
}

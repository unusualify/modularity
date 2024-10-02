<?php

namespace Modules\SystemPricing\Repositories;

use Oobook\Priceable\Models\PriceType;
use Unusualify\Modularity\Repositories\Repository;

class PriceTypeRepository extends Repository
{
    public function __construct(PriceType $model)
    {
        $this->model = $model;
    }
}

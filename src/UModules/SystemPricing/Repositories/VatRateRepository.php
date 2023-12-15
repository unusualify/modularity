<?php

namespace Modules\SystemPricing\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class VatRateRepository extends Repository
{


    public function __construct(\Unusualify\Priceable\Models\VatRate $model)
    {
        $this->model = $model;
    }
}

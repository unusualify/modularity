<?php

namespace Modules\SystemPricing\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class VatRateRepository extends Repository
{
    public function __construct(\Oobook\Priceable\Models\VatRate $model)
    {
        $this->model = $model;
    }
}

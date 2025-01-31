<?php

namespace Modules\SystemPricing\Repositories;

use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularity\Repositories\Repository;

class VatRateRepository extends Repository
{
    public function __construct(VatRate $model)
    {
        $this->model = $model;
    }
}

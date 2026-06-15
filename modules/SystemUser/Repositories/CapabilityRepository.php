<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\Capability;
use Unusualify\Modularous\Repositories\Repository;

class CapabilityRepository extends Repository
{
    public function __construct(Capability $model)
    {
        $this->model = $model;
    }
}

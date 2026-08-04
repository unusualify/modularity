<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\Capability;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;

class CapabilityRepository extends Repository
{
    use PublishableTrait;

    public function __construct(Capability $model)
    {
        $this->model = $model;
    }
}

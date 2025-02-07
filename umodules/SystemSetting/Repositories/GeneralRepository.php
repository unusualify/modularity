<?php

namespace Modules\SystemSetting\Repositories;

use Modules\SystemSetting\Entities\General;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class GeneralRepository extends Repository
{
    use ImagesTrait, SpreadableTrait;

    public function __construct(General $model)
    {
        $this->model = $model;
    }
}

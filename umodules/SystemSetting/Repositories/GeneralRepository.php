<?php

namespace Modules\SystemSetting\Repositories;

use Modules\SystemSetting\Entities\General;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadTrait;

class GeneralRepository extends Repository
{
    use ImagesTrait, SpreadTrait;
    public function __construct(General $model)
    {
        $this->model = $model;
    }
}

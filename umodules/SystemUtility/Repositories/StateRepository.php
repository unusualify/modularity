<?php

namespace Modules\SystemUtility\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemUtility\Entities\State;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;


class StateRepository extends Repository
{
    use TranslationsTrait;

    public function __construct(State $model)
    {
        $this->model = $model;
    }
}

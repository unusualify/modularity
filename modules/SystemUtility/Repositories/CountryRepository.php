<?php

namespace Modules\SystemUtility\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemUtility\Entities\Country;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;


class CountryRepository extends Repository
{
    use TranslationsTrait;

    public function __construct(Country $model)
    {
        $this->model = $model;
    }
}

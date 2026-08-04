<?php

namespace Modules\SystemUtility\Repositories;

use Modules\SystemUtility\Entities\Country;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

class CountryRepository extends Repository
{
    use TranslationsTrait, PublishableTrait;

    public function __construct(Country $model)
    {
        $this->model = $model;
    }
}

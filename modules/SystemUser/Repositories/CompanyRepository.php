<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\Company;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class CompanyRepository extends Repository
{
    use SpreadableTrait;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }
}

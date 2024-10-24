<?php

namespace Modules\SystemUser\Repositories;

use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Repositories\Repository;

class CompanyRepository extends Repository
{
    public function __construct(Company $model)
    {
        $this->model = $model;
    }
}

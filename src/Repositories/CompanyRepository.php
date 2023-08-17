<?php

namespace OoBook\CRM\Base\Repositories;

use Illuminate\Http\Request;
use OoBook\CRM\Base\Entities\Company;
use OoBook\CRM\Base\Repositories\Traits\TreeviewTrait;

class CompanyRepository extends Repository
{
    public function __construct(Company $model)
    {
        $this->model = $model;
    }
}

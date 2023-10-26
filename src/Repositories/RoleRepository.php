<?php

namespace OoBook\CRM\Base\Repositories;

use Illuminate\Http\Request;
use OoBook\CRM\Base\Repositories\Traits\RelationTrait;
use OoBook\CRM\Base\Repositories\Traits\TreeviewTrait;
use OoBook\CRM\Base\Traits\Tabulable;

class RoleRepository extends Repository
{
    public function __construct(\Spatie\Permission\Models\Role $model)
    {
        $this->model = $model;
    }

}

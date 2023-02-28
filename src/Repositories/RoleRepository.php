<?php

namespace Unusual\CRM\Base\Repositories;

use Illuminate\Http\Request;
use Unusual\CRM\Base\Repositories\Traits\TreeviewTrait;
use Unusual\CRM\Base\Traits\Tabulable;

class RoleRepository extends Repository
{
    // use Tabulable;
    use TreeviewTrait;

    public function __construct(\Spatie\Permission\Models\Role $model)
    {
        $this->model = $model;
    }

}

<?php

namespace OoBook\CRM\Base\Repositories;

use Illuminate\Http\Request;
class PermissionRepository extends Repository
{

    public function __construct(\Spatie\Permission\Models\Permission $model)
    {
        $this->model = $model;
    }

}

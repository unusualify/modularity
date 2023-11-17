<?php

namespace Modules\SystemUser\Repositories;

use Illuminate\Http\Request;
use Unusualify\Modularity\Repositories\Repository;

class PermissionRepository extends Repository
{

    public function __construct(\Spatie\Permission\Models\Permission $model)
    {
        $this->model = $model;
    }

}

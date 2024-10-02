<?php

namespace Modules\SystemUser\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class RoleRepository extends Repository
{
    public function __construct(\Spatie\Permission\Models\Role $model)
    {
        $this->model = $model;
    }
}

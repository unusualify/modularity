<?php

namespace Unusual\CRM\Base\Repositories;

use Illuminate\Http\Request;
use Unusual\CRM\Base\Traits\Tabulable;

class PermissionRepository extends Repository
{
    use Tabulable;

    public function __construct(\Spatie\Permission\Models\Permission $model)
    {
        $this->model = $model;
    }

}

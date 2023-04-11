<?php

namespace Unusual\CRM\Base\Repositories;

use Illuminate\Http\Request;
use Unusual\CRM\Base\Entities\User;
use Unusual\CRM\Base\Traits\Tabulable;

class RoleRepository extends Repository
{
    use Tabulable;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

}

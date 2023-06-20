<?php

namespace OoBook\CRM\Base\Repositories;

use Illuminate\Http\Request;
use OoBook\CRM\Base\Entities\User;

class UserRepository extends Repository
{

    public function __construct(User $model)
    {
        $this->model = $model;
    }

}

<?php

namespace OoBook\CRM\Base\Repositories;

use Illuminate\Http\Request;
use OoBook\CRM\Base\Entities\User;
use OoBook\CRM\Base\Repositories\Traits\TreeviewTrait;

class UserRepository extends Repository
{
    use TreeviewTrait;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

}

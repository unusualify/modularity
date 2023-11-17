<?php

namespace Modules\SystemUser\Repositories;

use Illuminate\Http\Request;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\TreeviewTrait;

class UserRepository extends Repository
{
    use TreeviewTrait;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

}

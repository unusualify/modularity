<?php

namespace Modules\SystemUser\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\TreeviewTrait;

class UserRepository extends Repository
{
    // use TreeviewTrait;
    public $exceptRelations = [
        // 'roles'
    ];

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    // public function afterSave($object, $fields)
    // {
    //     parent::afterSave($object, $fields);

    //     if(isset($fields['roles'])){
    //         dd(
    //             $fields,
    //             Arr::map($fields['roles'], function($item){
    //                 return $item['name'];
    //             })
    //         );
    //         // $this->syncRoles(Arr::map(fu));
    //     }
    // }

}

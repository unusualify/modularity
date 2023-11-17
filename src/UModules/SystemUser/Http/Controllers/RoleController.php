<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Http\Controllers\BaseController;

class RoleController extends BaseController
{

    protected $namespace = 'Modules\SystemUser';

    protected $moduleName = 'SystemUser';

    protected $routeName = 'Role';

    // protected $routePrefix = 'User';

    protected $modelName = "Role";

    protected $titleColumnKey = "name";

    protected $perPage = 15;

    protected $childrenTree = [
        'permission'
    ];

    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }

    public function childrenForTree()
    {
        foreach ($this->childrenTree as $child) {
            # code...
        }
    }

    public function treeActions()
    {

        return Collection::make($this->childrenTree)->map(function($name){

            return [
                'name' => '',
                'color' => '',
                'link' => '/user/role/:id/permission'
            ];
        });
    }

    public function getAllTreeElements()
    {

        return Collection::make($this->childrenTree)->map(function($name){

            return [
                'name' => '',
                'color' => '',
                'link' => '/user/role/:id/permission'
            ];
        });
    }


}

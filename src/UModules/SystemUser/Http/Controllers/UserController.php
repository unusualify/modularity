<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularity\Http\Controllers\BaseController;

class UserController extends BaseController
{
    /**
     * @var string
     */
    protected $namespace = 'Modules\SystemUser';

    /**
     * @var string
     */
    protected $moduleName = 'SystemUser';

    /**
     * @var string
     */
    protected $routeName = 'User';


    protected $titleColumnKey = 'name';


    // protected $perPage = 2;


    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = "User";


    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }
}

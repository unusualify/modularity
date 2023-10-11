<?php

namespace OoBook\CRM\Base\Http\Controllers;

use Illuminate\Http\Request;
class PermissionController extends BaseController
{

    /**
     * @var string
     */
    protected $namespace = 'OoBook\CRM\Base';

    /**
     * @var string
     */
    protected $moduleName = 'SystemUser';

    /**
     * @var string
     */
    protected $routeName = 'Permission';


    protected $titleColumnKey = 'name';


    // protected $perPage = 2;


    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = "Permission";


    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }


}

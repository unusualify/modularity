<?php

namespace OoBook\CRM\Base\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OoBook\CRM\Base\Http\Requests\StorePermissionRequest;
use OoBook\CRM\Base\Transformers\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{

    /**
     * @var string
     */
    protected $namespace = 'OoBook\CRM\Base';

    /**
     * @var string
     */
    protected $moduleName = 'User';

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

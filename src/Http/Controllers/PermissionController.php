<?php

namespace Unusual\CRM\Base\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusual\CRM\Base\Http\Requests\StorePermissionRequest;
use Unusual\CRM\Base\Transformers\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{

    /**
     * @var string
     */
    protected $namespace = 'Unusual\CRM\Base';

    /**
     * @var string
     */
    protected $moduleName = 'User';

    /**
     * @var string
     */
    protected $routeName = 'Permission';


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

<?php

namespace Modules\SystemUtility\Http\Controllers\Front;

use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Illuminate\Contracts\Foundation\Application;

class StateController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'SystemUtility';

    /**
     * @var string
     */
    protected $routeName = 'State';

    /**
     * Use default authorization permissions
     *
     * @var bool
     */
    protected $setDefaultPermissions = false;


    public function __construct(
        Application $app,
        Request $request
    )
    {
        parent::__construct($app,$request);
    }
}

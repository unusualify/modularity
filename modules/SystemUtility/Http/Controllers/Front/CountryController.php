<?php

namespace Modules\SystemUtility\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;

class CountryController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'SystemUtility';

    /**
     * @var string
     */
    protected $routeName = 'Country';

    /**
     * Use default authorization permissions
     *
     * @var bool
     */
    protected $setDefaultPermissions = false;

    public function __construct(
        Application $app,
        Request $request
    ) {
        parent::__construct($app, $request);
    }
}

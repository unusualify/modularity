<?php

namespace Modules\ErrorPage\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\BaseController;

class ErrorPageController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'ErrorPage';

    /**
     * @var string
     */
    protected $routeName = 'ErrorPage';

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

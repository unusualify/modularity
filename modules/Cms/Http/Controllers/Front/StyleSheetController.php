<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\BaseController;

class StyleSheetController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'Cms';

    /**
     * @var string
     */
    protected $routeName = 'StyleSheet';

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

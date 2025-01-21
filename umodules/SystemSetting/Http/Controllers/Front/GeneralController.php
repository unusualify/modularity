<?php

namespace Modules\SystemSetting\Http\Controllers\Front;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;

class GeneralController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'SystemSetting';

    /**
     * @var string
     */
    protected $routeName = 'General';

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

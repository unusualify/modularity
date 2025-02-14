<?php

namespace Modules\SystemNotification\Http\Controllers\Front;

use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Illuminate\Contracts\Foundation\Application;

class NotificationController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'Notification';

    /**
     * @var string
     */
    protected $routeName = 'Notification';

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

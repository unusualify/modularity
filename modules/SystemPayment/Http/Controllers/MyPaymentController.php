<?php

namespace Modules\SystemPayment\Http\Controllers;

use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;

class MyPaymentController extends BaseController
{

    /**
     * @var string
     */
    protected $moduleName = 'SystemPayment';

    /**
     * @var string
     */
    protected $routeName = 'MyPayment';

}

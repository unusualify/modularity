<?php

namespace Modules\SystemPricing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Http\Controllers\BaseController;

class CurrencyController extends BaseController
{

    /**
     * @var string
     */
     protected $moduleName = 'SystemPricing';

    /**
     * @var string
     */
     protected $routeName = 'Currency';

}

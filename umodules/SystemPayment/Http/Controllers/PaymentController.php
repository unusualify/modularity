<?php

namespace Modules\SystemPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Package\Entities\PackageCountry;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Unusualify\Payable\Payable;
use Unusualify\Payable\Services\TebCommonPosService;
use Oobook\Priceable\Models\Price;

class PaymentController extends BaseController
{

    /**
     * @var string
     */
    protected $moduleName = 'SystemPayment';

    /**
     * @var string
     */
    protected $routeName = 'Payment';


    /**
     *
     */

}

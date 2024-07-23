<?php

namespace Modules\SystemPayment\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Package\Entities\PackageCountry;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Unusualify\Payable\Payable;
use Unusualify\Priceable\Models\Price;

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
     * @var int request of packageCountry
     */
    public function pay(Request $request,Price $price, $payment_service_id)
    {

        $price = PackageCountry::find($price->id)
            ->calculatePrice();
        $paymentService = PaymentService::find($payment_service_id);
        $payment = new Payable($paymentService->name);
        $params = [
            'amount' => $price->display_price,
            'currency' => $price->currency
        ];
        $payment->service->pay($params, $price->id);
    }
}

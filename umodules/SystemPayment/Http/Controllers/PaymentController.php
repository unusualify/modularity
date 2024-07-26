<?php

namespace Modules\SystemPayment\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Package\Entities\PackageCountry;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Unusualify\Payable\Payable;
use Unusualify\Payable\Services\TebCommonPosService;
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
     *
     */
    public function pay(Request $request, Price $price)
    {
        $params = $request->all();
        $paymentService = PaymentService::find($params['payment_service_id']);
        $payment = new Payable($paymentService->name);
        $payment->service->pay($params);
    }

    public function paypalResponse(Request $request)
    {
        $allParams = $request->query();

        if ($allParams['success'] == true) {
            $paypal = new Payable('paypal');
            $resp = $paypal->service->capturePayment($allParams['token']);
            // dd($resp);
            $paypal->service->updateRecord(
                $allParams['token'],
                'COMPLETED',
                json_encode($resp)
            );
        }
        dd('here');
    }
    public function garantiResponse(Request $request)
    {
        //Not testable at the moment
        dd($request);
    }

    public function tebResponse(Request $request)
    {
        //Not testable at the moment

        dd($request);
    }

    public function tebCommonResponse(Request $request)
    {
        $payment = new Payable('teb-common-pos');
        if ($request->BankResponseCode == "00") {
            // dd($request, $request->BankResponseCode);
            $payment->service->updateRecord($request->OrderId, 'COMPLETED', $request->all());
            //Update payment model with the response field and remove parameters
            // return view()
            dd('success');
        } else {
            $payment->service->updateRecord($request->OrderId, 'CANCELED', $request->all());        }
        dd($request);
    }

    public function iyzicoResponse(Request $request)
    {
        $payment = new Payable('iyzico');
        // dd($request->all());
        if ($request->status == 'success') {
            $params = [
                'payment_id' => $request->paymentId,
                'conversation_id' => $request->conversationId,
                'conversation_data' => $request->conversationData
            ];
            // dd('here');
            $payment->service->completePayment($params);

            dd('finished');
        }
        dd($request);
    }
}

<?php

namespace Modules\SystemPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Modules\Package\Entities\PackageCountry;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Unusualify\Payable\Payable;
use Unusualify\Payable\Services\TebCommonPosService;
use Unusualify\Priceable\Models\Price;

class PriceController extends Controller
{



    /**
     *
     */
    public function pay(Request $request)
    {
        // dd(redirect());

        $params = $request->all();
        // dd($params);
        $payment = null;
        $price = Price::with('currency')->find($params['price_id']);
        $paymentService = null;
        if($params['payment_service']['payment_method'] == -1){
            //TODO: find price with currency based on the currency get default payment service
            $currency = $price->currency->iso_4217;
            $paymentServiceName = config("modularity.default_payment_service" . ".{$currency}");
            $payment = new Payable($paymentServiceName);
            $paymentService = PaymentService::where('name', $paymentServiceName)->first();
            Session::put('payable_payment_service', $paymentServiceName );


        }else{
            $paymentService = PaymentService::find($params['payment_service']['payment_method']);
            $payment = new Payable($paymentService->name);
            Session::put('payable_payment_service', $paymentService->name );

        }
        // dd(Cookie::get('payable_payment_service'));
        // dd($paymentService->id);
        // dd($payment);
        $user = Auth::user();
        $company = $user->company;
        // dd($company);
        // dd($params);
        $payload = $payment->getPayloadSchema();
        // dd($payload);
        // dd($price);
        $payload["locale"] = app()->getLocale();
        $payload["payment_service_id"] = $paymentService->id;
        $payload["order_id"] = uniqid("ORD-");
        $payload["price"] = $price->price_excluding_vat;
        $payload["price_id"] = $price->id;
        $payload["paid_price"] = $price->display_price;
        $payload["currency"] = $price->currency;
        $payload["installment"] = "1";
        $payload["payment_group"] = "PRODUCT";
        $payload["card_name"] = $params["payment_service"]["credit_card"]["card_name"];
        $payload["card_no"] = str_replace(' ', '', $params["payment_service"]["credit_card"]["card_number"]);
        $payload["card_month"] = (string) $params["payment_service"]["credit_card"]["card_month"];
        $payload["card_year"] = (string) $params["payment_service"]["credit_card"]["card_year"];
        $payload["card_cvv"] = $params["payment_service"]["credit_card"]["card_cvv"];
        $payload["user_id"] = $user->id;
        $payload["user_name"] = $user->name;
        $payload["user_surname"] = $user->surname;
        $payload["user_gsm"] = $user->phone;
        $payload["user_email"] = $user->email;
        $payload["user_ip"] = $request->ip();
        $payload["user_last_login_date"] = Carbon::now()->format('Y-m-d H:i:s');
        $payload["user_registration_date"] = $user->created_at->format('Y-m-d H:i:s');
        $payload["user_address"] = $company->address;
        $payload["user_city"] = $company->city;
        $payload["user_country"] = $company->country;
        $payload["user_zip_code"] = $company->zip_code;
        $payload["basket_id"] = uniqid();
        $payload["items"] = [
            [
                "id" => "1",
                "name" => "test",
                "category1" => "testcat1",
                "category2" => "testcat2",
                "price" => $price->price_excluding_vat,
                "type" => 'VIRTUAL'
            ]

        ];
        $payload["custom_fields"] = [
            'previous_url' => session('_previous.url')
        ];
        // dd($payload,$params);
        // dd(session('_previous.url'));
        $resp = $payment->service->pay($payload);

        $redirectionUrl = $resp->links[1]->href;
        if($redirectionUrl)
            print(
            "<script>window.open('" . $redirectionUrl . "', '_self')</script>"
            );
        exit;
    }

    public function response(Request $request){
        // Left the variables separete incase we need data to display.
        // $payment = Payment::find($request->id);
        // $price = $payment->price;
        // $priceable = $price->priceable;
        // dd($price,$priceable);
        if($request->status == 'success')
            return redirect(merge_url_query($request->custom_fields['previous_url'],
        [
            'customModal' => [
                'color' => 'success',
                'description' => 'Your payment has been successfully completed. Thank you for your purchase.',
            ]
        ]));
        else{
            // dd($request->custom_fields['previous_url']);
            return redirect(merge_url_query($request->custom_fields['previous_url'], ['payment' => 'error']));
        }
    }
}

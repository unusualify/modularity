<?php

namespace Modules\SystemPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Currency;
use Oobook\Priceable\Models\Price;
use Unusualify\Modularity\Services\CurrencyExchangeService;
use Unusualify\Payable\Payable;

class PriceController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyExchangeService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function pay(Request $request)
    {
        // dd(redirect());

        $params = $request->all();
        $payment = null;
        // dd($params);
        $price = Price::with('currency')->find($params['price_id']);
        $requestCurrency = $params['payment_service']['currency']['iso_4217'];
        // dd($price);
        if ($price->currency->iso_4217 != $requestCurrency) {
            $newCurrency = Currency::where('iso_4217', $requestCurrency)->first();
            $price->currency_id = $newCurrency->id;

            $price = $this->updatePrice($price, $newCurrency);
            $price->save();
        }
        $paymentService = null;
        if ($params['payment_service']['payment_method'] == -1) {

            $currency = $price->currency->iso_4217;
            $paymentServiceName = unusualConfig('payment.currency_services' . ".{$currency}");
            $payment = new Payable($paymentServiceName);
            $paymentService = PaymentService::where('name', $paymentServiceName)->first();
            // dd($paymentServiceName);
            Session::put('payable_payment_service', $paymentServiceName);

        } else {

            $paymentService = PaymentService::find($params['payment_service']['payment_method']);
            $payment = new Payable($paymentService->name);
            Session::put('payable_payment_service', $paymentService->name);

        }

        $user = Auth::user();
        $company = $user->company;
        // dd($company);
        $payload = $payment->getPayloadSchema();
        $arr = [
            'locale' => app()->getLocale(),
            'payment_service_id' => $paymentService->id,
            'order_id' => uniqid('ORD-'),
            'price' => $price->price_excluding_vat,
            'price_id' => $price->id,
            'paid_price' => $price->display_price,
            'currency' => $price->currency,
            'installment' => '1',
            'payment_group' => 'PRODUCT',
            'card_name' => $params['payment_service']['credit_card']['card_name'],
            'card_no' => str_replace(' ', '', $params['payment_service']['credit_card']['card_number']),
            'card_month' => (string) $params['payment_service']['credit_card']['card_month'],
            'card_year' => (string) $params['payment_service']['credit_card']['card_year'],
            'card_cvv' => $params['payment_service']['credit_card']['card_cvv'],
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_surname' => $user->surname,
            'user_gsm' => $user->phone,
            'user_email' => $user->email,
            'user_ip' => $request->ip(),
            'user_last_login_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'user_registration_date' => $user->created_at->format('Y-m-d H:i:s'),
            'company_name' => $company->name,
            'user_address' => $company->address,
            'user_city' => $company->city,
            'user_country' => $company->country,
            'user_zip_code' => $company->zip_code,
            'basket_id' => uniqid(),
            'items' => [
                [
                    'id' => '1',
                    'name' => 'test',
                    'category1' => 'testcat1',
                    'category2' => 'testcat2',
                    'price' => $price->price_excluding_vat,
                    'type' => 'VIRTUAL',
                ],
            ],
            'custom_fields' => [
                'previous_url' => session('_previous.url'),
            ],
        ];

        $payload = array_merge_recursive_preserve($payload, $arr);
        $resp = $payment->pay($payload);

        return $resp;
    }

    public function response(Request $request)
    {

        if ($request->status == 'success') {
            return redirect(merge_url_query($request->custom_fields['previous_url'],
                [
                    'customModal' => [
                        'color' => 'success',
                        'description' => 'Your payment has been successfully completed. Thank you for your purchase.',
                        'icon' => '$check',
                        'hideModalCancel' => true,
                    ],
                ]));
        } else {
            // dd($request->custom_fields['previous_url']);
            return redirect(merge_url_query($request->custom_fields['previous_url'],
                [
                    'customModal' => [
                        'color' => 'error',
                        'description' => 'Your payment has been failed. Please try again later or contact with administrator.',
                        'icon' => '$error',
                        'hideModalCancel' => true,
                    ],
                ]));
        }
    }

    protected function updatePrice($price, $currency)
    {

        try {

            $converted = $this->currencyService->convertTo($price->display_price, mb_strtoupper($currency->iso_4217));
            $vatPercentage = ($price->vat_amount / $price->display_price) * 100;

            $price->display_price = $converted / 100;
            $price->vat_amount = ($converted * $vatPercentage) / 100;
            $price->price_excluding_vat = ($price->display_price - $price->vat_amount) / 100;
            $price->price_including_vat = $price->display_price;

            return $price;

        } catch (\Exception $e) {
            return response()->json(['error' => 'Conversion failed.'], 400);
        }

    }
}

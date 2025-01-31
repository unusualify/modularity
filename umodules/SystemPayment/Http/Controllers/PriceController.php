<?php

namespace Modules\SystemPayment\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\Payment;
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
        $params = $request->all();
        $payment = null;
        $price = Price::with('currency')->find($params['price_id']);
        $requestCurrencyIso4217 = $params['payment_service']['currency']['iso_4217'];

        $convertedPrice = $price->price_including_vat;
        $convertedPriceExcludingVat = $price->price_excluding_vat;

        if (Str::upper($price->currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
            $convertedPriceExcludingVat = $this->currencyService
                ->convertTo(
                    $convertedPriceExcludingVat,
                    mb_strtoupper($requestCurrencyIso4217),
                    decimals: 0,
                    round: 'round'
                );
            $convertedPrice = $this->currencyService
                ->convertTo(
                    $convertedPrice,
                    mb_strtoupper($requestCurrencyIso4217),
                    decimals: 0,
                    round: 'round'
                );
        }

        $convertedCurrency = Currency::where('iso_4217', $requestCurrencyIso4217 ?? $price->currencyIso4217)->first();

        $paymentService = null;

        if ($params['payment_service']['payment_method'] == -1) {

            $currency = $convertedCurrency->iso_4217;
            $paymentServiceName = modularityConfig('payment.currency_services' . ".{$currency}");
            $payment = new Payable($paymentServiceName);
            $paymentService = PaymentService::where('name', $paymentServiceName)->first();

            Session::put('payable_payment_service', $paymentServiceName);

        } else {

            $paymentService = PaymentService::find($params['payment_service']['payment_method']);
            $payment = new Payable($paymentService->name);
            Session::put('payable_payment_service', $paymentService->name);

        }

        $user = Auth::user();
        $company = $user->company;

        $payload = $payment->getPayloadSchema();
        $arr = [
            'locale' => app()->getLocale(),
            'payment_service_id' => $paymentService->id,
            'order_id' => uniqid('ORD-'),
            'price_id' => $price->id,
            'price' => $convertedPriceExcludingVat,
            'paid_price' => $convertedPrice,
            'currency' => $convertedCurrency,
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

            try {
                if ($request->get('id')) {

                    $payment = Payment::find($request->get('id'));

                    $newPrice = $payment->price->replicate();

                    $newPrice->saveQuietly();

                    $newPrice->update([
                        'display_price' => 0,
                    ]);
                }
            } catch (\Throwable $th) {

                try {
                    Mail::raw('There was an error updating the price after payment.\n\n' .
                        'Error details:\n' .
                        'Payment ID: ' . request()->get('id') . '\n\n' .
                        'Error: ' . $th->getMessage() . '\n\n' .
                        'Trace: ' . $th->getTraceAsString(), function ($message) {
                            $message->to('oguzhan@unusualgrowth.com')
                                ->subject('Payment Price Update Error');
                        });
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }

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
            // dd($request->custom_fields);
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

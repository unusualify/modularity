<?php

namespace Modules\SystemPayment\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Unusualify\Modularity\Facades\CurrencyExchange;
use Unusualify\Modularity\View\Component;
use Unusualify\Payable\Models\Enums\PaymentStatus;
use Unusualify\Payable\Payable;

class PriceController extends Controller
{
    protected $currencyService;

    public function pay(Request $request)
    {
        $params = $request->all();
        $price = Price::with('currency', 'vatRate')->find($params['price_id']);

        $requestCurrencyIso4217 = $params['payment_service']['currency']['iso_4217'];

        $rawAmount = $price->discounted_raw_amount;
        $totalAmount = $price->total_amount;

        $currency = $price->currency;
        $converted = false;

        $exchangeRate = null;

        if (Str::upper($price->currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
            $converted = true;
            $currency = Currency::where('iso_4217', $requestCurrencyIso4217)->first();

            $rawAmount = CurrencyExchange::convertTo(
                $rawAmount,
                mb_strtoupper($requestCurrencyIso4217),
                decimals: 0,
                round: 'round'
            );
            $exchangeRate = CurrencyExchange::getExchangeRate(mb_strtoupper($requestCurrencyIso4217));

            $totalAmount = intval($rawAmount * (1 + $price->vat_multiplier));
        }

        $paymentService = null;

        if ($params['payment_service']['payment_method'] == -1) {
            $paymentCurrency = PaymentCurrency::find($currency->id);
            $paymentService = $paymentCurrency->paymentService;

            if (! $paymentService) {
                throw new \Exception('Payment service not found for currency ' . $paymentCurrency->iso_4217);
            }
        } else {
            $paymentService = PaymentService::find($params['payment_service']['payment_method']);
        }

        $payable = new Payable($paymentService->key);
        Session::put('payable_payment_service', $paymentService->key);

        $user = Auth::user();
        $company = $user->company;

        $payload = [
            'amount' => $totalAmount,
            'currency' => $currency->iso_4217,

            'locale' => app()->getLocale(),
            'order_id' => uniqid('ORD'),
            'installment' => $request->get('installment') ?? '1',
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

            'company_name' => $company ? $company->name : null,
            'user_address' => $company ? $company->address : null,
            'user_city' => $company ? $company->city : null,
            'user_country' => $company ? $company->country : null,
            'user_zip_code' => $company ? $company->zip_code : null,

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

            'modularity' => [
                'previous_url' => url()->previous(),
                'datetime' => now()->format('Y-m-d H:i:s'),

                'original_raw_amount' => $price->discounted_raw_amount,
                'original_total_amount' => $price->total_amount,

                'converted_raw_amount' => $rawAmount,
                'converted_total_amount' => $totalAmount,

                'vat_percentage' => $price->vat_percentage,
                'vat_multiplier' => $price->vat_multiplier,
                'discount_percentage' => $price->discount_percentage,

                'converted' => $converted,
                'original_currency' => $price->currency->iso_4217,
                'original_currency_id' => $price->currency_id,
                'converted_currency' => $currency->iso_4217,
                'converted_currency_id' => $currency->id,
                'exchange_rate' => $exchangeRate,
            ],
        ];

        $paymentPayload = [
            'price_id' => $price->id,
            'payment_service_id' => $paymentService->id,
            'currency_id' => $currency->id,
        ];

        // dd(
        //     $totalAmount,
        //     $paymentService,
        //     $payload,
        // );

        if ($price->payment && in_array($price->payment->status, [PaymentStatus::PENDING, PaymentStatus::FAILED])) {
            $paymentPayload['id'] = $price->payment->id;
        }

        $resp = $payable->pay($payload, paymentPayload: $paymentPayload);

        return $resp;
    }

    public function response(Request $request)
    {
        $color = 'error';
        $title = 'Payment Failed';
        $description = 'Your payment has been failed. Please try again later or contact with administrator.';
        $icon = 'mdi-alert-circle-outline';
        $modalProps = [];

        $payment = null;
        if ($request->get('id')) {
            $payment = Payment::find($request->get('id'));
        }

        // dd($payment, $request->all());
        // try {

        // } catch (\Throwable $th) {

        //     try {
        //         Mail::raw('There was an error updating the price after payment.\n\n' .
        //             'Error details:\n' .
        //             'Payment ID: ' . request()->get('id') . '\n\n' .
        //             'Error: ' . $th->getMessage() . '\n\n' .
        //             'Trace: ' . $th->getTraceAsString(), function ($message) {
        //                 $message->to('oguzhan@unusualgrowth.com')
        //                     ->subject('Payment Price Update Error');
        //             });
        //     } catch (\Throwable $th) {
        //         // throw $th;
        //     }
        // }

        if ($request->status == 'success') {
            $color = 'success';
            $title = 'Payment Complete';
            $description = 'Congratulations! Your has been successfully completed.';
            $icon = 'mdi-check-decagram-outline';
            $modalProps = [
                'noCancelButton' => true,
                'confirmText' => __('Continue'),
            ];

            if ($payment) {
                // $newPrice = $payment->price->replicate();
                // $newPrice->saveQuietly();

                // $newPrice->update([
                //     'raw_amount' => 0,
                //     'discount_percentage' => 0,
                //     'vat_amount' => 0,
                // ]);
            }
        } else {
            $modalProps = [
                'noConfirmButton' => true,
            ];
        }

        $modularityPayload = $payment ? $payment->parameters->modularity ?? new \stdClass : new \stdClass;

        return redirect(merge_url_query($modularityPayload->previous_url ?? route('admin.dashboard'), [
            'modalService' => [
                'component' => 'ue-recursive-stuff',
                'props' => [
                    'configuration' => Component::makeDiv()
                        ->setElements([
                            Component::makeVIcon()
                                ->setAttributes([
                                    'icon' => $icon,
                                    'size' => 'x-large',
                                    'color' => $color,
                                ]),
                            Component::makeUeTitle()
                                ->setAttributes([
                                    'tag' => 'h3',
                                    'type' => 'h3',
                                    'text' => $title,
                                    'color' => $color,
                                    'weight' => 'regular',
                                    'transform' => 'capitalize',
                                    'justify' => 'center',
                                ]),
                            Component::makeUeTitle()
                                ->setAttributes([
                                    'type' => 'body-2',
                                    'text' => $description,
                                    'color' => 'grey-darken-1',
                                    'weight' => 'regular',
                                    'transform' => 'none',
                                    'justify' => 'center',
                                ]),
                        ])
                        ->render(),
                ],
                'modalProps' => $modalProps,
            ],
        ]));
    }
}

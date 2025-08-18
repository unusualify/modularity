<?php

namespace Modules\SystemPayment\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\SystemNotification\Events\PaymentCompleted;
use Modules\SystemNotification\Events\PaymentFailed;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Unusualify\Modularity\Entities\Enums\PaymentStatus;
use Unusualify\Modularity\Facades\CurrencyExchange;
use Unusualify\Modularity\Facades\Filepond;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\View\Component;
use Unusualify\Payable\Payable;

class PriceController extends Controller
{
    protected $currencyService;

    public function pay(Request $request)
    {
        $priceTable = (new Price)->getTable();
        $request->validate([
            'price_id' => 'required|exists:' . $priceTable . ',id',
        ]);

        $params = $request->all();
        $price = Price::with('currency', 'vatRate')->find($params['price_id']);

        $previousUrl = url()->previous();

        if($price->payment && $price->payment->status == PaymentStatus::COMPLETED){
            $modalService = $this->createModalService('warning', 'mdi-alert-circle-outline', 'Paid Before', 'This price has already been paid! Please check the payment status.', [
                'noCancelButton' => true,
            ]);

            if($request->ajax()){
                return response()->json([
                    'status' => MessageStage::ERROR,
                    'message' => 'Payment already completed',
                    'redirector' => merge_url_query($previousUrl ?? route('admin.dashboard'), [
                        'modalService' => $modalService,
                    ]),
                ], 403);
            }

            return redirect(merge_url_query($previousUrl ?? route('admin.dashboard'), [
                'modalService' => $modalService,
            ]));
        }

        $user = Auth::user();
        $company = $user->company;
        $isTransfer = false;

        $requestCurrencyIso4217 = null;
        $paymentService = null;

        if (isset($params['payment_service_id'])) {
            $paymentService = PaymentService::isTransfer()->find($params['payment_service_id']);

            if (! $paymentService) {
                return response()->json([
                    'status' => MessageStage::ERROR,
                    'message' => 'Payment service is not transferable',
                ], 403);
            }

            $isTransfer = true;
            $request->validate([
                'bank_receipt' => function($attribute, $value, $fail){
                    if(!$value){
                        $fail('The bank receipt is required.');
                    }

                    if(!is_array($value)){
                        $fail('The bank receipt must be an array.');
                    }

                    if(count($value) == 0){
                        $fail('There must be at least one file.');
                    }

                },
                'tos' => 'required|in:true,1',
            ]);

            if(isset($params['currency_id'])){
                $requestCurrencyIso4217 = PaymentCurrency::find($params['currency_id'])->iso_4217;
            }

        } else if(!isset($params['payment_service'])) {
            if($request->ajax()) {
                return response()->json([
                    'status' => MessageStage::ERROR,
                    'message' => 'Not compatible payment service',
                ], 403);
            }

            return redirect()->back()->with('error', 'Not compatible payment service');
        } else {
            $requestCurrencyIso4217 = $params['payment_service']['currency']['iso_4217'];
        }

        $rawAmount = $price->discounted_raw_amount;
        $totalAmount = $price->total_amount;

        $currency = $price->currency;
        $converted = false;
        $exchangeRate = null;

        if (Str::upper($currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
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

        $orderId = uniqid('ORD');
        $modularityPayload = [
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
        ];

        if(!$isTransfer){
            if (isset($params['payment_service']) && $params['payment_service']['payment_method'] == -1) {
                $paymentCurrency = PaymentCurrency::find($currency->id);
                $paymentService = $paymentCurrency->paymentService;

                if (! $paymentService) {
                    throw new \Exception('Payment service not found for currency ' . $paymentCurrency->iso_4217);
                }
            } else if(isset($params['payment_service'])){
                $paymentService = PaymentService::find($params['payment_service']['payment_method']);
            }
        }  else {
            // get the url host
            $modularityPayload['previous_url'] = $request->header('referer');

            $paymentPayload = [
                'amount' => $totalAmount,
                'currency' => $currency->iso_4217,
                'currency_id' => $currency->id,
                'email' => $user->email,
                'order_id' => $orderId,
                'installment' => $params['installment'] ?? 1,
                'status' => PaymentStatus::COMPLETED,
                'payment_gateway' => $paymentService->key,
                'payment_service_id' => $paymentService->id,
                'parameters' => [
                    'modularity' => $modularityPayload,
                ],
                'response' => [],
            ];

            $color = 'success';
            $icon = 'mdi-check-decagram-outline';
            $title = 'Payment Complete';
            $description = 'Thank you for your payment. When your transfer is completed, you will be informed.';
            $modalProps = [
                'noCancelButton' => true,
                'confirmText' => __('Continue'),
            ];

            $payment = $price->updateOrNewPayment($paymentPayload);

            Filepond::saveFile($payment, $request->bank_receipt, 'receipts');

            PaymentCompleted::dispatch($payment);

            if($request->ajax()){
                return response()->json([
                    'status' => MessageStage::SUCCESS,
                    'message' => 'Payment created',
                    'redirector' => merge_url_query($modularityPayload['previous_url'] ?? route('admin.dashboard'), [
                        'modalService' => $this->createModalService($color, $icon, $title, $description, $modalProps),
                    ]),
                    // 'payment' => $payment,
                ]);
            }

            return redirect(merge_url_query($modularityPayload['previous_url'] ?? route('admin.dashboard'), [
                'modalService' => $this->createModalService($color, $icon, $title, $description, $modalProps),
            ]));
        }

        // dd($params);

        $payable = new Payable($paymentService->key);
        Session::put('payable_payment_service', $paymentService->key);

        $payload = [
            'amount' => $totalAmount,
            'currency' => $currency->iso_4217,
            'order_id' => $orderId,
            'installment' => $request->get('installment') ?? '1',

            'payment_group' => 'PRODUCT',
            'locale' => app()->getLocale(),

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

            'modularity' => $modularityPayload,
        ];

        $paymentPayload = [
            'price_id' => $price->id,
            'payment_service_id' => $paymentService->id,
            'currency_id' => $currency->id,
        ];

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
                PaymentCompleted::dispatch($payment);
                // $newPrice = $payment->price->replicate();
                // $newPrice->saveQuietly();

                // $newPrice->update([
                //     'raw_amount' => 0,
                //     'discount_percentage' => 0,
                //     'vat_amount' => 0,
                // ]);
            }
        } else {
            if ($payment) {
                PaymentFailed::dispatch($payment);
            }
            $modalProps = [
                'noConfirmButton' => true,
            ];
        }

        $modularityPayload = $payment ? $payment->parameters->modularity ?? new \stdClass : new \stdClass;

        return redirect(merge_url_query($modularityPayload->previous_url ?? route('admin.dashboard'), [
            'modalService' => $this->createModalService($color, $icon, $title, $description, $modalProps),
        ]));
    }

    protected function createModalBody(string $color = 'success', $icon = 'mdi-check-decagram-outline', string $title, string $description, array $modalProps = [])
    {
        return Component::makeDiv()
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
                        'color' => $color,
                        'weight' => 'regular',
                        'transform' => 'capitalize',
                        'justify' => 'center',
                    ])
                    ->setElements($title),
                Component::makeUeTitle()
                    ->setAttributes([
                        'type' => 'body-2',
                        'color' => 'grey-darken-1',
                        'weight' => 'regular',
                        'transform' => 'none',
                        'justify' => 'center',
                    ])
                    ->setElements($description),
            ])
            ->render();
    }

    protected function createModalService(string $color = 'success', $icon = 'mdi-check-decagram-outline', string $title, string $description, array $modalProps = [])
    {
        return [
            'component' => 'ue-recursive-stuff',
            'props' => [
                'configuration' => $this->createModalBody($color, $icon, $title, $description, $modalProps),
            ],
            'modalProps' => $modalProps,
        ];
    }
}

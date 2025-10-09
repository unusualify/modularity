<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Facades\Modularity;

class PaymentServiceHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;
        $input['type'] = 'input-payment-service';

        $input['default_payment_service'] = config('modularity.default_payment_service');
        $input['currencyConversionEndpoint'] = route('currency.convert');

        $input['supportedCurrencies'] = ! $this->skipQueries
            ? PaymentCurrency::whereHas('paymentServices')
                ->orWhereHas('paymentService')
                ->with('paymentServices', 'paymentService')
                ->get()
                ->each(function ($item) {
                    $item->append('has_built_in_form');
                })
            : [];

        $input['items'] = ! $this->skipQueries
            ? PaymentService::published()
                ->isExternal()
                ->orWhere(fn ($query) => $query->published()->isTransfer())
                ->with('paymentCurrencies')
                ->get()
                ->toArray()
            : [];

        $paymentServices = ! $this->skipQueries
            ? PaymentService::published()
                ->where('is_internal', 1)
                ->with(['paymentCurrencies', 'cardTypes'])
                ->get()
                ->all()
            : [];

        $mappedData = [];

        foreach ($paymentServices as $paymentService) {
            foreach ($paymentService->internalPaymentCurrencies as $internalPaymentCurrency) {
                $currencyCode = $internalPaymentCurrency->iso_4217 ?? '';

                if (! isset($mappedData[$currencyCode])) {
                    $mappedData[$currencyCode] = [];
                }

                foreach ($paymentService->cardTypes as $cardType) {
                    $cardInfo = [
                        'name' => mb_strtolower($cardType->name ?? ''),
                        'logo' => $cardType->image('logo', locale: 'en'),
                    ];

                    if ($cardInfo['name'] && ! $this->cardExists($mappedData[$currencyCode], $cardInfo['name'])) {
                        $mappedData[$currencyCode][] = $cardInfo;
                    }
                }
            }
        }

        $input['currencyCardTypes'] = $mappedData;

        $input['transferFormSchema'] = modularity_format_inputs([
            [
                'type' => 'hidden',
                'name' => 'price_id',
            ],
            [
                'type' => 'hidden',
                'name' => 'payment_service_id',
            ],
            [
                'type' => 'hidden',
                'name' => 'currency_id',
            ],
            [
                'type' => 'filepond',
                'name' => 'bank_receipt',
                'label' => 'Upload Transfer Receipt',
                'col' => ['cols' => 12],
                'acceptedExtensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max-files' => 2,
                'min' => 1,
                'default' => [],
            ],
            [
                'type' => 'checkbox',
                'name' => 'tos',
                'col' => ['cols' => 12],
                'label' => 'I have made the transfer',
                'rules' => 'required',
            ],
        ]);

        $input['includeTransactionFee'] = Modularity::shouldIncludeTransactionFee();

        $input['paymentUrl'] = route('admin.system.system_payment.pay');
        $input['checkoutUrl'] = route('admin.system.system_payment.checkout');
        $input['completeUrl'] = route('admin.system.system_payment.payment.response');

        return $input;
    }

    private function cardExists($currencyCards, $cardName)
    {
        return collect($currencyCards)->contains(function ($card) use ($cardName) {
            return $card['name'] === $cardName;
        });

    }
}

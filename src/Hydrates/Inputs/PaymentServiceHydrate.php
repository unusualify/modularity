<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;

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

        $input['api'] = route('currency.convert');

        $input['currencies'] = ! $this->skipQueries
            ? PaymentCurrency::whereHas('paymentServices')
                ->orWhereHas('paymentService')
                ->with('paymentServices', 'paymentService')
                ->get()
            : [];

        $input['items'] = ! $this->skipQueries
            ? PaymentService::published()->where('is_external', 1)->with('paymentCurrencies')->get()->toArray()
            : [];
        $paymentServices = ! $this->skipQueries
            ? PaymentService::published()->where('is_internal', 1)->with(['paymentCurrencies', 'cardTypes'])->get()->all()
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

        return $input;
    }

    private function cardExists($currencyCards, $cardName)
    {
        return collect($currencyCards)->contains(function ($card) use ($cardName) {
            return $card['name'] === $cardName;
        });

    }
}

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

        $ps = new PaymentService;
        $input['currencies'] = !$this->skipQueries
            ? PaymentCurrency::whereHas('paymentServices')->with('paymentServices')->get()->all()
            : [];
        $input['items'] = !$this->skipQueries
            ? $ps->where('is_external', 1)->with('paymentCurrencies')->get()->all()
            : [];
        $cct = !$this->skipQueries
            ? $ps->where('is_internal', 1)->with(['paymentCurrencies', 'cardTypes'])->get()->all()
            : [];

        $mappedData = [];
        foreach ($cct as $service) {
            foreach ($service->paymentCurrencies as $currency) {
                $currencyCode = $currency->iso_4217 ?? '';
                if (! $currencyCode) {
                    continue;
                }

                if (! isset($mappedData[$currencyCode])) {
                    $mappedData[$currencyCode] = [];
                }

                foreach ($service->cardTypes as $cardType) {
                    $cardInfo = [
                        'name' => mb_strtolower($cardType->name ?? ''),
                        'logo' => $this->getCardLogo($cardType),
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

    private function getCardLogo($cardType)
    {
        $logoMedia = $cardType->medias->first(function ($media) {
            return $media->pivot->role === 'logo';
        });

        return $logoMedia?->uuid ?? null;
    }

    private function cardExists($currencyCards, $cardName)
    {
        return collect($currencyCards)->contains(function ($card) use ($cardName) {
            return $card['name'] === $cardName;
        });

    }
}

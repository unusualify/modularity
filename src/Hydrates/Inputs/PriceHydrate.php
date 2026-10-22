<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Repositories\VatRateRepository;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Http\Requests\Request;

class PriceHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'prices',
        'col' => [
            'cols' => 6,
            'sm' => 5,
            'md' => 4,
        ],
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-price';
        $input['label'] ??= __('Prices');
        $input['clearable'] = false;

        $input['priceInputName'] = Price::$priceSavingKey ?? 'price_value';
        $defaultPriceAttributes = (new Price)->defaultAttributes();

        $defaultValue = $input['default'] ?? 0.01;
        $input['default'] = [
            $defaultPriceAttributes,
        ];
        foreach ($input['default'] as $key => $value) {
            $input['default'][$key][Price::$priceSavingKey] = $defaultValue;
        }

        $provider = App::make(CurrencyProviderInterface::class);
        $input['items'] = (! $this->skipQueries && $provider->isAvailable())
            ? $provider->getCurrenciesForSelect()
            : [];

        if (isset($input['hasVatRate']) && $input['hasVatRate']) {
            $input['vatRates'] = ! $this->skipQueries
                ? App::make(VatRateRepository::class)->list(['name', 'rate'])->map(function ($item) {
                    return [
                        'title' => $item['name'] . ' (' . $item['rate'] . '%)',
                        'value' => $item['id'],
                        'rate' => $item['rate'],
                    ];
                })->toArray()
                : [];
        }

        $userCurrency = method_exists(Request::class, 'getCachedUserCurrency') ? Request::getCachedUserCurrency() : null;
        $input['default'][0]['currency_id'] = $userCurrency?->id ?? ($input['items'][0]['id'] ?? 1);

        return $input;
    }
}

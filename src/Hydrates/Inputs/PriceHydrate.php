<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Repositories\VatRateRepository;
use Unusualify\Modularity\Http\Requests\Request;

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
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-price';
        $input['label'] ??= __('Prices');
        $input['clearable'] = false;

        $input['priceInputName'] = Price::$priceSavingKey ?? 'price_value';
        $defaultPriceAttributes = (new Price())->defaultAttributes();
        $input['default'] = [
            $defaultPriceAttributes,
        ];

        $query = Currency::query()->select(['id', 'symbol as name', 'iso_4217 as iso']);
        $onlyBaseCurrency = modularityConfig('services.currency_exchange.active');


        if ($onlyBaseCurrency) {
            $baseCurrency = modularityConfig('services.currency_exchange.base_currency');
            $query = $query->where('iso_4217', mb_strtoupper($baseCurrency));
        }

        if (isset($input['hasVatRate']) && $input['hasVatRate']) {
            $input['vatRates'] = !$this->skipQueries
                ? App::make(VatRateRepository::class)->list(['name', 'rate'])->map(function ($item) {
                    return [
                        'title' => $item['name'] . ' (' . $item['rate'] . '%)',
                        'value' => $item['id'],
                        'rate' => $item['rate'],
                    ];
                })->toArray()
                : [];

            // dd($input);
        }

        $input['items'] = !$this->skipQueries
            ? $query->get()->toArray()
            : [];

        $input['default'][0]['currency_id'] = Request::getUserCurrency()->id;

        return $input;
    }
}

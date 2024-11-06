<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Modules\SystemPricing\Entities\Currency;
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
        'default' => [
            [
                'display_price' => '',
                'currency_id' => 1,
            ],
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

        $query = Currency::query()->select(['id', 'symbol as name', 'iso_4217 as iso']);

        $onlyBaseCurrency = unusualConfig('services.currency_exchange.active');

        if ($onlyBaseCurrency) {
            $baseCurrency = unusualConfig('services.currency_exchange.base_currency');
            $query = $query->where('iso_4217', mb_strtoupper($baseCurrency));
        }

        $input['items'] = $query->get()->toArray();

        $input['default'][0]['currency_id'] = Request::getUserCurrency()->id;

        return $input;
    }
}

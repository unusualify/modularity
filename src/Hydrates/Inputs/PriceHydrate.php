<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Modules\SystemPricing\Entities\Currency;

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

        $input['currencies'] = Currency::query()->select(['id', 'symbol as name'])->get()->toArray();

        return $input;
    }
}

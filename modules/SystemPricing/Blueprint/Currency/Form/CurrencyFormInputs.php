<?php

namespace Modules\SystemPricing\Blueprint\Currency\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CurrencyFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'symbol',
                'label' => 'Symbol',
                'type' => 'text',
            ],
            [
                'name' => 'iso_4217',
                'label' => 'ISO 4217',
                'type' => 'text',
            ],
        ];
    }
}

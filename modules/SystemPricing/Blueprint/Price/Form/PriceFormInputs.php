<?php

namespace Modules\SystemPricing\Blueprint\Price\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PriceFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'priceable_type',
                'label' => 'Priceable Type',
                'type' => 'select',
                'options' => [
                    'priceable_type' => 'Priceable Type',
                ],
            ],

        ];
    }
}

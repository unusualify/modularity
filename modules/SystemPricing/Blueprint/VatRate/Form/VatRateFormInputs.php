<?php

namespace Modules\SystemPricing\Blueprint\VatRate\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class VatRateFormInputs implements ModuleRouteInputsProvider
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
                'name' => 'rate',
                'label' => 'Rate',
                'type' => 'text',
                'ext' => 'number',
            ],
        ];
    }
}

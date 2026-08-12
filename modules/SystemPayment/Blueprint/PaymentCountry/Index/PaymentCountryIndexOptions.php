<?php

namespace Modules\SystemPayment\Blueprint\PaymentCountry\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCountryIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => true,
            'editOnModal' => true,
            'isRowEditing' => false,
            'rowActionsType' => 'inline',
        ];
    }
}

<?php

namespace Modules\SystemPayment\Blueprint\PaymentCurrency\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCurrencyIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => false,
            'editOnModal' => true,
            'isRowEditing' => false,
            'rowActionsType' => 'inline',
            // 'noForm' => true,
        ];
    }
}

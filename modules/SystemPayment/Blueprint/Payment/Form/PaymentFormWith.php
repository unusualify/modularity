<?php

namespace Modules\SystemPayment\Blueprint\Payment\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentFormWith implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>|list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'price',
            'paymentable',
        ];
    }
}

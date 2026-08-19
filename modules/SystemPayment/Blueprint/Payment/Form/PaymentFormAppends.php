<?php

namespace Modules\SystemPayment\Blueprint\Payment\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentFormAppends implements ModuleRouteBlueprintProvider
{
    /**
     * @return list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'amount_formatted',
            'paymentable',
        ];
    }
}

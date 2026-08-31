<?php

namespace Modules\SystemPayment\Blueprint\MyPayment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyPaymentIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'subtitle' => __('You can check all the payments that you receive and the invoices related to the payments here according to company list.'),
            'formEditTitleTranslationKey' => 'messages.my_payment.edit',

            'createOnModal' => true,
            'editOnModal' => true,
            'isRowEditing' => false,
            'rowActionsType' => 'inline',
        ];
    }
}

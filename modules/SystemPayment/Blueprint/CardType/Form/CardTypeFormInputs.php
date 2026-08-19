<?php

namespace Modules\SystemPayment\Blueprint\CardType\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CardTypeFormInputs implements ModuleRouteInputsProvider
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
                'type' => 'text',
                'name' => 'card_type',
                'label' => __('Card Type'),
                'rules' => 'sometimes|required',
            ],
            [
                'name' => 'paymentServices',
                'label' => __('Payment Services'),
                'type' => 'select',
                'multiple',
                'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
            ],
            [
                'label' => __('Logo'),
                'type' => 'image',
                'name' => 'logo',
                'rules' => 'sometimes|required:array',
                'isIcon' => true,
            ],
        ];
    }
}

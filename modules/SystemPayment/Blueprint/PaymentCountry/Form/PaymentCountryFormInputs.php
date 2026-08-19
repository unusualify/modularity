<?php

namespace Modules\SystemPayment\Blueprint\PaymentCountry\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCountryFormInputs implements ModuleRouteInputsProvider
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
                'readonly' => true,
                'clearable' => false,
                'noSubmit' => true,
            ],
            [
                'type' => 'json-repeater',
                'name' => 'currency_vat_rate',
                'default' => [],
                'label' => __('Supported Languages'),
                'formRowAttribute' => [
                    'noGutters' => true,
                ],
                'col' => [
                    'cols' => 12,
                ],
                'autoIdGenerator' => false,
                'isUnique' => true,
                'uniqueField' => 'currency_id',
                'uniqueValue' => 'iso_4217',
                'asObject' => true,
                'schema' => [
                    [
                        'type' => 'select',
                        'name' => 'currency_id',
                        'label' => __('Currency'),
                        'connector' => 'SystemPayment:PaymentCurrency|repository:list:column=name,iso_4217:scopes=hasAnyPaymentService',
                        'itemValue' => 'iso_4217',
                        'itemTitle' => 'name',
                        'hideDetails' => 'auto',
                        'col' => [
                            'cols' => 6,
                            'class' => 'pr-4',
                        ],
                        'rules' => 'required',
                    ],
                    [
                        'type' => 'select',
                        'name' => 'vat_rate_id',
                        'label' => __('VAT Rate'),
                        'connector' => 'SystemPricing:VatRate|repository:list:column=name,rate:appends=name_with_rate',
                        'itemTitle' => 'name_with_rate',
                        'hideDetails' => 'auto',
                        'col' => [
                            'cols' => 6,
                        ],
                        'rules' => 'required',
                    ],
                ],
            ],
        ];
    }
}

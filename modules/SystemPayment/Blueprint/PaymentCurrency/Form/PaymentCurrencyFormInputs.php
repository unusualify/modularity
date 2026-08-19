<?php

namespace Modules\SystemPayment\Blueprint\PaymentCurrency\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentCurrencyFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'name',
                'label' => __('Name'),
                'type' => 'text',
                'readonly' => true,
                'clearable' => false,
                'noSubmit' => true,
            ],
            [
                'name' => 'payment_service_id',
                'label' => __('Internal Payment Service (for credit card payment)'),
                'type' => 'select',
                'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                'multiple' => false,
                'itemTitle' => 'name',
            ],
            [
                'type' => 'json-repeater',
                'name' => 'default_vat_rates',
                'label' => __('Default VAT Rates'),
                'default' => [],
                'formRowAttribute' => [
                    'noGutters' => true,
                ],
                'col' => [
                    'cols' => 12,
                ],
                'isUnique' => true,
                'uniqueField' => 'company_type',
                'uniqueValue' => 'id',
                'autoIdGenerator' => false,
                'asObject' => true,
                'schema' => [
                    [
                        'type' => 'select',
                        'name' => 'company_type',
                        'label' => __('Company Type'),
                        'hideDetails' => 'auto',
                        'itemValue' => 'id',
                        'itemTitle' => 'name',
                        'itemValueType' => 'string',
                        'col' => [
                            'cols' => 6,
                            'class' => 'pr-4',
                        ],
                        'items' => [
                            [
                                'id' => 'corporate',
                                'name' => __('Corporate Company'),
                            ],
                            [
                                'id' => 'personal',
                                'name' => __('Personal Company'),
                            ],
                        ],
                        'rules' => 'required',
                    ],
                    [
                        'name' => 'vat_rate_id',
                        'label' => __('VAT Rate'),
                        'type' => 'select',
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

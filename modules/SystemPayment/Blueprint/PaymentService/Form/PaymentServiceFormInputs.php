<?php

namespace Modules\SystemPayment\Blueprint\PaymentService\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentServiceFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Payment Service Name',
                'rules' => 'sometimes|required',
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            [
                'name' => 'key',
                'label' => 'Payment Service Slug',
                'type' => 'text',
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            // [
            //     'name' => 'payment-service',
            //     'label' => 'Payment',
            //     'type' => 'payment-service',
            //     'connector' => 'SystemPayment:PaymentService|repository:listAll'
            // ],
            [
                'name' => 'paymentCurrencies',
                'label' => __('Payment Currencies'),
                'type' => 'select',
                'multiple',
                'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentCurrencyRepository',
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            [
                'name' => 'transaction_fee_percentage',
                'label' => __('Transaction Fee Percentage'),
                'type' => 'number-input',
                'clearable' => false,
                'default' => 0.00,
                'precision' => 2,
                'max' => 100.00,
                'min' => 0.00,
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            [
                'type' => 'radio-group',
                'name' => 'type',
                'label' => __('Service Type'),
                'spreadable' => true,
                'items' => [
                    [
                        'name' => 'Standard',
                        'id' => 1,
                        'transfer_details_toggleInputValue' => false,
                    ],
                    [
                        'name' => 'Transfer',
                        'id' => 2,
                        'transfer_details_toggleInputValue' => true,
                    ],
                ],
                'formEvents' => [
                    [
                        'toggleInput',
                        'transfer_details',
                        'items.*.transfer_details_toggleInputValue',
                    ],
                ],
            ],
            [
                'type' => 'group',
                'col' => ['cols' => 12, 'lg' => 12],
                'name' => 'transfer_details',
                'label' => 'Transfer Details',
                'class' => 'd-none',
                'spreadable' => true,
                'schema' => [
                    [
                        'type' => 'text',
                        'name' => 'account_holder',
                        'label' => __('Account Holder'),
                        'col' => ['cols' => 12, 'lg' => 6],
                        'rules' => '',
                        '_cached-rawRules' => 'required',
                        // '_cached-rules' => 'required',
                    ],
                    [
                        'type' => 'text',
                        'name' => 'iban',
                        'label' => __('IBAN'),
                        'col' => ['cols' => 12, 'lg' => 6],
                        'rules' => '',
                        // '_cached-rawRules' => 'required',
                        '_cached-rules' => 'required',
                    ],
                    [
                        'type' => 'text',
                        'name' => 'swift_code',
                        'label' => __('SWIFT/BIC'),
                        'col' => ['cols' => 12, 'lg' => 6],
                        'rules' => '',
                        '_cached-rawRules' => 'required',
                        // '_cached-rules' => 'required',
                    ],
                    [
                        'type' => 'text',
                        'name' => 'description',
                        'label' => __('Payment Description'),
                        'col' => ['cols' => 12, 'lg' => 6],
                        'rules' => '',
                        '_cached-rawRules' => 'required',
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'address',
                        'label' => __('Bank Name & Address'),
                        'col' => ['cols' => 12],
                        'rules' => '',
                        '_cached-rawRules' => 'required',
                    ],
                ],
            ],

            [
                'name' => 'is_external',
                'label' => __('Is an external service ?'),
                'type' => 'checkbox',
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            [
                'name' => 'is_internal',
                'label' => __('Is an internal service ?'),
                'type' => 'checkbox',
                'col' => ['cols' => 12, 'lg' => 6],
            ],
            [
                'label' => __('Logo'),
                'type' => 'image',
                'name' => 'logo',
                // 'rules' => 'sometimes|required:array',
                'isIcon' => true,
                'col' => ['cols' => 12, 'lg' => 6],
                'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
            ],
            [
                'label' => __('Button Logo'),
                'type' => 'image',
                'name' => 'button_logo',
                'rules' => '',
                'isIcon' => true,
                'col' => ['cols' => 12, 'lg' => 6],
                'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
            ],
            [
                'name' => 'button_style',
                'label' => __('Button Style'),
                'type' => 'textarea',
            ],
        ];
    }
}

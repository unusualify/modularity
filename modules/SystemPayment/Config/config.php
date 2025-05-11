<?php

use Unusualify\Payable\Models\Enums\PaymentStatus;

return [
    'name' => 'SystemPayment',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Payments',
    'base_prefix' => false,
    'routes' => [
        'payment_service' => [
            'name' => 'PaymentService',
            'headline' => 'Payment Services',
            'url' => 'payment-services',
            'route_name' => 'payment_service',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Service Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Service Slug',
                    'key' => 'key',
                    'searchable' => true,
                ],
                [
                    'title' => 'Credit Card Currencies',
                    'key' => 'internalPaymentCurrencies',
                ],
                [
                    'title' => 'External Supported Currencies',
                    'key' => 'paymentCurrencies',
                ],
                [
                    'title' => 'Status',
                    'key' => 'published',
                    'formatter' => [
                        'switch',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'medium',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Payment Service Name',
                    'rules' => 'sometimes|required',
                ],
                [
                    'name' => 'key',
                    'label' => 'Payment Service Slug',
                    'type' => 'text',
                ],
                // [
                //     'name' => 'payment-service',
                //     'label' => 'Payment',
                //     'type' => 'payment-service',
                //     'connector' => 'SystemPayment:PaymentService|repository:listAll'
                // ],
                [
                    'name' => 'paymentCurrencies',
                    'label' => 'Payment Currencies',
                    'type' => 'select',
                    'multiple',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentCurrencyRepository',
                ],
                [
                    'name' => 'is_external',
                    'label' => 'Is an external service ?',
                    'type' => 'checkbox',
                ],
                [
                    'name' => 'is_internal',
                    'label' => 'Is an internal service ?',
                    'type' => 'checkbox',
                ],
                [
                    'label' => 'Logo',
                    'type' => 'image',
                    'name' => 'logo',
                    'rules' => 'sometimes|required:array',
                    'isIcon' => true,
                ],
                [
                    'label' => 'Button Logo',
                    'type' => 'image',
                    'name' => 'button_logo',
                    'rules' => '',
                    'isIcon' => true,
                ],
                [
                    'name' => 'button_style',
                    'label' => 'Button Style',
                    'type' => 'text',
                ],
            ],
        ],
        'payment' => [
            'name' => 'Payment',
            'headline' => 'Payments',
            'url' => 'payments',
            'route_name' => 'payment',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => false,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'table_row_actions' => [
                [
                    'name' => 'cancel',
                    'icon' => 'mdi-credit-card-remove-outline',
                    'color' => 'error',
                    'conditions' => [
                        ['is_cancelable', '=', true],
                    ],
                    'url' => [
                        'payable.cancel',
                        [
                            'payment' => ':id',
                        ],
                    ],
                    'hasDialog' => true,
                    'dialogQuestion' => __('Are you sure you want to cancel this payment?'),
                ],
                [
                    'name' => 'refund',
                    'icon' => 'mdi-credit-card-refund-outline',
                    'color' => 'warning',
                    'conditions' => [
                        ['is_refundable', '=', true],
                    ],
                    'url' => [
                        'payable.refund',
                        [
                            'payment' => ':id',
                        ],
                    ],
                    'hasDialog' => true,
                    'dialogQuestion' => __('Are you sure you want to refund this payment?'),
                ],
            ],
            'headers' => [
                [
                    'title' => 'Owner Id',
                    'key' => 'price.priceable',
                    'itemTitle' => 'id',
                    'formatter' => [
                        'edit',
                    ],
                    // 'itemTitle' => 'content->headline',
                ],
                [
                    'title' => 'Owner Type',
                    'key' => 'price',
                    'itemTitle' => 'priceable_type',
                    // 'itemTitle' => 'content->headline',
                ],
                [
                    'title' => 'Service',
                    'key' => 'paymentService',
                    'itemTitle' => 'title',
                    'formatter' => [
                        'chip',
                        [
                            'variant' => 'outlined',
                            'color' => 'primary',
                        ],
                    ],
                ],
                [
                    'title' => 'Amount',
                    'key' => 'amount_formatted',
                ],
                [
                    'title' => 'Status',
                    'key' => 'status',
                    'formatter' => [
                        'chip',
                        [
                            'size' => 'small',
                        ],
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'visible' => false,
                    'formatter' => [
                        'date',
                        'medium',
                    ],
                    'searchable' => false,
                    'sortable' => true,
                ],
                [
                    'title' => 'Last Update',
                    'key' => 'updated_at',
                    'formatter' => [
                        'date',
                        'numeric',
                    ],
                    'searchable' => false,
                    'sortable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'type' => 'select',
                    'name' => 'payment_service_id',
                    'label' => 'Payment Service',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                    'rules' => 'sometimes|required',
                    'editable' => false,
                ],
                [
                    'type' => 'filepond',
                    'name' => 'invoice',
                    'label' => 'Invoice',
                    'max' => 1,
                    'conditions' => [
                        ['status', '=', 'COMPLETED'],
                    ],
                    'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
                    'acceptedExtensions' => ['pdf'],
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'itemTitle' => 'name',
                    'itemValue' => 'value',
                    'items' => PaymentStatus::cases(),
                    'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
                ],
            ],
        ],
        'payment_currency' => [
            'name' => 'PaymentCurrency',
            'headline' => 'Payment Currencies',
            'url' => 'payment-currencies',
            'route_name' => 'payment_currency',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => false,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
                // 'noForm' => true,
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Services',
                    'key' => 'paymentServices',
                    'itemTitle' => 'name',
                ],
                [
                    'title' => 'Credit Card Service',
                    'key' => 'paymentService',
                    'itemTitle' => 'name',
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'medium',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'disabled',
                ],
                [
                    'name' => 'payment_service_id',
                    'label' => 'Internal Payment Service (for credit card payment)',
                    'type' => 'select',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                    'multiple' => false,
                    'itemTitle' => 'name',
                ],
            ],
        ],
        'card_type' => [
            'name' => 'CardType',
            'headline' => 'Card Types',
            'url' => 'card-types',
            'route_name' => 'card_type',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Slug',
                    'key' => 'card_type',
                    'searchable' => true,
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'medium',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                ],
                [
                    'type' => 'text',
                    'name' => 'card_type',
                    'label' => 'Card Type',
                    'rules' => 'sometimes|required',
                ],
                [
                    'name' => 'paymentServices',
                    'label' => 'Payment Services',
                    'type' => 'select',
                    'multiple',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                ],
                [
                    'label' => 'Logo',
                    'type' => 'image',
                    'name' => 'logo',
                    'rules' => 'sometimes|required:array',
                    'isIcon' => true,
                ],
            ],
        ],
    ],
];

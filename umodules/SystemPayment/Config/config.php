<?php

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
                    'title' => 'Payment Service Name',
                    'key' => 'title',
                    'searchable' => true,
                ],
                [
                    'title' => 'Payment Service Slug',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
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
                    'name' => 'title',
                    'label' => 'Payment Service Name',
                    'rules' => 'sometimes|required',
                ],
                [
                    'name' => 'name',
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
                    'label' => 'Images',
                    'type' => 'image',
                    'name' => 'images',
                    'rules' => 'sometimes|required:array',
                    'isIcon' => true,
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
                    'title' => 'Payment Service',
                    'key' => 'paymentService',
                ],
                [
                    'title' => 'System Payment Parent',
                    'key' => 'systemPaymentable',
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
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
                    'type' => 'select',
                    'name' => 'payment_service_id',
                    'label' => 'Payment Service',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                    'rules' => 'sometimes|required',
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
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
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
                    'name' => 'paymentServices',
                    'label' => 'Payment Service',
                    'type' => 'select',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                    'multiple',
                    'itemTitle' => 'title',
                ],
            ],
        ],
    ],
];

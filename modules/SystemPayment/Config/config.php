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
                        ]
                    ],
                ],
                [
                    'title' => 'Status',
                    'key' => 'status',
                    'formatter' => [
                        'chip',
                        [
                            'size' => 'small',
                        ]
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
                    'acceptedExtensions' => ['pdf'],
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
                    'title' => 'Card Type',
                    'key' => 'card_type',
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

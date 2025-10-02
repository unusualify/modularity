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
                    'label' => 'Payment Currencies',
                    'type' => 'select',
                    'multiple',
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentCurrencyRepository',
                ],
                [
                    'type' => 'radio-group',
                    'name' => 'type',
                    'label' => 'Service Type',
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
                    'ext' => [
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
                            'label' => 'Account Holder',
                            'col' => ['cols' => 12, 'lg' => 6],
                            'rules' => '',
                            '_cached-rawRules' => 'required',
                            // '_cached-rules' => 'required',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'iban',
                            'label' => 'IBAN',
                            'col' => ['cols' => 12, 'lg' => 6],
                            'rules' => '',
                            // '_cached-rawRules' => 'required',
                            '_cached-rules' => 'required',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'swift_code',
                            'label' => 'SWIFT/BIC',
                            'col' => ['cols' => 12, 'lg' => 6],
                            'rules' => '',
                            '_cached-rawRules' => 'required',
                            // '_cached-rules' => 'required',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'description',
                            'label' => 'Payment Description',
                            'col' => ['cols' => 12, 'lg' => 6],
                            'rules' => '',
                            '_cached-rawRules' => 'required',
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'address',
                            'label' => 'Bank Name & Address',
                            'col' => ['cols' => 12],
                            'rules' => '',
                            '_cached-rawRules' => 'required',
                        ],
                    ],
                ],

                [
                    'name' => 'is_external',
                    'label' => 'Is an external service ?',
                    'type' => 'checkbox',
                    'col' => ['cols' => 12, 'lg' => 6],
                ],
                [
                    'name' => 'is_internal',
                    'label' => 'Is an internal service ?',
                    'type' => 'checkbox',
                    'col' => ['cols' => 12, 'lg' => 6],
                ],
                [
                    'label' => 'Logo',
                    'type' => 'image',
                    'name' => 'logo',
                    // 'rules' => 'sometimes|required:array',
                    'isIcon' => true,
                    'col' => ['cols' => 12, 'lg' => 6],
                    'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
                ],
                [
                    'label' => 'Button Logo',
                    'type' => 'image',
                    'name' => 'button_logo',
                    'rules' => '',
                    'isIcon' => true,
                    'col' => ['cols' => 12, 'lg' => 6],
                    'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
                ],
                [
                    'name' => 'button_style',
                    'label' => 'Button Style',
                    'type' => 'textarea',
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
                'subtitle' => __('You can check all the payments that you receive and the invoices related to the payments here according to company list.'),

                'createOnModal' => false,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'table_row_actions' => [
                'cancel' => [
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
                'refund' => [
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
                    'allowedRoles' => ['superadmin'],
                    'formatter' => [
                        'edit',
                    ],
                    // 'itemTitle' => 'content->headline',
                ],
                [
                    'title' => 'Owner Type',
                    'key' => 'price',
                    'itemTitle' => 'priceable_type',
                    'allowedRoles' => ['superadmin'],
                    // 'itemTitle' => 'content->headline',
                ],
                [
                    'title' => 'Related',
                    'key' => 'paymentable',
                    'itemTitle' => 'id',
                    'allowedRoles' => ['superadmin'],
                ],
                [
                    'title' => 'Company',
                    'key' => 'company',
                    'itemTitle' => 'name',
                    'minWidth' => 150,
                ],
                [
                    'title' => 'Service',
                    'key' => 'paymentService',
                    // 'itemTitle' => 'title',
                    'formatter' => [
                        'chip',
                        [
                            'variant' => 'outlined',
                            'color' => 'primary',
                        ],
                    ],
                ],
                [
                    'title' => 'Total Price',
                    'key' => 'amount_formatted',
                ],
                [
                    'title' => 'Status',
                    'key' => 'status_vuetify_chip',
                    'formatter' => [
                        'dynamic',
                    ],
                ],
                [
                    'title' => 'User Email',
                    'key' => 'creator',
                    'itemTitle' => 'email',
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
                        'long',
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
                    'type' => 'preview',
                    'name' => 'description',
                    'label' => 'Description',
                    'col' => ['cols' => 12, 'lg' => 12],
                    'configuration' => [
                        'elements' => [
                            [
                                'tag' => 'ue-title',
                                'attributes' => [
                                    'classes' => 'mb-2',
                                    'padding' => 'a-0',
                                    'type' => 'body-2',
                                ],
                                'elements' => 'Description',
                            ],
                            [
                                'tag' => 'p',
                                'elements' => '${description??N/A}$',
                            ],
                        ],
                    ],
                    'conditions' => [
                        ['description', '!=', ''],
                        ['description', '!=', null],
                    ],
                ],
                [
                    'type' => 'preview',
                    'name' => 'bank_receipts',
                    'noSubmit' => true,
                    'default' => null,
                    'col' => ['cols' => 12, 'class' => 'mb-4'],
                    'configuration' => [
                        'elements' => [
                            [
                                'tag' => 'ue-title',
                                'attributes' => [
                                    'classes' => 'mb-2',
                                    'padding' => 'a-0',
                                    'type' => 'body-2',
                                ],
                                'elements' => 'Bank Receipts',
                            ],
                            [
                                'tag' => 'ue-filepond-preview',
                                'attributes' => [
                                    'source' => '${bank_receipts??N/A}$',
                                    'show-inline-file-name' => true,
                                    'max-file-name-length' => 30,
                                    'image-size' => 24,
                                ],
                            ],
                        ],
                    ],
                    'conditions' => [
                        ['bank_receipts', '>', 0],
                    ],
                    'creatable' => 'hidden',
                ],
                [
                    'type' => 'select',
                    'name' => 'payment_service_id',
                    'label' => 'Payment Service',
                    'col' => ['cols' => 12, 'lg' => 6],
                    'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
                    'rules' => 'sometimes|required',
                    'editable' => false,
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'col' => ['cols' => 12, 'lg' => 6],
                    'itemTitle' => 'name',
                    'itemValue' => 'value',
                    'items' => PaymentStatus::cases(),
                    'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
                    'rules' => 'required',
                ],
                [
                    'type' => 'filepond',
                    'name' => 'invoice',
                    'label' => 'Invoice',
                    'max' => 3,
                    'conditions' => [
                        ['status', '=', 'COMPLETED', 'REFUNDED', 'CANCELLED'],
                    ],
                    'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
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
        'my_payment' => [
            'name' => 'MyPayment',
            'headline' => 'Payments',
            'url' => 'my-payments',
            'route_name' => 'my_payment',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'scopes' => [
                'isMyCreation' => true,
            ],
            'table_options' => [
                'subtitle' => __('You can check all the payments that you receive and the invoices related to the payments here according to company list.'),
                'formEditTitleTranslationKey' => 'messages.my_payment.edit',

                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'table_row_actions' => [
                'show' => [
                    'name' => 'edit',
                    'merge' => true,
                    'icon' => 'mdi-eye',
                    'label' => __('Show'),
                ],
            ],
            'headers' => [
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
                    'title' => 'User Email',
                    'key' => 'creator',
                    'itemTitle' => 'email',
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
                    'type' => 'preview',
                    'name' => 'files',
                    'noSubmit' => true,
                    'default' => null,
                    'col' => ['cols' => 12, 'class' => 'mb-4'],
                    'configuration' => [
                        'tag' => 'v-card',
                        'attributes' => [
                            'link' => true,
                            'class' => 'mx-auto py-4 mb-4 h-100',
                            'variant' => 'elevated',
                            'title' => 'Invoices',
                        ],
                        'elements' => [
                            'tag' => 'v-card-text',
                            'elements' => [
                                'tag' => 'ue-filepond-preview',
                                'attributes' => [
                                    'source' => '$invoices',
                                    'show-inline-file-name' => true,
                                    'max-file-name-length' => 20,
                                    'image-size' => 24,
                                ],
                            ],
                        ],
                    ],
                    // 'conditions' => [
                    //     ['invoice', '>', 0],
                    // ],
                    'creatable' => 'hidden',
                ],
            ],
        ],
    ],
];

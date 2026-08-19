<?php

use Unusualify\Modularous\Entities\Enums\PaymentStatus;

return [
    'name' => 'SystemPayment',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Payments',
    'base_prefix' => false,
    'routes' => [
        'payment_service' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\PaymentService\Index\PaymentServiceIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\PaymentService\Index\PaymentServiceIndexOptions::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\PaymentService\Form\PaymentServiceFormInputs::class,
            ],
            'name' => 'PaymentService',
            'headline' => 'Payment Services',
            'url' => 'payment-services',
            'route_name' => 'payment_service',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'createOnModal' => true,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Service Name',
            //         'key' => 'name',
            //         'formatter' => [
            //             'edit',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Service Slug',
            //         'key' => 'key',
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Credit Card Currencies',
            //         'key' => 'internalPaymentCurrencies',
            //     ],
            //     [
            //         'title' => 'External Supported Currencies',
            //         'key' => 'paymentCurrencies',
            //     ],
            //     [
            //         'title' => 'Transaction Fee (%)',
            //         'key' => 'transaction_fee_percentage',
            //         'formatter' => [
            //             'chip',
            //         ],
            //     ],
            //     [
            //         'title' => 'Status',
            //         'key' => 'published',
            //         'formatter' => [
            //             'switch',
            //         ],
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'formatter' => [
            //             'date',
            //             'medium',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'type' => 'text',
            //         'name' => 'name',
            //         'label' => 'Payment Service Name',
            //         'rules' => 'sometimes|required',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            //     [
            //         'name' => 'key',
            //         'label' => 'Payment Service Slug',
            //         'type' => 'text',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            // [
            //     'name' => 'payment-service',
            //     'label' => 'Payment',
            //     'type' => 'payment-service',
            //     'connector' => 'SystemPayment:PaymentService|repository:listAll'
            // ],
            //     [
            //         'name' => 'paymentCurrencies',
            //         'label' => __('Payment Currencies'),
            //         'type' => 'select',
            //         'multiple',
            //         'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentCurrencyRepository',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            //     [
            //         'name' => 'transaction_fee_percentage',
            //         'label' => __('Transaction Fee Percentage'),
            //         'type' => 'number-input',
            //         'clearable' => false,
            //         'default' => 0.00,
            //         'precision' => 2,
            //         'max' => 100.00,
            //         'min' => 0.00,
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            //     [
            //         'type' => 'radio-group',
            //         'name' => 'type',
            //         'label' => __('Service Type'),
            //         'spreadable' => true,
            //         'items' => [
            //             [
            //                 'name' => 'Standard',
            //                 'id' => 1,
            //                 'transfer_details_toggleInputValue' => false,
            //             ],
            //             [
            //                 'name' => 'Transfer',
            //                 'id' => 2,
            //                 'transfer_details_toggleInputValue' => true,
            //             ],
            //         ],
            //         'ext' => [
            //             [
            //                 'toggleInput',
            //                 'transfer_details',
            //                 'items.*.transfer_details_toggleInputValue',
            //             ],
            //         ],
            //     ],
            //     [
            //         'type' => 'group',
            //         'col' => ['cols' => 12, 'lg' => 12],
            //         'name' => 'transfer_details',
            //         'label' => 'Transfer Details',
            //         'class' => 'd-none',
            //         'spreadable' => true,
            //         'schema' => [
            //             [
            //                 'type' => 'text',
            //                 'name' => 'account_holder',
            //                 'label' => __('Account Holder'),
            //                 'col' => ['cols' => 12, 'lg' => 6],
            //                 'rules' => '',
            //                 '_cached-rawRules' => 'required',
            // '_cached-rules' => 'required',
            //             ],
            //             [
            //                 'type' => 'text',
            //                 'name' => 'iban',
            //                 'label' => __('IBAN'),
            //                 'col' => ['cols' => 12, 'lg' => 6],
            //                 'rules' => '',
            // '_cached-rawRules' => 'required',
            //                 '_cached-rules' => 'required',
            //             ],
            //             [
            //                 'type' => 'text',
            //                 'name' => 'swift_code',
            //                 'label' => __('SWIFT/BIC'),
            //                 'col' => ['cols' => 12, 'lg' => 6],
            //                 'rules' => '',
            //                 '_cached-rawRules' => 'required',
            // '_cached-rules' => 'required',
            //             ],
            //             [
            //                 'type' => 'text',
            //                 'name' => 'description',
            //                 'label' => __('Payment Description'),
            //                 'col' => ['cols' => 12, 'lg' => 6],
            //                 'rules' => '',
            //                 '_cached-rawRules' => 'required',
            //             ],
            //             [
            //                 'type' => 'textarea',
            //                 'name' => 'address',
            //                 'label' => __('Bank Name & Address'),
            //                 'col' => ['cols' => 12],
            //                 'rules' => '',
            //                 '_cached-rawRules' => 'required',
            //             ],
            //         ],
            //     ],

            //     [
            //         'name' => 'is_external',
            //         'label' => __('Is an external service ?'),
            //         'type' => 'checkbox',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            //     [
            //         'name' => 'is_internal',
            //         'label' => __('Is an internal service ?'),
            //         'type' => 'checkbox',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //     ],
            //     [
            //         'label' => __('Logo'),
            //         'type' => 'image',
            //         'name' => 'logo',
            // 'rules' => 'sometimes|required:array',
            //         'isIcon' => true,
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
            //     ],
            //     [
            //         'label' => __('Button Logo'),
            //         'type' => 'image',
            //         'name' => 'button_logo',
            //         'rules' => '',
            //         'isIcon' => true,
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'imageCol' => ['cols' => 12, 'md' => 6, 'lg' => 6],
            //     ],
            //     [
            //         'name' => 'button_style',
            //         'label' => __('Button Style'),
            //         'type' => 'textarea',
            //     ],
            // ],
        ],
        'payment' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\Payment\Index\PaymentIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\Payment\Index\PaymentIndexOptions::class,
                'row_actions' => \Modules\SystemPayment\Blueprint\Payment\Index\PaymentIndexRowActions::class,
                'advanced_filters' => \Modules\SystemPayment\Blueprint\Payment\Index\PaymentIndexAdvancedFilters::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\Payment\Form\PaymentFormInputs::class,
                'with' => \Modules\SystemPayment\Blueprint\Payment\Form\PaymentFormWith::class,
                'appends' => \Modules\SystemPayment\Blueprint\Payment\Form\PaymentFormAppends::class,
            ],
            'name' => 'Payment',
            'headline' => 'Payments',
            'url' => 'payments',
            'route_name' => 'payment',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'subtitle' => __('You can check all the payments that you receive and the invoices related to the payments here according to company list.'),

            //     'createOnModal' => false,
            //     'editOnModal' => false,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'table_row_actions' => [
            //     'cancel' => [
            //         'name' => 'cancel',
            //         'icon' => 'mdi-credit-card-remove-outline',
            //         'color' => 'error',
            //         'conditions' => [
            //             ['is_cancelable', '=', true],
            //         ],
            //         'url' => [
            //             'payable.cancel',
            //             [
            //                 'payment' => ':id',
            //             ],
            //         ],
            //         'hasDialog' => true,
            //         'dialogQuestion' => __('Are you sure you want to cancel this payment?'),
            //     ],
            //     'refund' => [
            //         'name' => 'refund',
            //         'icon' => 'mdi-credit-card-refund-outline',
            //         'color' => 'warning',
            //         'conditions' => [
            //             ['is_refundable', '=', true],
            //         ],
            //         'url' => [
            //             'payable.refund',
            //             [
            //                 'payment' => ':id',
            //             ],
            //         ],
            //         'hasDialog' => true,
            //         'dialogQuestion' => __('Are you sure you want to refund this payment?'),
            //         'allowedRoles' => ['superadmin', 'admin', 'manager'],
            //     ],
            // ],
            // 'filters' => [
            //     'columns' => [
            //         [
            //             'type' => 'select',
            //             'slug' => 'status',
            //             'componentOptions' => [
            //                 'multiple' => true,
            //                 'clearable' => true,
            //                 'variant' => 'outlined',
            //                 'label' => 'Status',
            //                 'itemTitle' => 'name',
            //                 'itemValue' => 'value',
            //                 'items' => array_map(function ($status) {
            //                     return [
            //                         'name' => $status->label(),
            //                         'value' => $status->value,
            //                     ];
            //                 }, PaymentStatus::cases()),
            //             ],
            //         ],
            //     ],
            //     'relations' => [
            //         [
            //             'type' => 'select',
            //             'slug' => 'paymentService',
            //             'componentOptions' => [
            //                 'multiple' => true,
            //                 'clearable' => true,
            //                 'variant' => 'outlined',
            //                 'label' => 'Payment Service',
            //             ],
            //             'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
            //         ],
            //         [
            //             'type' => 'select',
            //             'slug' => 'currency',
            //             'componentOptions' => [
            //                 'multiple' => true,
            //                 'clearable' => true,
            //                 'variant' => 'outlined',
            //                 'label' => 'Currency',
            //                 'itemTitle' => 'iso_4217',
            //                 'itemValue' => 'id',
            //                 'connector' => 'SystemPricing|Currency^repository->list?column=iso_4217&scopes=[enabled]',
            //             ],
            //         ],
            //     ],
            // ],
            // 'form_with' => [
            //     'price',
            //     'paymentable',
            // ],
            // 'form_appends' => [
            //     'amount_formatted',
            //     'paymentable',
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Owner Id',
            //         'key' => 'price.priceable',
            //         'itemTitle' => 'id',
            //         'allowedRoles' => ['superadmin'],
            //         'formatter' => [
            //             'edit',
            //         ],
            // 'itemTitle' => 'content->headline',
            //     ],
            //     [
            //         'title' => 'Owner Type',
            //         'key' => 'price',
            //         'itemTitle' => 'priceable_type',
            //         'allowedRoles' => ['superadmin'],
            //         'groupable' => true,
            //         'groupOrder' => 'asc',
            // 'itemTitle' => 'content->headline',
            //     ],
            //     [
            //         'title' => 'Related',
            //         'key' => 'paymentable',
            //         'itemTitle' => 'id',
            //         'allowedRoles' => ['superadmin'],
            //     ],
            //     [
            //         'title' => 'Company',
            //         'key' => 'creator.company',
            //         'itemTitle' => 'name',
            //         'minWidth' => 150,
            //         'searchable' => true,
            //         'searchKey' => 'creator.company.name',

            //         'groupable' => true,
            //         'groupOrder' => 'asc',
            //     ],
            //     [
            //         'title' => 'Service',
            //         'key' => 'paymentService',
            // 'itemTitle' => 'title',
            //         'formatter' => [
            //             'chip',
            //             [
            //                 'variant' => 'outlined',
            //                 'color' => 'primary',
            //             ],
            //         ],
            //         'groupable' => true,
            //     ],
            //     [
            //         'title' => 'Total Price',
            //         'key' => 'amount_formatted',
            //     ],
            //     [
            //         'title' => 'Status',
            //         'key' => 'status_vuetify_chip',
            //         'formatter' => [
            //             'dynamic',
            //         ],
            //         'groupable' => true,
            //         'groupOrder' => 'asc',
            //     ],
            //     [
            //         'title' => 'User Email',
            //         'key' => 'creator',
            //         'itemTitle' => 'email',
            //         'searchable' => true,
            //         'searchKey' => 'creator.email',
            //         'groupable' => true,
            //         'groupOrder' => 'asc',
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'visible' => false,
            //         'formatter' => [
            //             'date',
            //             'medium',
            //         ],
            //         'searchable' => false,
            //         'sortable' => true,
            //     ],
            //     [
            //         'title' => 'Last Update',
            //         'key' => 'updated_at',
            //         'formatter' => [
            //             'date',
            //             'long',
            //         ],
            //         'searchable' => false,
            //         'sortable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'type' => 'number-input',
            //         'name' => 'amount',
            //         'label' => __('Payment Amount'),
            //         'controlVariant' => 'stacked',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'allowedRoles' => ['superadmin'],
            //     ],
            //     [
            //         'type' => 'combobox',
            //         'name' => 'currency_id',
            //         'label' => __('Currency'),
            //         'itemTitle' => 'name',
            //         'itemValue' => 'id',
            //         'connector' => 'SystemPricing:Currency|repository:list:column=name',
            //         'default' => 1,
            //         'rules' => 'required',
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'allowedRoles' => ['superadmin'],
            //     ],
            //     [
            //         'type' => 'text',
            //         'name' => 'email',
            //         'label' => __('Payer Email'),
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'allowedRoles' => ['superadmin'],
            //     ],
            //     [
            //         'type' => 'preview',
            //         'name' => 'description',
            //         'label' => __('Description'),
            //         'col' => ['cols' => 12, 'lg' => 12],
            //         'configuration' => [
            //             'elements' => [
            //                 [
            //                     'tag' => 'ue-title',
            //                     'attributes' => [
            //                         'classes' => 'mb-2',
            //                         'padding' => 'a-0',
            //                         'type' => 'body-2',
            //                     ],
            //                     'elements' => 'Description',
            //                 ],
            //                 [
            //                     'tag' => 'p',
            //                     'elements' => '${description??N/A}$',
            //                 ],
            //             ],
            //         ],
            //         'conditions' => [
            //             ['description', '!=', ''],
            //             ['description', '!=', null],
            //         ],
            //     ],
            //     [
            //         'type' => 'preview',
            //         'name' => 'bank_receipts',
            //         'noSubmit' => true,
            //         'default' => null,
            //         'col' => ['cols' => 12, 'class' => 'mb-4'],
            //         'configuration' => [
            //             'elements' => [
            //                 [
            //                     'tag' => 'ue-title',
            //                     'attributes' => [
            //                         'classes' => 'mb-2',
            //                         'padding' => 'a-0',
            //                         'type' => 'body-2',
            //                     ],
            //                     'elements' => 'Bank Receipts',
            //                 ],
            //                 [
            //                     'tag' => 'ue-filepond-preview',
            //                     'attributes' => [
            //                         'source' => '${bank_receipts??N/A}$',
            //                         'show-inline-file-name' => true,
            //                         'max-file-name-length' => 30,
            //                         'image-size' => 24,
            //                     ],
            //                 ],
            //             ],
            //         ],
            //         'conditions' => [
            //             ['bank_receipts', '>', 0],
            //         ],
            //         'creatable' => 'hidden',
            //     ],
            //     [
            //         'type' => 'select',
            //         'name' => 'payment_service_id',
            //         'label' => __('Payment Service'),
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
            //         'rules' => 'sometimes|required',
            //         'editable' => false,
            //     ],
            //     [
            //         'type' => 'select',
            //         'name' => 'status',
            //         'label' => __('Status'),
            //         'col' => ['cols' => 12, 'lg' => 6],
            //         'itemTitle' => 'name',
            //         'itemValue' => 'value',
            //         'items' => PaymentStatus::cases(),
            //         'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
            //         'rules' => 'required',
            //     ],
            //     [
            //         'type' => 'filepond',
            //         'name' => 'invoice',
            //         'label' => 'Invoice',
            //         'max' => 3,
            //         'conditions' => [
            //             ['status', '=', PaymentStatus::COMPLETED, PaymentStatus::REFUNDED, PaymentStatus::CANCELLED],
            //         ],
            //         'allowedRoles' => ['superadmin', 'admin', 'manager', 'account-executive'],
            //         'acceptedExtensions' => ['pdf'],
            //     ],

            // ],
        ],
        'payment_currency' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\PaymentCurrency\Index\PaymentCurrencyIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\PaymentCurrency\Index\PaymentCurrencyIndexOptions::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\PaymentCurrency\Form\PaymentCurrencyFormInputs::class,
            ],
            'name' => 'PaymentCurrency',
            'headline' => 'Payment Currencies',
            'url' => 'payment-currencies',
            'route_name' => 'payment_currency',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'createOnModal' => false,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // 'noForm' => true,
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Name',
            //         'key' => 'name',
            //         'formatter' => [
            //             'edit',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Services',
            //         'key' => 'paymentServices',
            //         'itemTitle' => 'name',
            //     ],
            //     [
            //         'title' => 'Credit Card Service',
            //         'key' => 'paymentService',
            //         'itemTitle' => 'name',
            //     ],
            //     [
            //         'title' => __('VAT Rate') . ' (' . __('for personals') . ')',
            //         'key' => 'personal_vat_rate_name_with_rate',
            //     ],
            //     [
            //         'title' => __('VAT Rate') . ' (' . __('for corporates') . ')',
            //         'key' => 'corporate_vat_rate_name_with_rate',
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'formatter' => [
            //             'date',
            //             'medium',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'name' => 'name',
            //         'label' => __('Name'),
            //         'type' => 'text',
            //         'readonly' => true,
            //         'clearable' => false,
            //         'noSubmit' => true,
            //     ],
            //     [
            //         'name' => 'payment_service_id',
            //         'label' => __('Internal Payment Service (for credit card payment)'),
            //         'type' => 'select',
            //         'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
            //         'multiple' => false,
            //         'itemTitle' => 'name',
            //     ],
            //     [
            //         'type' => 'json-repeater',
            //         'name' => 'default_vat_rates',
            //         'label' => __('Default VAT Rates'),
            //         'default' => [],
            //         'formRowAttribute' => [
            //             'noGutters' => true,
            //         ],
            //         'col' => [
            //             'cols' => 12,
            //         ],
            //         'isUnique' => true,
            //         'uniqueField' => 'company_type',
            //         'uniqueValue' => 'id',
            //         'autoIdGenerator' => false,
            //         'asObject' => true,
            //         'schema' => [
            //             [
            //                 'type' => 'select',
            //                 'name' => 'company_type',
            //                 'label' => __('Company Type'),
            //                 'hideDetails' => 'auto',
            //                 'itemValue' => 'id',
            //                 'itemTitle' => 'name',
            //                 'itemValueType' => 'string',
            //                 'col' => [
            //                     'cols' => 6,
            //                     'class' => 'pr-4',
            //                 ],
            //                 'items' => [
            //                     [
            //                         'id' => 'corporate',
            //                         'name' => __('Corporate Company'),
            //                     ],
            //                     [
            //                         'id' => 'personal',
            //                         'name' => __('Personal Company'),
            //                     ],
            //                 ],
            //                 'rules' => 'required',
            //             ],
            //             [
            //                 'name' => 'vat_rate_id',
            //                 'label' => __('VAT Rate'),
            //                 'type' => 'select',
            //                 'connector' => 'SystemPricing:VatRate|repository:list:column=name,rate:appends=name_with_rate',
            //                 'itemTitle' => 'name_with_rate',
            //                 'hideDetails' => 'auto',
            //                 'col' => [
            //                     'cols' => 6,
            //                 ],
            //                 'rules' => 'required',
            //             ],
            //         ],
            //     ],
            // ],
        ],
        'card_type' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\CardType\Index\CardTypeIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\CardType\Index\CardTypeIndexOptions::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\CardType\Form\CardTypeFormInputs::class,
            ],
            'name' => 'CardType',
            'headline' => 'Card Types',
            'url' => 'card-types',
            'route_name' => 'card_type',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'createOnModal' => true,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Name',
            //         'key' => 'name',
            //         'formatter' => [
            //             'edit',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Slug',
            //         'key' => 'card_type',
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'formatter' => [
            //             'date',
            //             'medium',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'name' => 'name',
            //         'label' => 'Name',
            //         'type' => 'text',
            //     ],
            //     [
            //         'type' => 'text',
            //         'name' => 'card_type',
            //         'label' => __('Card Type'),
            //         'rules' => 'sometimes|required',
            //     ],
            //     [
            //         'name' => 'paymentServices',
            //         'label' => __('Payment Services'),
            //         'type' => 'select',
            //         'multiple',
            //         'repository' => 'Modules\\SystemPayment\\Repositories\\PaymentServiceRepository',
            //     ],
            //     [
            //         'label' => __('Logo'),
            //         'type' => 'image',
            //         'name' => 'logo',
            //         'rules' => 'sometimes|required:array',
            //         'isIcon' => true,
            //     ],
            // ],
        ],
        'my_payment' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\MyPayment\Index\MyPaymentIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\MyPayment\Index\MyPaymentIndexOptions::class,
                'row_actions' => \Modules\SystemPayment\Blueprint\MyPayment\Index\MyPaymentIndexRowActions::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\MyPayment\Form\MyPaymentFormInputs::class,
            ],
            'name' => 'MyPayment',
            'headline' => 'My Payments',
            'url' => 'my-payments',
            'route_name' => 'my_payment',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'scopes' => [
                'isMyCreation' => true,
            ],
            // 'table_options' => [
            //     'subtitle' => __('You can check all the payments that you receive and the invoices related to the payments here according to company list.'),
            //     'formEditTitleTranslationKey' => 'messages.my_payment.edit',

            //     'createOnModal' => true,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'table_row_actions' => [
            //     'show' => [
            //         'name' => 'edit',
            //         'merge' => true,
            //         'icon' => 'mdi-eye',
            //         'label' => __('Show'),
            //     ],
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Amount',
            //         'key' => 'amount_formatted',
            //     ],
            //     [
            //         'title' => 'Status',
            //         'key' => 'status',
            //         'formatter' => [
            //             'chip',
            //             [
            //                 'size' => 'small',
            //             ],
            //         ],
            //     ],
            //     [
            //         'title' => 'User Email',
            //         'key' => 'creator',
            //         'itemTitle' => 'email',
            //     ],
            //     [
            //         'title' => 'Created Time',
            //         'key' => 'created_at',
            //         'visible' => false,
            //         'formatter' => [
            //             'date',
            //             'medium',
            //         ],
            //         'searchable' => false,
            //         'sortable' => true,
            //     ],
            //     [
            //         'title' => 'Last Update',
            //         'key' => 'updated_at',
            //         'formatter' => [
            //             'date',
            //             'numeric',
            //         ],
            //         'searchable' => false,
            //         'sortable' => true,
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'type' => 'preview',
            //         'name' => 'files',
            //         'noSubmit' => true,
            //         'default' => null,
            //         'col' => ['cols' => 12, 'class' => 'mb-4'],
            //         'configuration' => [
            //             'tag' => 'v-card',
            //             'attributes' => [
            //                 'link' => true,
            //                 'class' => 'mx-auto py-4 mb-4 h-100',
            //                 'variant' => 'elevated',
            //                 'title' => 'Invoices',
            //             ],
            //             'elements' => [
            //                 'tag' => 'v-card-text',
            //                 'elements' => [
            //                     'tag' => 'ue-filepond-preview',
            //                     'attributes' => [
            //                         'source' => '$invoices',
            //                         'show-inline-file-name' => true,
            //                         'max-file-name-length' => 20,
            //                         'image-size' => 24,
            //                     ],
            //                 ],
            //             ],
            //         ],
            // 'conditions' => [
            //     ['invoice', '>', 0],
            // ],
            //         'creatable' => 'hidden',
            //     ],
            // ],
        ],
        'payment_country' => [
            'index' => [
                'columns' => \Modules\SystemPayment\Blueprint\PaymentCountry\Index\PaymentCountryIndexColumns::class,
                'options' => \Modules\SystemPayment\Blueprint\PaymentCountry\Index\PaymentCountryIndexOptions::class,
            ],
            'form' => [
                'inputs' => \Modules\SystemPayment\Blueprint\PaymentCountry\Form\PaymentCountryFormInputs::class,
            ],
            'name' => 'PaymentCountry',
            'headline' => 'Payment Countries',
            'url' => 'payment-countries',
            'route_name' => 'payment_country',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            // 'table_options' => [
            //     'createOnModal' => true,
            //     'editOnModal' => true,
            //     'isRowEditing' => false,
            //     'rowActionsType' => 'inline',
            // ],
            // 'headers' => [
            //     [
            //         'title' => 'Name',
            //         'key' => 'name',
            //         'formatter' => [
            //             'edit',
            //         ],
            //         'searchable' => true,
            //     ],
            //     [
            //         'title' => 'EUR VAT Rate',
            //         'key' => 'EURVatRate',
            //     ],
            //     [
            //         'title' => 'USD VAT Rate',
            //         'key' => 'USDVatRate',
            //     ],
            //     [
            //         'title' => 'TRY VAT Rate',
            //         'key' => 'TRYVatRate',
            //     ],
            //     [
            //         'title' => 'Actions',
            //         'key' => 'actions',
            //         'sortable' => false,
            //     ],
            // ],
            // 'inputs' => [
            //     [
            //         'name' => 'name',
            //         'label' => 'Name',
            //         'type' => 'text',
            //         'readonly' => true,
            //         'clearable' => false,
            //         'noSubmit' => true,
            //     ],
            //     [
            //         'type' => 'json-repeater',
            //         'name' => 'currency_vat_rate',
            //         'default' => [],
            //         'label' => __('Supported Languages'),
            //         'formRowAttribute' => [
            //             'noGutters' => true,
            //         ],
            //         'col' => [
            //             'cols' => 12,
            //         ],
            //         'autoIdGenerator' => false,
            //         'isUnique' => true,
            //         'uniqueField' => 'currency_id',
            //         'uniqueValue' => 'iso_4217',
            //         'asObject' => true,
            //         'schema' => [
            //             [
            //                 'type' => 'select',
            //                 'name' => 'currency_id',
            //                 'label' => __('Currency'),
            //                 'connector' => 'SystemPayment:PaymentCurrency|repository:list:column=name,iso_4217:scopes=hasAnyPaymentService',
            //                 'itemValue' => 'iso_4217',
            //                 'itemTitle' => 'name',
            //                 'hideDetails' => 'auto',
            //                 'col' => [
            //                     'cols' => 6,
            //                     'class' => 'pr-4',
            //                 ],
            //                 'rules' => 'required',
            //             ],
            //             [
            //                 'type' => 'select',
            //                 'name' => 'vat_rate_id',
            //                 'label' => __('VAT Rate'),
            //                 'connector' => 'SystemPricing:VatRate|repository:list:column=name,rate:appends=name_with_rate',
            //                 'itemTitle' => 'name_with_rate',
            //                 'hideDetails' => 'auto',
            //                 'col' => [
            //                     'cols' => 6,
            //                 ],
            //                 'rules' => 'required',
            //             ],
            //         ],
            //     ],
            // ],
        ],
    ],
];

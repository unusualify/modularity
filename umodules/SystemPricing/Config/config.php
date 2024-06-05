<?php

return [
    'name' => 'SystemPricing',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'Price Management',
    'routes' => [
        'vat_rate' => [
            'name' => 'VatRate',
            'headline' => 'Vat Rates',
            'url' => 'vat-rates',
            'route_name' => 'vat_rate',
            'icon' => '',
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
                        0 => 'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Rate',
                    'key' => 'rate',
                    'formatter' => [
                        0 => 'chip',
                        1 => 'primary',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        0 => 'date',
                        1 => 'long',
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
                    'name' => 'rate',
                    'label' => 'Rate',
                    'type' => 'text',
                    'ext' => 'number',
                ],
            ],
        ],
        'currency' => [
            'name' => 'Currency',
            'headline' => 'Currencies',
            'url' => 'currencies',
            'route_name' => 'currency',
            'icon' => '',
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
                        0 => 'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Symbol',
                    'key' => 'symbol',
                    'searchable' => true,
                    'formatter' => [
                        0 => 'chip',
                        1 => 'success',
                    ],
                ],
                [
                    'title' => 'ISO 4217',
                    'key' => 'iso_4217',
                    'searchable' => true,
                    'formatter' => [
                        0 => 'chip',
                        1 => 'primary',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        0 => 'date',
                        1 => 'long',
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
                    'name' => 'symbol',
                    'label' => 'Symbol',
                    'type' => 'text',
                ],
                [
                    'name' => 'iso_4217',
                    'label' => 'ISO 4217',
                    'type' => 'text',
                ],
            ],
        ],
        'price_type' => [
            'name' => 'PriceType',
            'headline' => 'Price Types',
            'url' => 'price-types',
            'route_name' => 'price_type',
            'icon' => '',
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
                        0 => 'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        0 => 'date',
                        1 => 'long',
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
            ],
        ],
    ],
];


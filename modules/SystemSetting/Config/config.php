<?php

return [
    'name' => 'SystemSetting',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Settings',
    'routes' => [
        'general' => [
            'name' => 'General',
            'headline' => 'Generals',
            'url' => 'generals',
            'route_name' => 'general',
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
                // [
                //     'name' => 'balance',
                //     'label' => 'Balance',
                //     'type' => 'text',
                //     'spreadable' => true,
                // ],
                [
                    'name' => 'cont',
                    'label' => 'Spread',
                    'type' => 'spread',
                    'connector' => 'SystemSetting:General',
                    'reservedKeys' => ['name'],
                    'height' => '250px',
                    // 'scrollable' => true,
                ],
                [
                    'name' => 'logo',
                    'label' => 'Logo',
                    'type' => 'image',
                    'translated' => true,
                ],
            ],
        ],
    ],
];

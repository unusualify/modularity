<?php

return [
    'name' => 'SystemNotification',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Notifications',
    'base_prefix' => false,
    'routes' => [
        'notification' => [
            'name' => 'Notification',
            'headline' => 'Notifications',
            'url' => 'notifications',
            'route_name' => 'notification',
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
            ],
        ],
    ],
];

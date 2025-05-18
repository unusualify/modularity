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
            'title_column_key' => 'id',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Message',
                    'key' => 'data.message',
                    'searchable' => true,
                    'formatter' => [
                        'shorten',
                        10
                    ],
                ],
                [
                    'title' => 'Read',
                    'key' => 'is_read',
                    'formatter' => [
                        'status'
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
                    'name' => 'data->message',
                    'label' => 'Message',
                    'type' => 'text',
                ],
            ],
        ],
    ],
];

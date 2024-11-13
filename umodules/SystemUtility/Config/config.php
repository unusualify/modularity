<?php

return [
    'name' => 'SystemUtility',
    'system_prefix' => true,
    'headline' => 'System Utilities',
    'group' => 'system',
    'routes' => [
        'state' => [
            'name' => 'State',
            'headline' => 'States',
            'url' => 'states',
            'route_name' => 'state',
            'icon' => '',
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
                    'title' => 'Code',
                    'key' => 'code',
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
                    'rules' => 'sometimes|required',

                ],
                [
                    'type' => 'text',
                    'name' => 'code',
                    'label' => 'Code',
                    'rules' => 'sometimes|required',
                ],
                [
                    'type' => 'text',
                    'name' => 'color',
                    'label' => 'Color',
                    'rules' => 'sometimes|required',
                ],
                [
                    'type' => 'text',
                    'name' => 'icon',
                    'label' => 'Icon',
                    'rules' => 'sometimes|required',
                ],
            ],
        ],
    ],
];

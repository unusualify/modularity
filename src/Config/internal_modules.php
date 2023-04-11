<?php

return [
    'user' => [
        'name' => 'User',
        'base_prefix' => false,
        'parent_route' => [
            'name' => 'User',
            'url' => 'user',
            'route_name' => 'user',
            'icon' => '$users',
            'table_options' => [
                'createOnModal' => false,
                'editOnModal' => false,
                'isRowEditing' => true,
                'actionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'align' => 'start',
                    'sortable' => false,
                    'searchable' => true,
                ],
                [
                    'title' => 'Type',
                    'key' => 'type',
                    'align' => 'start',
                    'sortable' => false,
                    'searchable' => true,
                ],
                [
                    'itlet' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'title' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'placeholder' => '',
                    'cols' => 12,
                    'sm' => 12,
                    'md' => 8,
                ],
                [
                    'title' => 'Type',
                    'name' => 'type',
                    'type' => 'text',
                    'placeholder' => '',
                    'cols' => 12,
                    'sm' => 12,
                    'md' => 8,
                ],
            ]
        ],
        'sub_routes' => [
            'role' => [
                'name' => 'Role',
                'url' => 'role',
                'route_name' => 'role',
                'icon' => '$role',
                'table_options' => [
                    'createOnModal' => false,
                    'editOnModal' => false,
                    'isRowEditing' => true,
                    'actionsType' => 'inline',
                ],
                'headers' => [
                    [
                        'title' => 'Name',
                        'key' => 'name',
                        'align' => 'start',
                        'sortable' => true,
                        'filterable' => false,
                        'groupable' => false,
                        'divider' => false,
                        'class' => '', // || []
                        'cellClass' => '', // || []
                        'width' => '', // || int
                        // vuetify datatable header fields end

                        // custom fields for ue-datatable start
                        'searchable' => true,
                        'isRowEditable' => true,
                        'isColumnEditable' => true,
                        'formatter' => '',
                        // custom fields for ue-datatable end
                    ],
                    [
                        // vuetify datatable header fields start
                        'title' => 'Guard Name',
                        'key' => 'guard_name',
                        'align' => 'start',
                        'sortable' => false,
                        'filterable' => false,
                        'groupable' => false,
                        'divider' => false,
                        'class' => '', // || []
                        'cellClass' => '', // || []
                        'width' => '', // || int
                        // vuetify datatable header fields end

                        // custom fields for ue-datatable start
                        'searchable' => true,
                        'isRowEditable' => false,
                        'isColumnEditable' => false,
                        'formatter' => '',
                        // custom fields for ue-datatable end
                    ],
                    [
                        'title' => 'Created Time',
                        'key' => 'created_at',
                        'sortable' => true,
                        'filterable' => true,
                        'groupable' => false,
                        'divider' => false,
                        'class' => '', // || []
                        'cellClass' => '', // || []
                        'width' => '', // || int
                        // vuetify datatable header fields end

                        // custom fields for ue-datatable start
                        'searchable' => true,
                        'isRowEditable' => false,
                        'isColumnEditable' => false,
                        'formatter' => 'formatDate',
                        // custom fields for ue-datatable end
                    ],
                    [
                        'title' => 'Actions',
                        'key' => 'actions',
                        'sortable' => false
                    ],
                ],
                'inputs_old' => [
                    [
                        'title' => 'Name',
                        'name' => 'name',
                        'type' => 'text',
                        'default' => '',
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'placeholder' => '',
                            'outlined',
                            'prepend-icon' => 'mdi-card-text-outline',
                            // 'dense' => true
                        ]
                    ],
                    [
                        'title' => 'Guard Name',
                        'name' => 'guard_name',
                        'type' => 'text',
                        'default' => 'web',
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'placeholder' => 'web',
                            'prepend-icon' => 'mdi-account-child',
                            'readonly',
                            'dense',
                            'disabled',
                            'error',
                            'flat',
                            // 'full-width',
                            'hide-spin-buttons',
                            'outlined'
                        ]
                    ],
                    [
                        'title' => 'Permission',
                        'name' => 'permissions',
                        // 'type' => 'radio',
                        'type' => 'select',
                        'default' => 0,
                        'items' => [
                            [
                                'text' => 'Edit Role',
                                'value' => 1,
                                // 'disabled' => false,
                            ],
                            [
                                'text' => 'Create Role',
                                'value' => 2
                            ],
                            [
                                'text' => 'Delete Role',
                                'value' => 3
                            ],
                        ],
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'prepend-icon' => 'mdi-account-arrow-right',
                            'color' => 'success',
                            'mandatory',
                            'row',
                            'outlined',
                            'dense',
                            'menu-props' => [
                                'closeOnClick' => true,
                                'closeOnContentClick' => false,
                                'disableKeys' => true,
                                'openOnClick' => false,
                                'maxHeight' => 304
                            ]
                        ],
                    ],
                    [
                        'title' => 'Activity of Permission',
                        'name' => 'is_active',
                        'type' => 'checkbox',
                        // 'type' => 'switch',
                        'default' => true,
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'color' => 'success',
                            // 'readonly',
                            'dense',
                            // 'disabled',
                            // 'error',
                            'flat',
                            'full-width',
                            'hide-spin-buttons',
                            'prepend-icon' => 'mdi-glass-stange',

                            // 'false-value' => false,

                            // 'false-value' => true,
                            // 'true-value' => false,
                            // 'appendIcon' => 'mdi-dropbox',
                            // 'prependIcon' => 'mdi-radioactive',
                            // 'offIcon' => 'mdi-inactive',
                            // 'onIcon' => 'mdi-radioactive',
                        ],
                    ],
                    [
                        'title' => 'Status',
                        'name' => 'status',
                        // 'type' => 'radio',
                        'type' => 'radio',
                        'options' => [
                            [
                                'label' => 'WAITING',
                                'value' => 1
                            ],
                            [
                                'label' => 'FAILURE',
                                'value' => 2
                            ],
                            [
                                'label' => 'COMPLETED',
                                'value' => 3
                            ],
                        ],
                        'default' => 1,
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'prepend-icon' => 'mdi-chart-arc',
                            'activeClass' => '',
                            'color' => 'success',

                            'mandatory',
                            'row',

                            'props' => [
                                'color' => 'error',
                                'on-icon' => '$radioOn',
                                'off-icon' => '$radioOff'
                            ]

                            // 'appendIcon' => 'mdi-dropbox',
                            // 'prependIcon' => 'mdi-radioactive',
                            // 'offIcon' => 'mdi-inactive',
                            // 'onIcon' => 'mdi-radioactive',
                        ],
                    ],
                    [
                        'title' => 'Report',
                        'name' => 'reports',
                        'type' => 'file',
                        'default' => [],
                        // 'accept' => "image/*,.doc,.docx,.pdf",
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'small-chips',
                            'prependIcon' => 'mdi-camera',
                            'prependInnerIcon' => '',

                        ]
                    ],
                    [
                        'title' => 'Day Interval',
                        'name' => 'day_interval',
                        'type' => 'range',
                        'default' => [0,100],
                        // 'default' => [],
                        'cols' => 10,
                        'sm' => 10,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'max' => 100,
                            'min' => 0,
                            'tick-size' => 1,
                            // 'background-color' => 'success',
                            'hint' => '',
                            // 'vertical',
                        ]
                    ],
                    [
                        'title' => 'Color',
                        'name' => 'color',
                        'type' => 'color',
                        'default' => '#32010121',
                        'cols' => 12,
                        'sm' => 12,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,

                        'props' => [
                            'placeholder' => '#FFDD11FF',
                            // 'dotSize' => 'rgba',
                            'prepend-icon' => 'mdi-palette',
                            'props' => [
                                'dotSize' => 25,
                                'maxHeight' => 200,
                            ]


                        ]
                    ],
                    [
                        'title' => 'Start Date',
                        'name' => 'start_date',
                        'type' => 'date',
                        'default' => '',
                        'cols' => 12,
                        'sm' => 12,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'color' => "red lighten-1",
                            'prepend-icon' => 'mdi-calendar',
                            // 'prepend-inner-icon' => 'mdi-calendar',
                            'dense',
                            'outlined'
                        ],
                        'picker_props' => [
                            'color' => 'success',
                            'header-color' => 'info',
                            'min' => "2016-06-15",
                            'max' => "2018-03-20",
                            // 'type' => "month",
                            // 'range',

                            // 'show-adjacent-months',
                        ]
                    ],
                    [
                        'title' => 'Start Time',
                        'name' => 'start_time',
                        'type' => 'time',
                        'default' => '',
                        'cols' => 12,
                        'sm' => 12,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'color' => "red lighten-1",
                            'prepend-icon' => 'mdi-timer',
                            // 'prepend-inner-icon' => 'mdi-calendar',
                            'dense',
                            'outlined'
                        ],
                        'picker_props' => [
                            'color' => 'success',
                            'header-color' => 'info',
                            // 'type' => "month",
                            // 'range',
                            // 'show-adjacent-months',
                        ]
                    ],
                    [
                        'title' => 'Icon',
                        'name' => 'icon',
                        'type' => 'text',
                        'default' => '',
                        'cols' => 12,
                        'sm' => 12,
                        'md' => 6,
                        'lg' => 6,
                        'xl' => 4,
                        'props' => [
                            'placeholder' => '',
                            'outlined',
                            'prepend-icon' => 'mdi-card-text-outline',
                            // 'dense' => true
                        ]
                    ],
                ],

                'inputs' => [
                    'name' => [
                        'type' => 'text',
                        'name' => 'name',
                        'label' => 'Name',
                        'hint' => '',
                        'placeholder' => '',
                        'default' => '',
                        // 'tooltip' => 'Enter a usual name',

                        'col' => [
                            'cols' => 10,
                            'sm' => 10,
                            'md' => 6,
                            'lg' => 6,
                            'xl' => 4
                        ],
                        'offset' => [
                            'offset' => 0,
                            'offset-sm' => 2,
                            'offset-md' => 0,
                            'offset-lg' => 0,
                            'offset-xl' => 0,
                        ],
                        'order' => [
                            'order' => 0,
                            'order-sm' => 1,
                            'order-md' => 1,
                            'order-lg' => 1,
                            'order-xl' => 1,
                        ],

                        'outlined',
                        'prepend-icon' => 'mdi-card-text-outline',
                        'dense',

                    ],
                    'guard_name' => [
                        'type' => 'text',
                        'name' => 'guard_name',
                        'label' => 'Guard Name',
                        'placeholder' => 'web',
                        // 'tooltip' => 'Enter the guard name',
                        'default' => 'web',

                        'col' => [
                            'cols' => 10,
                            'sm' => 10,
                            'md' => 6,
                            'lg' => 6,
                            'xl' => 4
                        ],
                        'offset' => [
                            'offset' => 0,
                            'offset-sm' => 2,
                            'offset-md' => 0,
                            'offset-lg' => 0
                        ],
                        'order' => [
                            'order' => 1,
                            'order-sm' => 0,
                            'order-md' => 0,
                            'order-lg' => 0,
                            'order-xl' => 0,
                        ],
                        'prepend-icon' => 'mdi-account-child',
                        'readonly',
                        'dense',
                        'disabled',
                        'error',
                        'flat',
                        // 'full-width',
                        'hide-spin-buttons',
                        'outlined',
                    ],
                    // 'permissions' => [
                    //     'type' => 'treeview',
                    //     // 'type' => 'custom-input-treeview',
                    //     'name' => 'permissions',
                    //     'label' => 'Permissions of the role',
                    //     'col' => [
                    //         'cols' => 12,
                    //         'sm' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //         'xl' => 12
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0,
                    //         'offset-xl' => 0,
                    //     ],
                    //     'order' => [
                    //         'order' => 0,
                    //         'order-sm' => 1,
                    //         'order-md' => 1,
                    //         'order-lg' => 1,
                    //         'order-xl' => 1,
                    //     ],

                    //     "activatable" => true,
                    //     "selectable" => true,
                    //     "multipleActive" => false,
                    //     "slot" => [
                    //         "prepend",
                    //         "label"
                    //     ],

                    //     'route' => 'permission',
                    //     'model' => Spatie\Permission\Models\Permission::class,
                    // ],
                    // 'permissions_' => [
                    //     'type' => 'autocomplete',
                    //     'name' => 'permissions_',
                    //     'label' => 'Permission',
                    //     'default' => 0,
                    //     'items' => [
                    //         [
                    //             'text' => 'Edit Role',
                    //             'value' => 1,
                    //             // 'disabled' => false,
                    //         ],
                    //         [
                    //             'text' => 'Create Role',
                    //             'value' => 2
                    //         ],
                    //         [
                    //             'text' => 'Delete Role',
                    //             'value' => 3
                    //         ],
                    //     ],
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0
                    //     ],
                    //     'order' => [
                    //         'order' => 2,
                    //         'order-sm' => 2,
                    //         'order-md' => 2,
                    //         'order-lg' => 2,
                    //         'order-xl' => 2,
                    //     ],
                    //     'prepend-icon' => 'mdi-account-arrow-right',
                    //     'color' => 'success',
                    //     'mandatory',
                    //     'row',
                    //     'outlined',
                    //     'dense',
                    //     'menu-props' => [
                    //         'closeOnClick' => true,
                    //         'closeOnContentClick' => false,
                    //         'disableKeys' => true,
                    //         'openOnClick' => false,
                    //         'maxHeight' => 304
                    //     ],

                    // ],
                    // 'is_active' => [
                    //     'type' => 'checkbox',
                    //     'name' => 'is_active',
                    //     'label' => 'Activity of Permission',
                    //     'default' => true,
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0
                    //     ],
                    //     'order' => [
                    //         'order' => 3,
                    //         'order-sm' => 3,
                    //         'order-md' => 3,
                    //         'order-lg' => 3,
                    //         'order-xl' => 3,
                    //     ],

                    //     'prepend-icon' => 'mdi-glass-stange',
                    //     'color' => 'success',
                    //     // 'readonly',
                    //     'dense',
                    //     // 'disabled',
                    //     // 'error',
                    //     'flat',
                    //     // 'full-width',
                    //     'hide-spin-buttons',
                    //     // 'false-value' => false,
                    //     // 'false-value' => true,
                    //     // 'true-value' => false,
                    //     // 'appendIcon' => 'mdi-dropbox',
                    //     // 'prependIcon' => 'mdi-radioactive',
                    //     // 'offIcon' => 'mdi-inactive',
                    //     // 'onIcon' => 'mdi-radioactive',

                    // ],
                    // 'status' => [
                    //     // 'label' => 'Status',
                    //     'name' => 'status',
                    //     'type' => 'radio',
                    //     'default' => 1,
                    //     'options' => [
                    //         [
                    //             'label' => 'WAITING',
                    //             'value' => 1
                    //         ],
                    //         [
                    //             'label' => 'FAILURE',
                    //             'value' => 2
                    //         ],
                    //         [
                    //             'label' => 'COMPLETED',
                    //             'value' => 3
                    //         ],
                    //     ],
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0
                    //     ],
                    //     'order' => [
                    //         'order' => 4,
                    //         'order-sm' => 4,
                    //         'order-md' => 4,
                    //         'order-lg' => 4,
                    //         'order-xl' => 4,
                    //     ],
                    //     'prepend-icon' => 'mdi-chart-arc',
                    //     'activeClass' => '',
                    //     'color' => 'success',
                    //     'mandatory',
                    //     'row',
                    //     'color' => 'error',
                    //     'on-icon' => '$radioOn',
                    //     'off-icon' => '$radioOff',

                    // ],
                    // 'reports' => [
                    //     'type' => 'file',
                    //     'name' => 'reports',
                    //     'label' => 'Reports',
                    //     'default' => [],
                    //     // 'accept' => "image/*,.doc,.docx,.pdf",
                    //     'showSize' => true,
                    //     'counter' => true,
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0
                    //     ],
                    //     'order' => [
                    //         'order' => 5,
                    //         'order-sm' => 5,
                    //         'order-md' => 5,
                    //         'order-lg' => 5,
                    //         'order-xl' => 5,
                    //     ],
                    //     'small-chips',
                    //     'prependIcon' => 'mdi-camera',
                    //     'prependInnerIcon' => '',
                    //     'outlined',
                    //     'dense'
                    // ],
                    // 'day_interval' => [
                    //     // 'type' => 'text',
                    //     // 'ext' => 'custom-input-range',
                    //     'type' => 'custom-input-range',
                    //     'name' => 'day_interval',
                    //     'label' => 'Day Interval:0',
                    //     'hint' => 'Day Interval Hint',
                    //     'default' => [0,100],
                    //     // 'default' => 0,
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0
                    //     ],
                    //     'order' => [
                    //         'order' => 6,
                    //         'order-sm' => 6,
                    //         'order-md' => 6,
                    //         'order-lg' => 6,
                    //         'order-xl' => 6,
                    //     ],

                    //     'max' => 100,
                    //     'min' => 0,
                    //     'tick-size' => 1,
                    //     'thumb-label' => 'always'
                    //     // 'background-color' => 'success',
                    // ],
                    // 'color' => [
                    //     // 'type' => 'text',
                    //     // 'ext' => 'color',
                    //     'type' => 'custom-input-color',
                    //     'label' => 'Color',
                    //     'name' => 'color',
                    //     'default' => '#32010121',

                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0,
                    //         'offset-xl' => 0,
                    //     ],
                    //     'order' => [
                    //         'order' => 7,
                    //         'order-sm' => 7,
                    //         'order-md' => 7,
                    //         'order-lg' => 7,
                    //         'order-xl' => 7,
                    //     ],

                    //     'placeholder' => '#FFDD11FF',
                    //     // 'dotSize' => 'rgba',
                    //     'prepend-icon' => 'mdi-palette',
                    //     'props' => [
                    //         'dotSize' => 25,
                    //         'maxHeight' => 200,
                    //     ]
                    // ],
                    // 'start_date' => [
                    //     'type' => 'custom-input-date',
                    //     'label' => 'Start Date',
                    //     'name' => 'start_date',
                    //     'default' => '',
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0,
                    //         'offset-xl' => 0,
                    //     ],
                    //     'order' => [
                    //         'order' => 8,
                    //         'order-sm' => 8,
                    //         'order-md' => 8,
                    //         'order-lg' => 8,
                    //         'order-xl' => 8,
                    //     ],
                    //     'color' => "red lighten-1",
                    //     'prepend-icon' => 'mdi-calendar',
                    //     // 'prepend-inner-icon' => 'mdi-calendar',
                    //     'dense',
                    //     'outlined',

                    //     'picker_props' => [
                    //         // 'type' => 'month',
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         'min' => "2022-01-01",
                    //         'max' => "2025-12-31",
                    //         'first-day-of-week' => '1',
                    //         // 'show-adjacent-months',
                    //         // 'range',
                    //     ]
                    // ],
                    // 'start_time' => [
                    //     'type' => 'custom-input-time',
                    //     'label' => 'Start Time',
                    //     'name' => 'start_time',
                    //     'default' => '',
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0,
                    //         'offset-xl' => 0,
                    //     ],
                    //     'order' => [
                    //         'order' => 8,
                    //         'order-sm' => 8,
                    //         'order-md' => 8,
                    //         'order-lg' => 8,
                    //         'order-xl' => 8,
                    //     ],
                    //     'color' => "red lighten-1",
                    //     'prepend-icon' => 'mdi-timer',
                    //     'prepend-inner-icon' => '',
                    //     'dense',
                    //     'outlined',
                    //     'picker_props' => [
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         'scrollable',

                    //         // 'format' => '24hr',
                    //         // 'min' => "8:00",
                    //         // 'max' => "19:00",
                    //         // 'width' => '500px',

                    //         // 'landscape' => '$vuetify.breakpoint.smAndUp',

                    //         // 'ampm-in-title'
                    //         // 'no-title',
                    //         // 'use-seconds',
                    //         // 'landscape',
                    //     ]
                    // ],
                    // 'icon' => [
                    //     'type' => 'custom-input-icon',
                    //     'name' => 'icon',
                    //     'label' => 'Icon Selector',
                    //     'col' => [
                    //         'cols' => 10,
                    //         'sm' => 10,
                    //         'md' => 6,
                    //         'lg' => 6,
                    //         'xl' => 4
                    //     ],
                    //     'offset' => [
                    //         'offset' => 0,
                    //         'offset-sm' => 2,
                    //         'offset-md' => 0,
                    //         'offset-lg' => 0,
                    //         'offset-xl' => 0,
                    //     ],
                    //     'order' => [
                    //         'order' => 9,
                    //         'order-sm' => 9,
                    //         'order-md' => 9,
                    //         'order-lg' => 9,
                    //         'order-xl' => 9,
                    //     ],
                    //     'dense',
                    //     'outlined',
                    //     'prepend-icon' => 'mdi-card-text-outline',
                    // ]
                ],
                'rules' => [
                    'view' => [],
                    'store' => [],
                    'update' => [],
                    'destroy' => []
                ],


            ],
            'permission' =>  [
                'name' => 'Permission',
                'url' => 'permission',
                'route_name' => 'permission',
                'icon' => '$permission',
                'model' => \Spatie\Permission\Models\Permission::class,
                'table_options' => [
                    'createOnModal' => true,
                    'editOnModal' => true,
                    'isRowEditing' => true,
                    'actionsType' => 'inline',
                ],
                'headers' => [
                    [
                        'title' => 'Name',
                        'key' => 'name',
                        'align' => 'start',
                        'sortable' => false,
                        'searchable' => true,
                    ],
                    [
                        'title' => 'Guard Name',
                        'key' => 'guard_name',
                        'searchable' => true,
                    ],
                    [
                        'title' => 'Actions',
                        'key' => 'actions',
                        'sortable' => false
                    ],
                ],
                'inputs_old' => [
                    [
                        'title' => 'Name',
                        'name' => 'name',
                        'type' => 'text',
                        // 'placeholder' => '',
                        'default' => '',
                        'cols' => 12,
                        'sm' => 6,
                        'md' => 4,
                        'props' => [
                            'outlined',
                            'placeholder' => '',
                            'prepend-icon' => 'mdi-card-text-outline',
                            'dense'
                        ]
                    ],
                    [
                        'title' => 'Guard Name',
                        'name' => 'guard_name',
                        'type' => 'text',
                        'placeholder' => 'web',
                        'default' => 'web',
                        'cols' => 12,
                        'sm' => 6,
                        'md' => 4,
                        'props' => [
                            'prepend-icon' => 'mdi-account-child',
                            'readonly',
                            'dense',
                            'disabled',
                            'error',
                            'flat',
                            // 'full-width',
                            'hide-spin-buttons',
                            'outlined'
                        ]
                    ],

                    [
                        'title' => 'Permission',
                        'name' => 'permissions',
                        // 'type' => 'radio',
                        'type' => 'select',
                        'default' => 0,
                        'items' => [
                            [
                                'text' => 'Edit Role',
                                'value' => 0,
                                'disabled' => false,
                            ],
                            [
                                'text' => 'Create Role',
                                'value' => 1
                            ],
                            [
                                'text' => 'Delete Role',
                                'value' => 2
                            ],
                        ],
                        'cols' => 12,
                        'md' => 12,
                        'sm' => 12,
                        'props' => [
                            'color' => 'success',
                            'mandatory',
                            'row',
                            'outlined',
                            'dense',
                            'menu-props' => [
                                'closeOnClick' => true,
                                'closeOnContentClick' => false,
                                'disableKeys' => true,
                                'openOnClick' => false,
                                'maxHeight' => 304
                            ]
                        ],
                    ],

                    // [
                    //     'title' => 'Activity of Permission',
                    //     'name' => 'is_active',
                    //     'type' => 'checkbox',
                    //     // 'type' => 'switch',
                    //     'default' => true,
                    //     'cols' => 6,
                    //     'md' => 9,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'color' => 'success',
                    //         // 'readonly',
                    //         'dense',
                    //         // 'disabled',
                    //         // 'error',
                    //         'flat',
                    //         'full-width',
                    //         'hide-spin-buttons',
                    //         // 'false-value' => false,

                    //         // 'false-value' => true,
                    //         // 'true-value' => false,
                    //         // 'appendIcon' => 'mdi-dropbox',
                    //         // 'prependIcon' => 'mdi-radioactive',
                    //         // 'offIcon' => 'mdi-inactive',
                    //         // 'onIcon' => 'mdi-radioactive',
                    //     ],
                    // ],
                    // [
                    //     'title' => 'Status',
                    //     'name' => 'status',
                    //     // 'type' => 'radio',
                    //     'type' => 'radio',
                    //     'options' => [
                    //         [
                    //             'label' => 'WAITING',
                    //             'value' => 0
                    //         ],
                    //         [
                    //             'label' => 'FAILURE',
                    //             'value' => 1
                    //         ],
                    //         [
                    //             'label' => 'COMPLETED',
                    //             'value' => 2
                    //         ],
                    //     ],
                    //     'default' => 0,
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'activeClass' => '',
                    //         'color' => 'success',

                    //         'mandatory',
                    //         'row',

                    //         'props' => [
                    //             'color' => 'error',
                    //             'on-icon' => '$radioOn',
                    //             'off-icon' => '$radioOff'
                    //         ]

                    //         // 'appendIcon' => 'mdi-dropbox',
                    //         // 'prependIcon' => 'mdi-radioactive',
                    //         // 'offIcon' => 'mdi-inactive',
                    //         // 'onIcon' => 'mdi-radioactive',
                    //     ],
                    // ],
                    // [
                    //     'title' => 'Report',
                    //     'name' => 'report',
                    //     'type' => 'file',
                    //     // 'accept' => "image/*,.doc,.docx,.pdf",
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'small-chips',
                    //         'prependIcon' => '',
                    //         'prependInnerIcon' => 'mdi-camera'

                    //     ]
                    // ],
                    // [
                    //     'title' => 'Day Interval',
                    //     'name' => 'day_interval',
                    //     'type' => 'range',
                    //     'default' => [0,100],
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'max' => 100,
                    //         'min' => 0,
                    //         'tick-size' => 1,
                    //         // 'background-color' => 'success',
                    //         'hint' => '',

                    //         // 'vertical',
                    //     ]
                    // ],
                    // [
                    //     'title' => 'Color',
                    //     'name' => 'color',
                    //     'type' => 'color',
                    //     'default' => '#32010121',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,

                    //     'props' => [
                    //         'placeholder' => '#FFDD11FF',
                    //         // 'dotSize' => 'rgba',
                    //         'prepend-icon' => 'mdi-palette',
                    //         'props' => [
                    //             'dotSize' => 25,
                    //             'maxHeight' => 200,
                    //         ]


                    //     ]
                    // ],
                    // [
                    //     'title' => 'Start Date',
                    //     'name' => 'start_date',
                    //     'type' => 'date',
                    //     'default' => '',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,
                    //     'props' => [
                    //         'color' => "red lighten-1",
                    //         'prepend-icon' => 'mdi-calendar',
                    //         // 'prepend-inner-icon' => 'mdi-calendar',
                    //         'dense',
                    //         'outlined'
                    //     ],
                    //     'picker_props' => [
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         'min' => "2016-06-15",
                    //         'max' => "2018-03-20",
                    //         // 'type' => "month",
                    //         // 'range',

                    //         // 'show-adjacent-months',
                    //     ]
                    // ],
                    // [
                    //     'title' => 'Start Time',
                    //     'name' => 'start_time',
                    //     'type' => 'time',
                    //     'default' => '',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,
                    //     'props' => [
                    //         'color' => "red lighten-1",
                    //         'prepend-icon' => 'mdi-calendar',
                    //         // 'prepend-inner-icon' => 'mdi-calendar',
                    //         'dense',
                    //         'outlined'
                    //     ],
                    //     'picker_props' => [
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         // 'type' => "month",
                    //         // 'range',

                    //         // 'show-adjacent-months',
                    //     ]
                    // ],
                ],
                'inputs' => [
                    'name' => [
                        'type' => 'text',
                        'title' => 'Name',
                        'name' => 'name',
                        // 'placeholder' => '',
                        'default' => '',
                        'col' => [
                            'cols' => 12,
                            'sm' => 8,
                            'md' => 6,
                        ],

                        // 'outlined',
                        'placeholder' => '',
                        'prepend-icon' => 'mdi-card-text-outline',
                        'dense'

                    ],
                    [
                        'type' => 'text',
                        'title' => 'Guard Name',
                        'name' => 'guard_name',
                        'placeholder' => 'web',
                        'default' => 'web',
                        'col' => [
                            'cols' => 12,
                            'sm' => 8,
                            'md' => 6,
                        ],

                        'prepend-icon' => 'mdi-account-child',
                        'readonly',
                        'dense',
                        'disabled',
                        'error',
                        'flat',
                        // 'full-width',
                        'hide-spin-buttons',
                        'outlined',

                    ],

                    // [
                    //     'title' => 'Permission',
                    //     'name' => 'permissions',
                    //     // 'type' => 'radio',
                    //     'type' => 'select',
                    //     'default' => 0,
                    //     'items' => [
                    //         [
                    //             'text' => 'Edit Role',
                    //             'value' => 0,
                    //             'disabled' => false,
                    //         ],
                    //         [
                    //             'text' => 'Create Role',
                    //             'value' => 1
                    //         ],
                    //         [
                    //             'text' => 'Delete Role',
                    //             'value' => 2
                    //         ],
                    //     ],
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'color' => 'success',
                    //         'mandatory',
                    //         'row',
                    //         'outlined',
                    //         'dense',
                    //         'menu-props' => [
                    //             'closeOnClick' => true,
                    //             'closeOnContentClick' => false,
                    //             'disableKeys' => true,
                    //             'openOnClick' => false,
                    //             'maxHeight' => 304
                    //         ]
                    //     ],
                    // ],

                    // [
                    //     'title' => 'Activity of Permission',
                    //     'name' => 'is_active',
                    //     'type' => 'checkbox',
                    //     // 'type' => 'switch',
                    //     'default' => true,
                    //     'cols' => 6,
                    //     'md' => 9,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'color' => 'success',
                    //         // 'readonly',
                    //         'dense',
                    //         // 'disabled',
                    //         // 'error',
                    //         'flat',
                    //         'full-width',
                    //         'hide-spin-buttons',
                    //         // 'false-value' => false,

                    //         // 'false-value' => true,
                    //         // 'true-value' => false,
                    //         // 'appendIcon' => 'mdi-dropbox',
                    //         // 'prependIcon' => 'mdi-radioactive',
                    //         // 'offIcon' => 'mdi-inactive',
                    //         // 'onIcon' => 'mdi-radioactive',
                    //     ],
                    // ],
                    // [
                    //     'title' => 'Status',
                    //     'name' => 'status',
                    //     // 'type' => 'radio',
                    //     'type' => 'radio',
                    //     'options' => [
                    //         [
                    //             'label' => 'WAITING',
                    //             'value' => 0
                    //         ],
                    //         [
                    //             'label' => 'FAILURE',
                    //             'value' => 1
                    //         ],
                    //         [
                    //             'label' => 'COMPLETED',
                    //             'value' => 2
                    //         ],
                    //     ],
                    //     'default' => 0,
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'activeClass' => '',
                    //         'color' => 'success',

                    //         'mandatory',
                    //         'row',

                    //         'props' => [
                    //             'color' => 'error',
                    //             'on-icon' => '$radioOn',
                    //             'off-icon' => '$radioOff'
                    //         ]

                    //         // 'appendIcon' => 'mdi-dropbox',
                    //         // 'prependIcon' => 'mdi-radioactive',
                    //         // 'offIcon' => 'mdi-inactive',
                    //         // 'onIcon' => 'mdi-radioactive',
                    //     ],
                    // ],
                    // [
                    //     'title' => 'Report',
                    //     'name' => 'report',
                    //     'type' => 'file',
                    //     // 'accept' => "image/*,.doc,.docx,.pdf",
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'small-chips',
                    //         'prependIcon' => '',
                    //         'prependInnerIcon' => 'mdi-camera'

                    //     ]
                    // ],
                    // [
                    //     'title' => 'Day Interval',
                    //     'name' => 'day_interval',
                    //     'type' => 'range',
                    //     'default' => [0,100],
                    //     'cols' => 12,
                    //     'md' => 12,
                    //     'sm' => 12,
                    //     'props' => [
                    //         'max' => 100,
                    //         'min' => 0,
                    //         'tick-size' => 1,
                    //         // 'background-color' => 'success',
                    //         'hint' => '',

                    //         // 'vertical',
                    //     ]
                    // ],
                    // [
                    //     'title' => 'Color',
                    //     'name' => 'color',
                    //     'type' => 'color',
                    //     'default' => '#32010121',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,

                    //     'props' => [
                    //         'placeholder' => '#FFDD11FF',
                    //         // 'dotSize' => 'rgba',
                    //         'prepend-icon' => 'mdi-palette',
                    //         'props' => [
                    //             'dotSize' => 25,
                    //             'maxHeight' => 200,
                    //         ]


                    //     ]
                    // ],
                    // [
                    //     'title' => 'Start Date',
                    //     'name' => 'start_date',
                    //     'type' => 'date',
                    //     'default' => '',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,
                    //     'props' => [
                    //         'color' => "red lighten-1",
                    //         'prepend-icon' => 'mdi-calendar',
                    //         // 'prepend-inner-icon' => 'mdi-calendar',
                    //         'dense',
                    //         'outlined'
                    //     ],
                    //     'picker_props' => [
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         'min' => "2016-06-15",
                    //         'max' => "2018-03-20",
                    //         // 'type' => "month",
                    //         // 'range',

                    //         // 'show-adjacent-months',
                    //     ]
                    // ],
                    // [
                    //     'title' => 'Start Time',
                    //     'name' => 'start_time',
                    //     'type' => 'time',
                    //     'default' => '',
                    //     'cols' => 12,
                    //     'sm' => 12,
                    //     'md' => 12,
                    //     'props' => [
                    //         'color' => "red lighten-1",
                    //         'prepend-icon' => 'mdi-calendar',
                    //         // 'prepend-inner-icon' => 'mdi-calendar',
                    //         'dense',
                    //         'outlined'
                    //     ],
                    //     'picker_props' => [
                    //         'color' => 'success',
                    //         'header-color' => 'info',
                    //         // 'type' => "month",
                    //         // 'range',

                    //         // 'show-adjacent-months',
                    //     ]
                    // ],
                ],
                'rules' => [
                    'view' => [],
                    'store' => [],
                    'update' => [],
                    'destroy' => []
                ],
            ]
        ],
    ]

];

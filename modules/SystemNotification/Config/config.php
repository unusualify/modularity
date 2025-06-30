<?php

use Unusualify\Modularity\View\Component;

return [
    'name' => 'SystemNotification',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Notifications',
    'base_prefix' => false,
    'routes' => [
        'notification' => [
            'name' => 'Notification',
            'headline' => 'All Notifications',
            'url' => 'all-notifications',
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
                        10,
                    ],
                ],
                [
                    'title' => 'Read',
                    'key' => 'is_read',
                    'formatter' => [
                        'status',
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
        /** TODO: Add multiple routes for my-notification and company-notification, sample clone routing configuration */
            // 'multiple' => [
            //     'my-notification' => [
            //         'url' => 'my-notifications',
            //         'route_name' => 'my_notification',
            //         'scopes' => [
            //             'myNotification' => true
            //         ],
            //     ],
            //     'company-notification' => [
            //         'url' => 'company-notifications',
            //         'route_name' => 'company_notification',
            //         'scopes' => [
            //             'companyNotification' => true
            //         ]
            //     ]

            // ]
        ],
        'my_notification' => [
            'name' => 'MyNotification',
            'headline' => 'Notifications',
            'url' => 'notifications',
            'route_name' => 'my_notification',
            'icon' => '$submodule',
            'title_column_key' => 'created_at',
            'scopes' => [
                'myNotification' => true,
                // 'companyNotification' => true
            ],
            'table_options' => [
                'subtitle' => __('You can easily monitor the entire process by following the notifications.'),

                'createOnModal' => false,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'no_default_table_row_actions' => true,
            'table_row_actions' => [
                'read' => [
                    'label' => 'See Notification',
                    'icon' => 'mdi-bell-check',
                    'color' => 'info',
                    'variant' => 'flat',
                    // 'href' => 'notification.read',
                    // 'target' => '_blank',
                    'conditions' => [
                        ['is_read', '=', true],
                    ],
                    'modalService' => [
                        'modalProps' => [
                            'title' => '$subject',
                            'hasCloseButton' => true,
                            'noActions' => true,
                        ],
                        'component' => 'ue-recursive-stuff',
                        'props' => [
                            'configuration' => Component::makeDiv()
                                ->setElements([
                                    Component::makeUeTitle()
                                        ->setAttributes([
                                            'tag' => 'div',
                                            'type' => 'text-body-2',
                                            'text' => '$html_message',
                                            // 'color' => 'primary',
                                            'weight' => 'regular',
                                            'transform' => 'none',
                                            'justify' => 'center',
                                        ]),
                                    Component::makeDiv()
                                        ->setAttributes([
                                            'class' => 'd-flex justify-center my-4',
                                        ])
                                        ->setElements([
                                            Component::makeVBtn()
                                                ->setAttributes([
                                                    'variant' => 'elevated',
                                                    'href' => '$redirector',
                                                    'target' => '_blank',
                                                ])
                                                ->setElements('$redirector_text'),
                                        ]),
                                ]),
                        ],
                    ],
                ],
                'unread' => [
                    'label' => 'Read Notification',
                    'icon' => 'mdi-bell-alert',
                    'color' => 'orange-darken-1',
                    'variant' => 'flat',
                    'conditions' => [
                        ['is_read', '!=', true],
                    ],
                    'modalService' => [
                        'modalProps' => [
                            'title' => '$subject',
                            'hasCloseButton' => true,
                            'noActions' => true,
                        ],
                        'component' => 'ue-recursive-stuff',
                        'props' => [
                            'configuration' => Component::makeDiv()
                                ->setElements([
                                    Component::makeUeTitle()
                                        ->setAttributes([
                                            'tag' => 'div',
                                            'type' => 'text-body-2',
                                            'text' => '$html_message',
                                            // 'color' => 'primary',
                                            'weight' => 'regular',
                                            'transform' => 'none',
                                            'justify' => 'center',
                                        ]),
                                    Component::makeDiv()
                                        ->setAttributes([
                                            'class' => 'd-flex justify-center my-4',
                                        ])
                                        ->setElements([
                                            Component::makeVBtn()
                                                ->setAttributes([
                                                    'variant' => 'elevated',
                                                    'href' => '$redirector',
                                                    'target' => '_blank',
                                                ])
                                                ->setElements('$redirector_text'),
                                        ]),
                                ]),
                        ],
                    ],
                    'preProcesses' => [
                        [
                            'type' => 'put',
                            'payload' => [
                                'read_at' => '{new Date()}',
                            ],
                            'conditions' => [
                                ['is_mine', '=', true],
                            ],
                        ],
                    ],
                ],
                // 'delete' => [
                //     'name' => 'delete',
                //     'label' => 'Delete',
                //     'icon' => 'mdi-delete',
                //     'color' => 'error',
                //     'variant' => 'outlined',
                // ]
            ],
            'table_actions' => [
                'bulk-mark-read' => [
                    'label' => __('Mark All as Read'),
                    'forceLabel' => true,
                    'icon' => 'mdi-check',
                    'color' => 'success',
                    'variant' => 'outlined',
                    'density' => 'comfortable',
                    'href' => 'admin.system.system_notification.my_notification.bulkMarkRead',
                    'target' => '_self',
                ],
            ],
            'default_filter_status' => 'my-notification',
            'table_filters' => [
                // 'my-notification' => [
                //     'name' => 'Mine',
                //     'slug' => 'my-notification',
                //     'scope' => 'myNotification',
                // ],
                'read' => [
                    'name' => 'Read',
                    'slug' => 'read',
                    'scope' => 'read',
                ],
                'unread' => [
                    'name' => 'Unread',
                    'slug' => 'unread',
                    'scope' => 'unread',
                ],
            ],
            'headers' => [
                [
                    'title' => 'Subject',
                    'key' => 'subject',
                    'formatter' => [
                        'shorten',
                        30,
                    ],
                    'searchKey' => 'data->subject',
                    'searchable' => true,
                ],
                [
                    'title' => 'Message',
                    'key' => 'message',
                    'formatter' => [
                        'shorten',
                        30,
                    ],
                    'searchKey' => 'data->message',
                    'searchable' => true,
                ],
                [
                    'title' => 'Read',
                    'key' => 'is_read',
                    'formatter' => [
                        'status',
                    ],
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
                    'name' => 'message',
                    'label' => 'Message',
                    'editable' => 'hidden',
                    'noSubmit' => true,
                ],
                [
                    'type' => 'preview',
                    'name' => 'messagePreview',
                    'previewKey' => 'message',
                    'creatable' => 'hidden',
                    'noSubmit' => true,
                    'default' => null,
                    'col' => ['cols' => 12],
                    'configuration' => [
                        'tag' => 'v-card',
                        'attributes' => [
                            'class' => 'py-4 mb-4',
                            'elevation' => 2,
                        ],
                        'slots' => [
                            'title' => [
                                'tag' => 'div',
                                'attributes' => [
                                    'class' => 'text-body-1',
                                ],
                                'elements' => __('Message'),
                            ],
                            'prepend' => [
                                'tag' => 'v-icon',
                                'attributes' => [
                                    'icon' => 'mdi-file-document-outline',
                                    'color' => 'primary',
                                ],
                            ],
                            'text' => [
                                'tag' => 'div',
                                'attributes' => [
                                    'class' => 'font-weight-bold text-wrap pt-2 text-primary',
                                ],
                                'elements' => '$messagePreview',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

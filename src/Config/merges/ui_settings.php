<?php

return [
    'sidebar' => [
        'expandOnHover' => true,
        'rail' => false,
        'mainLocation' => 'right',
        'permanent' => true,
        'showIcon' => false,
        'isMini' => true,
        'contentDrawer' => [
            'exists' => false,
            'float' => false,
            'rail' => true,
            'permanent' => true,
        ],
    ],
    'secondarySidebar' => [
        'exists' => false,
        'location' => 'right',
        'rail' => false,
        'permanent' => true,
        'max-width' => '10em'
    ],

    'profileMenu' => [
        'superadmin' => [
             [
                'name' => 'Profile Settings',
                // 'route_name' => 'admin.profile',
                'icon' => '$accountSettings',
                'menuActivator' => 'profile',
                'menuItems' => [
                    'profile' => [
                        'name' => 'Profile Settings',
                        'route_name' => 'admin.profile',
                        'icon' => '$accountSettings',
                    ],
                ]

            ],
            [
                'name' => 'Dashboard',
                'menuActivator' => 'dashboard',
                'icon' => '$dashboard',
                'menuItems' => [
                    'facebook' => [
                        'name' => 'Facebook',
                        'href' => 'https://www.facebook.com',
                        'icon' => 'mdi-facebook'
                    ],
                    'dashboard' => [
                        'name' => 'Dashboard',
                        'route_name' => 'admin.dashboard',
                        'icon' => '$dashboard',
                    ],
                    'z' => [
                        'name' => 'Permissions',
                        'route_name' => 'admin.system.system_user.permission.index',
                        'icon' => '$accountSettings',
                    ]
                ]
            ],
            'permissions' => [
                'name' => 'Permissions',
                'route_name' => 'admin.system.system_user.permission.index',
                'icon' => '$accountSettings',
            ]
        ]
    ],

    'dashboard' => [
        'blocks' => [
            [
                'component' => 'board-information-plus',
                'col' => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    's' => 12,
                    'class' => 'pr-theme-semi pb-theme-semi'
                ],
                'attributes' => [
                    'container' => [
                        'color' => '#F8F8FF',
                        'elevation' => 10,
                        'class' => 'px-5 py-5',
                    ],
                    'cardAttribute' => [
                        'variant' => 'outlined',
                        'borderRadius' => '14px',
                        'border' => 'md',
                        'titleClass' => 'pt-3 pb-3 text-subtitle-2',
                        'titleColor' => 'grey',
                        'infoClass' => 'text-h4 pt-0 pb-5',
                        'infoColor' => '#000000',
                    ]
                ],
                'cards' => [
                    [
                    'title' => 'Active Webinar',
                    'repository' => 'Modules\\Webinar\\Repositories\\VimeoWebinarRepository',
                    'method' => 'count',
                    'flex' => 6,
                    ],
                    [
                    'title' => 'Speaker & Moderators',
                    'repository' => 'Modules\\Webinar\\Repositories\\ModeratorRepository',
                    'method' => 'count',
                    'flex' => 6,
                    ],
                    [
                    'title' => 'Company',
                    'repository' => 'Modules\\Webinar\\Repositories\\WebCompanyRepository',
                    'method' => 'count',
                    'flex' => 6,
                    ],
                    [
                    'title' => 'Published Webinars',
                    'repository' => 'Modules\\Webinar\\Repositories\\VimeoWebinarRepository',
                    'method' => 'where:web_company_id:3|count',
                    'flex' => 6,
                    ],
                ]
            ],
            [
                'component' => 'new-table',
                'col' => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    's' => 12,
                    'class' => 'pl-theme-semi pb-theme-semi',
                ],
                'controller' => 'Modules\\Webinar\\Http\\Controllers\\VimeoWebinarController',
                'attributes' => [
                    'customTitle' => 'Vimeo Webinars',
                    'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                    'tableType' => 'dashboard',

                    'hideHeaders' => true,
                    'fullWidthWrapper' => true,
                    'hideSearchField' => true,
                    'fillHeight' => true,
                    'style' => '',
                    'columns' => [
                        [
                            'title' => 'Date',
                            'key' => 'start_date',
                            'align' => 'start',
                            'sortable' => true,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            // 'max-width' => 'max-content',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => false,
                            'formatter' => [
                                // 'date',
                                // 'numeric'
                            ]
                            // 'formatter' => ['date', 'numeric'],
                        ],
                        [
                            'title' => 'Name',
                            'key' => 'name',
                            'align' => 'start',
                            'sortable' => false,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => true,
                            'formatter' => [
                            ],
                        ],
                        [
                            'title' => 'Published',
                            'key' => 'published',
                            'align' => 'start',
                            'sortable' => false,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => true,
                            'formatter' => [
                                'status',
                                [
                                    'Not Published',
                                    'Published'
                                ],
                                [
                                    'blue',
                                    'red',
                                ]
                            ],
                        ],
                    ],
                    'tableOptions' => [
                        'page'          => 1,
                        'sortBy'        => [],
                        'multiSort'     => false,
                        'mustSort'      => false,
                        'groupBy'       => [],
                        'itemsPerPage'  => 10,
                        'tableType' => 'dashboard', // !!!!!!
                    ],
                    'slots' => [
                        'bottom' => [
                            'elements' => [
                                [
                                    'tag' => 'div',
                                    'attributes' => [
                                        'class' => 'text-right pa-8',
                                    ],
                                    'elements' => [
                                        [
                                            'tag' => 'v-btn-tertiary',
                                            'elements' => 'MANAGE RELEASES',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            [
                'component' => 'new-table',
                'col' => [
                    'cols' => 12,
                    'xxl' => 12,
                    'xl' => 12,
                    'lg' => 12,
                    's' => 12,
                    'class' => 'pl-theme-semi pb-theme-semi',
                ],
                'controller' => 'Modules\\Webinar\\Http\\Controllers\\VimeoWebinarController',
                'attributes' => [
                    'customTitle' => 'Vimeo Webinars',
                    'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                    'tableType' => 'dashboard',
                    'hideHeaders' => false,
                    'rowActionsType' => 'dropdown',
                    'rowActionsIcon' => 'mdi-dots-vertical',
                    'fullWidthWrapper' => true,
                    'hideSearchField' => true,
                    'fillHeight' => true,
                    'style' => '',
                    'columns' => [
                        [
                            'title' => 'Date',
                            'key' => 'start_date',
                            'align' => 'start',
                            'sortable' => true,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            // 'max-width' => 'max-content',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => false,
                            'formatter' => [
                                // 'date',
                                // 'numeric'
                            ]
                            // 'formatter' => ['date', 'numeric'],
                        ],
                        [
                            'title' => 'Name',
                            'key' => 'name',
                            'align' => 'start',
                            'sortable' => false,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => true,
                            'formatter' => [
                                'edit'
                            ],
                        ],
                        [
                            'title' => 'Company',
                            'key' => 'webCompany_relation',
                            'align' => 'start',
                            'sortable' => false,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => true,
                            'formatter' => [
                            ],
                        ],
                        [
                            'title' => 'Published',
                            'key' => 'published',
                            'align' => 'start',
                            'sortable' => false,
                            'filterable' => false,
                            'groupable' => false,
                            'divider' => false,
                            'class' => '',
                            'cellClass' => '',
                            'width' => '',
                            'searchable' => true,
                            'isRowEditable' => true,
                            'isColumnEditable' => true,
                            'formatter' => [
                                'status',
                                [
                                    'Not Published',
                                    'Published'
                                ],
                                [
                                    'blue',
                                    'red',
                                ]
                            ],
                        ],
                        [
                            'title' => 'Actions',
                            'key' => 'actions',
                            'sortable' => false,
                        ]
                    ],
                    'tableOptions' => [
                        'page'          => 1,
                        'sortBy'        => [],
                        'multiSort'     => false,
                        'mustSort'      => false,
                        'groupBy'       => [],
                        'itemsPerPage'  => 10,
                        'tableType' => 'dashboard',
                         // !!!!!!
                    ],
                    'slots' => [
                        'bottom' => [
                            'elements' => [
                                [
                                    'tag' => 'div',
                                    'attributes' => [
                                        'class' => 'text-right pa-8',
                                    ],
                                    'elements' => [
                                        [
                                            'tag' => 'v-btn-tertiary',
                                            'elements' => 'MANAGE RELEASES',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],


        ]
    ]

];

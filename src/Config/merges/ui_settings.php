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
                    'cols' => 6,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
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


        ]
    ]

];

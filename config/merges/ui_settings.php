<?php

return [
    'sidebar' => [
        'width' => 264,
        'expandOnHover' => true,
        'rail' => false,
        'location' => 'left',
        'persistent' => false,
        'hideIcons' => false,
        'railWidth' => 130,
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
        'max-width' => '10em',
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
                ],

            ],
            [
                'name' => 'Dashboard',
                'menuActivator' => 'dashboard',
                'icon' => '$dashboard',
                'menuItems' => [
                    'facebook' => [
                        'name' => 'Facebook',
                        'href' => 'https://www.facebook.com',
                        'icon' => 'mdi-facebook',
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
                    ],
                ],
            ],
            'permissions' => [
                'name' => 'Permissions',
                'route_name' => 'admin.system.system_user.permission.index',
                'icon' => '$accountSettings',
            ],
        ],
        'default' => [
            [
                'name' => 'Profile Settings',
                'route_name' => 'admin.profile',
                'icon' => '$accountSettings',

            ],
            'permissions' => [
                'name' => 'Permissions',
                'route_name' => 'admin.system.system_user.permission.index',
                'icon' => '$accountSettings',
            ],
        ],
        'client' => [
            [
                'name' => 'Profile Settings',
                'route_name' => 'admin.profile',
                'icon' => '$accountSettings',

            ],
            // 'permissions' => [
            //     'name' => 'Permissions',
            //     'route_name' => 'admin.system.system_user.permission.index',
            //     'icon' => '$accountSettings',
            // ],
        ],
    ],

    'dashboard' => [
        'blocks' => [

        ],
    ],

];

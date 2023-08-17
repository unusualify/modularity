<?php

use OoBook\CRM\Base\Facades\UnusualNavigation;

return [
    /*
    |--------------------------------------------------------------------------
    | Unusual sidebar & breadcrumbs & topbar configuration
    |--------------------------------------------------------------------------
    |
    */
    'sidebar' => [
        'default' => [
            'dashboard' => [
                'name' => 'Dashboard',
                'route_name' => 'dashboard',
            ],
        ],
        'superadmin' => [
            'dashboard' => [
                'name' => 'Dashboard',
                'route_name' => 'dashboard',
            ],
            '_system_modules' => [
                'name' => 'System Modules',
            ],
            ...UnusualNavigation::baseMenu(),
            '_modules' => [
                'name' => 'Modules',
            ],
            ...UnusualNavigation::modulesMenu(),
            'media_library' => [
                'name' => 'Media Library',
                // 'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
            'profile' => [
                'name' => 'Settings',
                'route_name' => 'profile',
            ],
        ],
        'client' => [
            'account' => [
                'name' => 'My Account',
                'items' => [
                    'dashboard' => [
                        'name' => 'Dashboard',
                        'route_name' => 'dashboard',
                    ],
                    'profile' => [
                        'name' => 'Profile',
                        'route_name' => 'profile',
                    ]
                ]
            ],
        ],
    ]

];

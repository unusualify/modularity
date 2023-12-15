<?php

use Unusualify\Modularity\Facades\UNavigation;

// dd(
//     UNavigation::baseMenu(),
//     UNavigation::modulesMenu()
// );
// dd(
//     phpArrayFileContent(UNavigation::modulesMenu()['package']),
// );
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
            '_system_settings' => [
                'name' => 'System Settings',
            ],
            ...UNavigation::systemMenu(),
            'locales' => [
                'name' => 'Locales',
                'icon' => 'mdi-exit-to-app',
                'route_name' => 'languages.index',
                'target' => '_blank',
            ],
            '_modules' => [
                'name' => 'Modules',
            ],
            ...UNavigation::modulesMenu(),
            'media_library' => [
                'name' => 'Media Library',
                // 'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
            'profile' => [
                'name' => 'Profile Settings',
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

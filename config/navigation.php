<?php

use Unusualify\Modularity\Facades\UNavigation;

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
                'route_name' => 'admin.dashboard',
                'icon' => '$dashboard',
            ],
            // 'media_library' => [
            //     'name' => 'Media Library',
            //     'icon' => '$media',
            //     'attr' => 'data-medialib-btn',
            //     // 'event' => '_triggerOpenMediaLibrary',
            //     'event' => 'openFreeMediaLibrary',
            // ],
        ],
        'superadmin' => [
            '_modules' => [
                'name' => 'Modules',
                'icon' => '$header',
            ],
            ...UNavigation::modulesMenu(),
            '_system_settings' => [
                'name' => 'System Settings',
                'icon' => '$header',
            ],
            ...UNavigation::systemMenu(),
            'media_library' => [
                'name' => 'Media Library',
                'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
            '_vendor' => [
                'name' => 'Vendor',
                'icon' => '$header',
            ],
            '_locales' => [
                'name' => 'Locales',
                'icon' => 'mdi-exit-to-app',
                'route_name' => 'languages.index',
                'target' => '_blank',
            ],
            '_horizon' => [
                'name' => 'Horizon',
                'icon' => 'mdi-exit-to-app',
                'route_name' => 'horizon.index',
                'target' => '_blank',
            ],
            '_telescope' => [
                'name' => 'Telescope',
                'icon' => 'mdi-exit-to-app',
                'route_name' => 'telescope',
                'target' => '_blank',
            ],
        ],
        'client' => [
            // 'account' => [
            //     'name' => 'My Account',
            //     'items' => [
            //         'dashboard' => [
            //             'name' => 'Dashboard',
            //             'route_name' => 'admin.dashboard',
            //         ],
            //         'profile' => [
            //             'name' => 'Profile',
            //             'route_name' => 'admin.profile',
            //         ],
            //     ],
            // ],
            // 'media_library' => [
            //     'name' => 'Media Library',
            //     'icon' => '$media',
            //     'attr' => 'data-medialib-btn',
            //     // 'event' => '_triggerOpenMediaLibrary',
            //     'event' => 'openFreeMediaLibrary',
            // ],
        ],
        'guest' => [
            'register' => [
                'name' => 'Register',
                'route_name' => 'register.form',
                'icon' => '$userAdd',
            ],
        ],
    ],

];

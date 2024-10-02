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
            ],
            'media_library' => [
                'name' => 'Media Library',
                'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
        ],
        'superadmin' => [
            'dashboard' => [
                'name' => 'Dashboard',
                'route_name' => 'admin.dashboard',
                'icon' => '$dashboard',
            ],
            '_system_settings' => [
                'name' => 'System Settings',
            ],
            ...UNavigation::systemMenu(),
            'locales' => [
                'name' => 'Locales',
                'icon' => 'mdi-exit-to-app',
                'route_name' => 'admin.languages.index',
                'target' => '_blank',
            ],
            '_modules' => [
                'name' => 'Modules',
            ],
            ...UNavigation::modulesMenu(),
            'media_library' => [
                'name' => 'Media Library',
                'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
            'profile' => [
                'name' => 'Profile Settings',
                'route_name' => 'admin.profile',
                'icon' => '$accountSettings',
            ],

        ],
        'client' => [
            'account' => [
                'name' => 'My Account',
                'items' => [
                    'dashboard' => [
                        'name' => 'Dashboard',
                        'route_name' => 'admin.dashboard',
                    ],
                    'profile' => [
                        'name' => 'Profile',
                        'route_name' => 'admin.profile',
                    ],
                ],
            ],
            'media_library' => [
                'name' => 'Media Library',
                'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ],
        ],
        'guest' => [
            'register' => [
                'name' => 'Register',
                'route_name' => 'register.form',
            ],
        ],
    ],

];

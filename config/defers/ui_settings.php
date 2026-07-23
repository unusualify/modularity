<?php

return [
    'auth' => [
        'logoSymbol' => 'main-logo',
    ],
    'sidebar' => [
        'width' => 264,
        'expandHover' => 'mini', // 'mini' | 'hidden'
        'expandOnHover' => true,
        'rail' => false,
        'location' => 'left', // 'left' | 'right'
        'persistent' => false,
        'hideIcons' => false,
        'railWidth' => 130,
        'contentDrawer' => [
            'exists' => false,
            'float' => false,
            'rail' => true,
            'permanent' => true,
        ],

        // runs on Main.vue component but not on store/modules/config.js
        'logoSymbol' => 'main-logo-dark',
    ],
    'secondarySidebar' => [
        'exists' => false,
        'location' => 'right',
        'rail' => false,
        'permanent' => true,
        'max-width' => '10em',
    ],
    /**
     * Top bar (v-app-bar) configuration.
     * User preferences override these defaults and are persisted in DB.
     */
    'topbar' => [
        'enabled' => true,
        'fixed' => false,
        'order' => 0, // Vuetify layout order (0 = above drawer)
        'showOnMobile' => true,
        'showOnDesktop' => false,
    ],
    /**
     * Bottom navigation (v-bottom-navigation) configuration.
     * Useful for mobile-first layouts.
     */
    'bottomNavigation' => [
        'enabled' => false,
        'showOnMobile' => true,
        'showOnDesktop' => false,
    ],
    'dashboard' => [
        'blocks' => [
            'system-console' => [
                'widget' => 'SystemConsoleWidget',
                'widgetCol' => [
                    'cols' => 12,
                    'lg' => 6,
                ],
                'widgetAttributes' => [
                    'class' => 'h-50',
                    'style' => 'min-height: 160px',
                ],
                'allowedRoles' => ['superadmin'],
                'attributes' => [
                    'class' => 'h-100',
                    'title' => 'System Console',
                    'subtitle' => 'Maintenance mode and cache / optimize commands.',
                    'elevation' => 2,
                ],
            ],
            'artisan-runner' => [
                'widget' => 'ArtisanRunnerWidget',
                'widgetCol' => [
                    'cols' => 12,
                    'lg' => 6,
                ],
                'widgetAttributes' => [
                    'class' => 'h-50',
                    'style' => 'min-height: 160px',
                ],
                'allowedRoles' => ['superadmin'],
                'attributes' => [
                    'class' => 'h-100',
                    'title' => 'Artisan Runner',
                    'subtitle' => 'Select a command to configure and run.',
                    'elevation' => 2,
                ],
            ],

        ],
    ]
];

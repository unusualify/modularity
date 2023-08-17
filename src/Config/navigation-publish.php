<?php

return [
    'sidebar' => [
        'default' => [
            [
                'name' => 'Dashboard',
                'route_name' => 'dashboard',
            ],
            [
                'name' => 'Users',
                'route_name' => 'users.index',
            ],
        ],
        'client' => [
            [
                'name' => 'My Account',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'route_name' => 'dashboard',
                    ],
                    [
                        'name' => 'Profile',
                        'route_name' => 'profile',
                    ]
                ]
            ],
        ],
    ],

];

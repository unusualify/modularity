<?php

return [
    'stateable' => [
        'channels' => env('NOTIFICATIONS_STATEABLE_CHANNELS', 'database,mail'),
    ],
    'chatable' => [
        'channels' => env('NOTIFICATIONS_CHATABLE_CHANNELS', 'database,mail'),
    ],
    'assignable' => [
        'channels' => env('NOTIFICATIONS_ASSIGNABLE_CHANNELS', 'database,mail'),
    ],
    'authorizable' => [
        'channels' => env('NOTIFICATIONS_AUTHORIZABLE_CHANNELS', 'database,mail'),
    ],
];

<?php

return [
    'mail_connection' => env('MODULARITY_NOTIFICATIONS_MAIL_CONNECTION', config('queue.default')),
    'database_connection' => env('MODULARITY_NOTIFICATIONS_DATABASE_CONNECTION', 'sync'),
    'mail_queue' => env('MODULARITY_NOTIFICATIONS_MAIL_QUEUE', 'mail'),
    'database_queue' => env('MODULARITY_NOTIFICATIONS_DATABASE_QUEUE', 'default'),

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

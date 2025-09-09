<?php

return [
    'mail_connection' => env('MODULARITY_NOTIFICATIONS_MAIL_CONNECTION', config('queue.default')),
    'database_connection' => env('MODULARITY_NOTIFICATIONS_DATABASE_CONNECTION', 'sync'),
    'mail_queue' => env('MODULARITY_NOTIFICATIONS_MAIL_QUEUE', 'mail'),
    'database_queue' => env('MODULARITY_NOTIFICATIONS_DATABASE_QUEUE', 'default'),

    'stateable' => [
        'channels' => env('NOTIFICATIONS_STATEABLE_CHANNELS', ''),
    ],
    'chatable' => [
        'channels' => env('NOTIFICATIONS_CHATABLE_CHANNELS', ''),
    ],
    'assignable' => [
        'channels' => env('NOTIFICATIONS_ASSIGNABLE_CHANNELS', ''),
    ],
    'authorizable' => [
        'channels' => env('NOTIFICATIONS_AUTHORIZABLE_CHANNELS', ''),
    ],

    'Modules\SystemNotification\Notifications\ChatableUnreadNotification' => [
        'channels' => env('MODULARITY_NOTIFICATIONS_CHATABLE_UNREAD_CHANNELS', ''),
    ],
    'Modules\SystemNotification\Notifications\PaymentCompletedNotification' => [
        'channels' => env('MODULARITY_NOTIFICATIONS_PAYMENT_COMPLETED_CHANNELS', ''),
    ],
    'Modules\SystemNotification\Notifications\StateableUpdatedNotification' => [
        'channels' => env('MODULARITY_NOTIFICATIONS_STATEABLE_UPDATED_CHANNELS', ''),
    ],
    'Modules\SystemNotification\Notifications\TaskCreatedNotification' => [
        'channels' => env('MODULARITY_NOTIFICATIONS_TASK_CREATED_CHANNELS', ''),
    ],
    'Modules\SystemNotification\Notifications\TaskUpdatedNotification' => [
        'channels' => env('MODULARITY_NOTIFICATIONS_TASK_UPDATED_CHANNELS', ''),
    ],
];

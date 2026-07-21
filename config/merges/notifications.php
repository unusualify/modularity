<?php

return [
    'mail_connection' => env('MODULAROUS_NOTIFICATIONS_MAIL_CONNECTION', config('queue.default')),
    'database_connection' => env('MODULAROUS_NOTIFICATIONS_DATABASE_CONNECTION', 'sync'),
    'broadcast_connection' => env('MODULAROUS_NOTIFICATIONS_BROADCAST_CONNECTION', config('queue.default')),
    'mail_queue' => env('MODULAROUS_NOTIFICATIONS_MAIL_QUEUE', 'mail'),
    'database_queue' => env('MODULAROUS_NOTIFICATIONS_DATABASE_QUEUE', 'default'),

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
        'channels' => env('MODULAROUS_NOTIFICATIONS_CHATABLE_UNREAD_CHANNELS', 'database,mail,broadcast'),
    ],
    'Modules\SystemNotification\Notifications\PaymentCompletedNotification' => [
        'channels' => env('MODULAROUS_NOTIFICATIONS_PAYMENT_COMPLETED_CHANNELS', 'database,mail,broadcast'),
    ],
    'Modules\SystemNotification\Notifications\PaymentFailedNotification' => [
        'channels' => env('MODULAROUS_NOTIFICATIONS_PAYMENT_FAILED_CHANNELS', 'database,mail,broadcast'),
    ],
    'Modules\SystemNotification\Notifications\StateableUpdatedNotification' => [
        'channels' => env('MODULAROUS_NOTIFICATIONS_STATEABLE_UPDATED_CHANNELS', 'database,mail,broadcast'),
    ],
    'Modules\SystemNotification\Notifications\TaskCreatedNotification' => [
        'channels' => env('MODULAROUS_NOTIFICATIONS_TASK_CREATED_CHANNELS', 'database,mail,broadcast'),
    ],
    'Modules\SystemNotification\Notifications\TaskUpdatedNotification' => [
        'channels' => env('MODULAROUS_NOTIFICATIONS_TASK_UPDATED_CHANNELS', 'database,mail,broadcast'),
    ],
];

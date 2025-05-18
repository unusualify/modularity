<?php

return [
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
];

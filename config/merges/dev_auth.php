<?php

return [
    // Local-only: authenticate every panel request as a superadmin without a login session.
    'enabled' => (bool) env('MODULAROUS_DEV_AUTO_LOGIN', false),

    // Falls back to the first user holding the superadmin role when empty.
    'email' => env('MODULAROUS_DEV_AUTO_LOGIN_EMAIL', env('MODULAROUS_SUPERADMIN_EMAIL')),

    // Booting with the flag enabled outside these environments throws.
    'allowed_environments' => ['local', 'testing'],
];

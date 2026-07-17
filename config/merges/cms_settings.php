<?php

return [
    'cache_ttl' => (int) env('MODULAROUS_CMS_SETTINGS_CACHE_TTL', env('MODULAROUS_SYSTEM_SETTINGS_CACHE_TTL', 3600)),
    'sensitive_keys' => [
        'smtp.password',
    ],
];

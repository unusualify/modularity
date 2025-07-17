<?php

return [
    // Add these API configurations to your existing modularity.php config
    'prefix' => env('MODULARITY_API_PREFIX', 'api/v1'),
    'domain' => env('MODULARITY_API_DOMAIN'),
    'middlewares' => [
        'language',
        'api',
        'throttle:api',
    ],
    'public_middlewares' => [],
    'auth_middlewares' => [
        'auth:sanctum',
    ],
    'versioning' => [
        'enabled' => true,
        'default_version' => 'v1',
        'header' => 'API-Version',
    ],
    'pagination' => [
        'default_per_page' => env('MODULARITY_API_PAGINATION_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('MODULARITY_API_PAGINATION_MAX_PER_PAGE', 100),
    ],
    'rate_limiting' => [
        'enabled' => env('MODULARITY_API_RATE_LIMITING_ENABLED', true),
        'per_minute' => env('MODULARITY_API_RATE_LIMITING_PER_MINUTE', 60),
        'per_hour' => env('MODULARITY_API_RATE_LIMITING_PER_HOUR', 1000),
        'blocking_time' => env('MODULARITY_API_RATE_LIMITING_BLOCKING_TIME', 3600), // 1 hour
        'blocking_maximum_attempts' => env('MODULARITY_API_RATE_LIMITING_BLOCKING_MAXIMUM_ATTEMPTS', 250), // 250 attempts
        'blocking_time_threshold' => env('MODULARITY_API_RATE_LIMITING_BLOCKING_TIME_THRESHOLD', 300), // 5 minutes
    ],
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['*'],
    ],
    'response' => [
        'wrap_in_data' => env('MODULARITY_API_RESPONSE_WRAP_IN_DATA', true),
        'include_meta' => env('MODULARITY_API_RESPONSE_INCLUDE_META', true),
        'include_links' => env('MODULARITY_API_RESPONSE_INCLUDE_LINKS', true),
    ],
    'features' => [
        'filtering' => env('MODULARITY_API_FEATURES_FILTERING', true),
        'sorting' => env('MODULARITY_API_FEATURES_SORTING', true),
        'searching' => env('MODULARITY_API_FEATURES_SEARCHING', true),
        'including' => env('MODULARITY_API_FEATURES_INCLUDING', true),
        'field_selection' => true,
    ]
];

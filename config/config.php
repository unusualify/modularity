<?php

use Unusualify\Modularity\Activators\FileActivator;

return [
    'namespace' => 'Unusualify\\Modularity',
    'name' => env('MODULARITY_BASE_NAME', 'Modularity'),

    'system_prefix' => 'system',

    // 'app_url' => parse_url(env('APP_URL'))['host'] ?? parse_url(env('APP_URL'))['path'],
    'app_url' => env('APP_URL'),
    // 'admin_app_url' => env('ADMIN_APP_URL', env('ADMIN_APP_PATH') ? null : 'admin.' . parse_url(env('APP_URL'))['host']),
    'admin_app_url' => env('ADMIN_APP_URL', ''),
    'admin_app_path' => env('ADMIN_APP_PATH', ''),
    'admin_route_name_prefix' => env('ADMIN_ROUTE_NAME_PREFIX', 'admin'),
    'app_theme' => env('VUE_APP_THEME', 'unusualify'),

    'version' => '1.0.0',
    'auth_login_redirect_path' => '/',
    // 'is_development' => env('UNUSUAL_DEV', false),
    // 'development_url' => 'http://' . env('UNUSUAL_DEV_URL', 'localhost:8080'),
    // 'public_dir' => env('UNUSUAL_ASSETS_DIR', 'unusual'),
    'vendor_dir' => 'vendor/unusualify/modularity',

    'custom_components_resource_path' => 'vendor/modularity/js/components',
    // 'vendor_components_resource_path' => 'assets/vendor/js/components',

    'manifest' => 'unusual-manifest.json',
    'js_namespace' => env('VUE_APP_NAME', 'MODULARITY'),
    'build_timeout' => 300,
    'use_big_integers_on_migrations' => true,
    'use_inertia' => env('MODULARITY_USE_INERTIA', false),

    'locale' => 'en',
    'fallback_locale' => 'en',
    'timezone' => 'Europe/London',

    'log_dir' => storage_path('logs/modularity'),
    'email_verified_register' => env('MODULARITY_EMAIL_VERIFIED_REGISTER', true),

    'benchmark_enabled' => env('MODULARITY_BENCHMARK_ENABLED', false),
    'benchmark_log_level' => env('MODULARITY_BENCHMARK_LOG_LEVEL', null),
    'benchmark_log_file' => env('MODULARITY_BENCHMARK_LOG_FILE', 'modularity-benchmark.log'),
    'benchmark_emergency_time' => env('MODULARITY_BENCHMARK_EMERGENCY_TIME', 1000), // in milliseconds

    'base_model' => \Unusualify\Modularity\Entities\Model::class,
    'base_repository' => \Unusualify\Modularity\Repositories\Repository::class,
    'base_controller' => \Unusualify\Modularity\Http\Controllers\BaseController::class,
    'base_request' => \Unusualify\Modularity\Http\Requests\Request::class,
    'route_patterns' => [
        'id' => '[0-9]+',
        'payment' => '[0-9]+',
    ],

    'activators' => [
        'file' => [
            'class' => FileActivator::class,
            'statuses-file' => 'routes_statuses.json',
            'cache-key' => 'module-activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],
    'activator' => 'file',

    'oauth' => [

        'providers' => [
            'google' => [
                'oauth_mapping' => [
                    'avatar' => 'avatar',
                    'token' => 'token',
                    '',
                ],
                'user_mapping' => [
                    'email' => 'email',
                    'name' => 'name',
                ],
            ],
            'apple' => [
                'oauth_mapping' => [
                    'email' => 'email',
                    'name' => 'name',
                    'avatar' => 'picture',
                ],
            ],
            'github' => [
                'oauth_mapping' => [
                    'email' => 'email',
                    'name' => 'name',
                    'avatar' => 'avatar_url',
                ],
            ],
        ],

        'google' => [
            'scopes' => [
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ],
        ],

        'apple' => [
            'scopes' => [
                'name',
                'email',
            ],
        ],

        'github' => [
            'scopes' => [
                'user',
                'user:email',
            ],
        ],
    ],
    'payment_middlewares' => [],
];

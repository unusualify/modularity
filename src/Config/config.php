<?php

use Unusualify\Modularity\Activators\FileActivator;

return [
    'namespace' => env('BASE_NAMESPACE','Unusualify\\Modularity'),
    'name' => env('UNUSUAL_BASE_NAME','Modularity'),

    'system_prefix' => 'system',

    'app_url' => parse_url(env('APP_URL'))['host'] ?? parse_url(env('APP_URL'))['path'],
    'admin_app_url' => env('ADMIN_APP_URL', env('ADMIN_APP_PATH') ? null : 'admin.' . env('APP_URL')),
    'admin_app_path' => env('ADMIN_APP_PATH', ''),
    'admin_route_name_prefix' => env('ADMIN_ROUTE_NAME_PREFIX', 'admin'),
    'app_theme' => env('VUE_APP_THEME', 'unusual'),

    'version' => '1.0.0',
    'auth_login_redirect_path' => '/',
    'is_development' => env('UNUSUAL_DEV', false),
    'development_url' => "http://" . env('UNUSUAL_DEV_URL', 'localhost:8080'),
    'public_dir' => env('UNUSUAL_ASSETS_DIR', 'unusual'),
    'vendor_path' => env('UNUSUAL_VENDOR_PATH', 'vendor/unusualify/modularity'),

    'custom_components_resource_path' => 'vendor/modularity/js/components',
    // 'vendor_components_resource_path' => 'assets/vendor/js/components',

    'manifest' => 'unusual-manifest.json',
    'js_namespace' => env('VUE_APP_NAME','UNUSUAL'),
    'build_timeout' => 300,
    'use_big_integers_on_migrations' => true,

    'locale' => 'en',
    'fallback_locale' => 'en',
    'timezone' => 'Europe/London',

    'base_model' => \Unusualify\Modularity\Entities\Model::class,

    'base_repository' => \Unusualify\Modularity\Repositories\Repository::class,

    'base_controller' => \Unusualify\Modularity\Http\Controllers\BaseController::class,

    'base_request' => \Unusualify\Modularity\Http\Requests\Request::class,

    'route_patterns' => [
        'id' => '[0-9]+',
        'payment' => '[0-9]+'
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

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache' => [
        'enabled' => false,
        'key' => 'ue-modules',
        'lifetime' => 60,
    ],
];

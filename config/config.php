<?php

use Unusualify\Modularous\Activators\FileActivator;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Http\Requests\Request;
use Unusualify\Modularous\Notifications\EmailVerification;
use Unusualify\Modularous\Repositories\Repository;

return [
    'namespace' => 'Unusualify\\Modularous',
    'name' => env('MODULAROUS_BASE_NAME', 'Modularous'),

    'system_prefix' => 'system',

    'verification_email_class' => EmailVerification::class,
    // 'app_url' => parse_url(env('APP_URL'))['host'] ?? parse_url(env('APP_URL'))['path'],
    'app_url' => env('APP_URL'),
    // 'admin_app_url' => env('ADMIN_APP_URL', env('ADMIN_APP_PATH') ? null : 'admin.' . parse_url(env('APP_URL'))['host']),
    'admin_app_url' => env('ADMIN_APP_URL', ''),
    'admin_app_path' => env('ADMIN_APP_PATH', ''),
    'admin_route_name_prefix' => env('ADMIN_ROUTE_NAME_PREFIX', 'admin'),
    'app_theme' => env('VUE_APP_THEME', 'unusualify'),
    'available_user_locales' => explode(',', env('MODULAROUS_AVAILABLE_USER_LOCALES', 'en')),
    'default_register_role' => env('MODULAROUS_DEFAULT_REGISTER_ROLE', 'client-manager'),

    'version' => '1.0.0',
    'auth_login_redirect_path' => '/',
    // 'is_development' => env('UNUSUAL_DEV', false),
    // 'development_url' => 'http://' . env('UNUSUAL_DEV_URL', 'localhost:8080'),
    // 'public_dir' => env('UNUSUAL_ASSETS_DIR', 'unusual'),
    'vendor_dir' => 'vendor/unusualify/modularous',

    'custom_components_resource_path' => 'vendor/modularous/js/components',
    // 'vendor_components_resource_path' => 'assets/vendor/js/components',
    'enabled_currencies' => explode(',', env('MODULAROUS_ACTIVE_CURRENCIES', 'USD,EUR,TRY')),
    'define_panel_routes_on_frontend_requests' => env('MODULAROUS_DEFINE_PANEL_ROUTES_ON_FRONTEND_REQUESTS', true),

    /**
     * Optional custom currency provider class implementing CurrencyProviderInterface.
     * When null, SystemPricingCurrencyProvider is used if available, else NullCurrencyProvider.
     */
    'currency_provider' => env('MODULAROUS_CURRENCY_PROVIDER', null),

    'manifest' => 'unusual-manifest.json',
    'js_namespace' => env('VUE_APP_NAME', 'MODULAROUS'),
    'build_timeout' => 300,
    'use_big_integers_on_migrations' => true,
    'use_collation_for_search' => env('MODULAROUS_USE_COLLATION_FOR_SEARCH', false),

    'use_inertia' => env('MODULAROUS_USE_INERTIA', true),
    'include_transaction_fee' => env('MODULAROUS_INCLUDE_TRANSACTION_FEE', false),
    'use_country_based_vat_rates' => env('MODULAROUS_USE_COUNTRY_BASED_VAT_RATES', false),
    'use_language_based_prices' => env('MODULAROUS_USE_LANGUAGE_BASED_PRICES', false),
    'use_format_item_eager' => env('MODULAROUS_USE_FORMAT_ITEM_EAGER', false),
    'language_currencies' => [],
    'hide_description_for_language_based_prices' => env('MODULAROUS_HIDE_DESCRIPTION_FOR_LANGUAGE_BASED_PRICES', false),
    'disable_billing_banner' => env('MODULAROUS_DISABLE_BILLING_BANNER', false),
    'lock_company_edit' => env('MODULAROUS_LOCK_COMPANY_EDIT', true),

    'locale' => 'en',
    'fallback_locale' => 'en',
    'timezone' => 'Europe/London',

    'log_dir' => storage_path('logs/modularous'),
    'email_verified_register' => env('MODULAROUS_EMAIL_VERIFIED_REGISTER', true),
    'benchmark_enabled' => env('MODULAROUS_BENCHMARK_ENABLED', false),
    'benchmark_log_level' => env('MODULAROUS_BENCHMARK_LOG_LEVEL', null),
    'benchmark_log_file' => env('MODULAROUS_BENCHMARK_LOG_FILE', 'modularous-benchmark.log'),
    'benchmark_emergency_time' => env('MODULAROUS_BENCHMARK_EMERGENCY_TIME', 1000), // in milliseconds

    'base_model' => Model::class,
    'base_repository' => Repository::class,
    'base_controller' => BaseController::class,
    'base_request' => Request::class,
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

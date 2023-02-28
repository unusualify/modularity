<?php

use Unusual\CRM\Base\Activators\FileActivator;
use Symfony\Component\Console\Input\InputOption;

return [
    'namespace' => env('BASE_NAMESPACE','Unusual\\CRM\\Base'),
    'name' => env('BASE_NAME','Base'),

    'version' => '1.0.0',

    'is_development' => env('UNUSUAL_DEV', false),
    'development_url' => env('UNUSUAL_DEV_URL', 'http://localhost:8080'),
    'public_dir' => env('UNUSUAL_ASSETS_DIR', 'unusual'),
    'vendor_path' => env('UNUSUAL_VENDOR_PATH', 'vendor/oobook/crm-base'),
    'custom_components_resource_path' => 'js/components',
    // 'vendor_components_resource_path' => 'assets/vendor/js/components',
    'manifest' => 'unusual-manifest.json',
    'js_namespace' => 'UNUSUAL',
    'build_timeout' => 300,

    'locale' => 'en',

    'fallback_locale' => 'en',

    'paths' => [
        'generator' => [
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'route-controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'route-controller-api' => ['path' => 'Http/Controllers/API', 'generate' => true],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            'view' => ['path' => 'Resources/views/$LOWER_NAME$', 'generate' => true],
        ]
    ],

    'route_patterns' => [
        'id' => '[0-9]+',
        'payment' => '[0-9]+'
    ],

    'stubs' => [
        'enabled' => false,
        'path' => base_path('Modules/Base/Console/stubs'),
        'files' => [
            'routes/web' => 'Routes/web.php',
            'routes/api' => 'Routes/api.php',
            'views/index' => 'Resources/views/$LOWER_NAME$/index.blade.php',
            'views/create' => 'Resources/views/$LOWER_NAME$/create.blade.php',
            'views/edit' => 'Resources/views/$LOWER_NAME$/edit.blade.php',
            // 'repository' => 'Repositories/$STUDLY_NAME$Repository.php',
            // 'route-controller' => 'Http/Controllers/$STUDLY_NAME$Controller.php',
            // 'route-controller-api' => 'Http/Controllers/API/$STUDLY_NAME$Controller.php',
        ],
        'replacements' => [
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api' => ['LOWER_NAME'],
            'views/index' => ['STUDLY_NAME'],
            'views/create' => ['STUDLY_NAME'],
            'views/edit' => ['STUDLY_NAME'],
            // 'route-controller' => ['NAMESPACE', 'MODULE', 'MODULE_NAMESPACE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
            // 'route-controller-api' => ['NAMESPACE', 'MODULE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
            // 'repository' => ['NAMESPACE', 'CLASS', 'MODULE','STUDLY_NAME', 'MODEL']
        ],
        'gitkeep' => true
    ],

    'schemas' => [
        'default_fields' => [
            "title:string('title'\,200):nullable",
            "description:text:nullable",
        ],
        'fillables' => [
            'published:boolean:default(false)'
        ],
        'translated_attributes' => [
            'active:boolean'
        ]
    ],

    'traits' => [
        'translationTrait' => [
            'model' => 'HasTranslation',
            'repository' => 'TranslationsTrait',
            'question' => 'Do you need to translate content on this route?',
            'command_option' => [
                'shortcut' => '--T',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Whether model has translation trait or not'
            ]
        ],
        'mediaTrait' => [
            'model' => 'HasMedias',
            'repository' => 'MediasTrait',
            'question' => 'Do you need to attach images on this module?',
            'command_option' => [
                'shortcut' => '--M',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to attach images on this module?'
            ]
        ],
        'fileTrait' => [
            'model' => 'HasFiles',
            'repository' => 'FilesTrait',
            'question' => 'Do you need to attach files on this module?',
            'command_option' => [
                'shortcut' => '--F',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to attach files on this module?'
            ]
        ],
        'positionTrait' => [
            'model' => 'HasPosition',
            'question' => 'Do you need to manage the position of records on this module?',
            'command_option' => [
                'shortcut' => '--P',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to manage the position of records on this module?'
            ],
            'implementations' => [
                \Unusual\CRM\Base\Entities\Interfaces\Sortable::class
            ]
        ],
    ],

    'base_model' => \Unusual\CRM\Base\Entities\Model::class,

    'base_repository' => \Unusual\CRM\Base\Repositories\Repository::class,

    'base_controller' => \Unusual\CRM\Base\Http\Controllers\BaseController::class,

    'base_request' => \Unusual\CRM\Base\Http\Requests\Request::class,

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

    'enabled' => [
        'media-library' => true,
        'users-management' => true,
        // 'file-library' => true,
        // 'block-editor' => true,
        // 'buckets' => true,
        // 'users-image' => false,
        // 'site-link' => false,
        // 'settings' => true,
        // 'dashboard' => true,
        // 'search' => true,
        // 'users-description' => false,
        // 'activitylog' => true,
        // 'users-2fa' => false,
        // 'users-oauth' => false,
    ]
];

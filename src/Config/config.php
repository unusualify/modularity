<?php

use Unusualify\Modularity\Activators\FileActivator;
use Symfony\Component\Console\Input\InputOption;

return [
    'namespace' => env('BASE_NAMESPACE','Unusualify\\Modularity'),
    'name' => env('UNUSUAL_BASE_NAME','Unusual'),

    'version' => '1.0.0',
    'auth_login_redirect_path' => '/',
    'is_development' => env('UNUSUAL_DEV', false),
    'development_url' => "http://" . env('UNUSUAL_DEV_URL', 'localhost:8080'),
    'public_dir' => env('UNUSUAL_ASSETS_DIR', 'unusual'),
    'vendor_path' => env('UNUSUAL_VENDOR_PATH', 'vendor/unusualify/modularity'),
    'custom_components_resource_path' => 'js/unusual',
    // 'vendor_components_resource_path' => 'assets/vendor/js/components',
    'manifest' => 'unusual-manifest.json',
    'js_namespace' => env('VUE_APP_NAME','UNUSUAL'),
    'build_timeout' => 300,
    'use_big_integers_on_migrations' => true,
    'base_prefix' => 'system',

    'users_table_name' => 'users',

    'locale' => 'en',
    'fallback_locale' => 'en',
    'timezone' => 'Europe/London',

    'default_input' => [
        'type' => 'text',
        'hint' => '',
        'placeholder' => '',
        'errorMessages' => [],
        'col' => [
            'cols' => 12,
            // 'sm' => 12,
            // 'md' => 8,
            // 'lg' => 6,
            // 'xl' => 6,
            // 'class' => 'pr-theme-semi pl-theme-semi pb-2 pt-2',
            'class' => 'pb-2 pt-2'
        ],
        'offset' => [
            'offset' => 0,
            'offset-sm' => 0,
            'offset-md' => 0,
            'offset-lg' => 0,
            'offset-xl' => 0,
        ],
        'order' => [
            // 'order' => 0,
            // 'order-sm' => 0,
            // 'order-md' => 0,
            // 'order-lg' => 0,
            // 'order-xl' => 0,
        ],
        // 'class' => 'mx-2 mb-1',
        'prependIcon' => '',
        'prependInnerIcon' => '',
        'appendIcon' => '',
        'appendInnerIcon' => '',

        'density' => 'comfortable', // default | comfortable | compact

        'variant' => 'outlined',
        // 'variant' => 'underlined',

        'clearable',

        // 'dense',
    ],

    'default_header' => [
        'align' => 'start',
        'sortable' => false,
        'filterable' => false,
        'groupable' => false,
        'divider' => false,
        'class' => 'text-primary', // || []
        'cellClass' => '', // || []
        'width' => 30, // || int

        'noPadding' => true,
        // vuetify datatable header fields end

        // custom fields for ue-datatable start
        'searchable' => false, //true,
        'isRowEditable' => false,
        'isColumnEditable' => false,
        'formatter' => [],
    ],

    'default_table_attributes' => [
        'embeddedForm' => true,
        'createOnModal' => true,
        'editOnModal' => true,
        'formWidth' => '60%',
        'isRowEditing' => false,
        'rowActionsType' => 'inline',
        'hideDefaultFooter' => false,
    ],

    'paths' => [
        'generator' => [
            'route-resource' => ['path' => 'Transformers', 'generate' => true],
            'model' => ['path' => 'Entities', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'route-controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'route-controller-api' => ['path' => 'Http/Controllers/API', 'generate' => true],
            'route-request' => ['path' => 'Http/Requests', 'generate' => true],
            // 'view' => ['path' => 'Resources/views/$SNAKE_NAME$', 'generate' => true],
        ]
    ],

    'route_patterns' => [
        'id' => '[0-9]+',
        'payment' => '[0-9]+'
    ],

    'stubs' => [
        'enabled' => false,
        'path' => base_path( env('UNUSUAL_STUB_PATH') ?? 'vendor/unusualify/modularity/src/Console/stubs'),
        'files' => [
            'routes/web' => 'Routes/web.php',
            'routes/api' => 'Routes/api.php',
            'views/index' => 'Resources/views/$SNAKE_CASE$/index.blade.php',
            'views/form' => 'Resources/views/$SNAKE_CASE$/form.blade.php',
            // 'repository' => 'Repositories/$STUDLY_NAME$Repository.php',
            // 'route-controller' => 'Http/Controllers/$STUDLY_NAME$Controller.php',
            // 'route-controller-api' => 'Http/Controllers/API/$STUDLY_NAME$Controller.php',
        ],
        'replacements' => [
            'routes/web' => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api' => ['LOWER_NAME'],
            'views/index' => ['STUDLY_NAME'],
            'views/form' => ['STUDLY_NAME'],
            // 'route-controller' => ['NAMESPACE', 'MODULE', 'MODULE_NAMESPACE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
            // 'route-controller-api' => ['NAMESPACE', 'MODULE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
            // 'repository' => ['NAMESPACE', 'CLASS', 'MODULE','STUDLY_NAME', 'MODEL']
        ],
        'gitkeep' => true
    ],

    'schemas' => [
        'default_fields' => [
            "name:string",
            // "description:text:nullable",
        ],
        'fillables' => [
            'published:boolean:default(false)'
        ],
        'translated_attributes' => [
            'active:boolean'
        ],
        'default_inputs' => [
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ]
        ],
        'default_pre_headers' => [
            [
                'title' => 'Name',
                'key' => 'name',
                'formatter' => ['edit'],
                'searchable' => true
            ],
        ],
        'default_post_headers' => [
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => ['date', 'long'],
                'searchable' => true
            ],
            // [
            //     'title' => 'Update Time',
            //     'key' => 'updated_at',
            //     'formatter' => ['date', 'long'],
            //     'searchable' => true
            // ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false
            ]
        ],
    ],

    'traits' => [
        'addTranslation' => [
            'model' => 'HasTranslation',
            'repository' => 'TranslationsTrait',
            'question' => 'Do you need to translate content on this route?',
            'command_option' => [
                'shortcut' => '--T',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Whether model has translation trait or not'
            ]
        ],
        'addMedia' => [
            'model' => 'HasMedias',
            'repository' => 'MediasTrait',
            'question' => 'Do you need to attach images on this module?',
            'command_option' => [
                'shortcut' => '--M',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to attach images on this module?'
            ]
        ],
        'addFile' => [
            'model' => 'HasFiles',
            'repository' => 'FilesTrait',
            'question' => 'Do you need to attach files on this module?',
            'command_option' => [
                'shortcut' => '--F',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to attach files on this module?'
            ]
        ],
        'addPosition' => [
            'model' => 'HasPosition',
            'question' => 'Do you need to manage the position of records on this module?',
            'command_option' => [
                'shortcut' => '--P',
                'input_type' => InputOption::VALUE_NONE,
                'description' => 'Do you need to manage the position of records on this module?'
            ],
            'implementations' => [
                \Unusualify\Modularity\Entities\Interfaces\Sortable::class
            ]
        ],
    ],

    'tables' => [
        'users' => 'users',
        'companies' => 'umod_companies',
        'files' => 'umod_files',
        'fileables' => 'umod_fileables',
        'medias' => 'umod_medias',
        'mediables' => 'umod_mediables',
        'tagged' => 'umod_tagged',
        'tags' => 'umod_tags',
        'repeaters' => 'umod_repeaters',
    ],

    'base_model' => \Unusualify\Modularity\Entities\Model::class,

    'base_repository' => \Unusualify\Modularity\Repositories\Repository::class,

    'base_controller' => \Unusualify\Modularity\Http\Controllers\BaseController::class,

    'base_request' => \Unusualify\Modularity\Http\Requests\Request::class,

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
        'file-library' => true,

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

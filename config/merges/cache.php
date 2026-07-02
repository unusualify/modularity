<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the modularous caching system is enabled.
    | When disabled, all cache operations will be bypassed and data will
    | be fetched directly from the database.
    |
    */
    'enabled' => env('MODULAROUS_RESOURCE_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Check Whether to Cache All Modules
    |--------------------------------------------------------------------------
    |
    | This option controls whether the modularous caching system is enabled for all modules on default behavior.
    | When enabled, the modularous caching system will be enabled for all modules on default behavior. You must
    | use modules key to disable/enable the caching system for specific modules.
    |
    */
    'all_modules' => env('MODULAROUS_RESOURCE_CACHE_ALL_MODULES', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Mode
    |--------------------------------------------------------------------------
    |
    | This option controls the mode of the modularous caching system.
    | The possible values are: local, development, production.
    | The mode will be used to determine if the modularous caching system is enabled.
    | If the mode is local, the modularous caching system to log the cache operations to the console.
    | If the mode is development, the modularous caching system to log the cache operations to the database.
    | If the mode is production, the modularous caching system to log the cache operations to the file.
    */
    'environment_variable' => env('MODULAROUS_RESOURCE_CACHE_MODE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | This option controls which cache driver should be used for storing
    | modularous cache data. By default, it uses Redis for optimal performance.
    |
    */
    'driver' => env('MODULAROUS_RESOURCE_CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be prepended to all cache keys to avoid conflicts
    | with other cached data in your application.
    |
    */
    'prefix' => env('MODULAROUS_RESOURCE_CACHE_PREFIX', 'modularous'),

    /*
    |--------------------------------------------------------------------------
    | TTL Settings (in seconds)
    |--------------------------------------------------------------------------
    |
    | Time-to-live settings for different types of cached data.
    | - counts: Filter count badges (all, published, trash, etc.)
    | - index: Repository paginated list data (raw Eloquent)
    | - record: Single record data
    | - formattedItem: Admin table row formatting (see CacheKeyGenerators)
    | - formItem: Admin edit form payload
    | - presentationItem: Public CMS inner body HTML (locale + record scoped)
    |
    */
    'ttl' => [
        'counts' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_COUNTS', 300),           // 5 minutes
        'index' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_INDEX', 600),             // 10 minutes
        'record' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_RECORD', 1800),          // 30 minutes
        'formattedItem' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_FORMATTED_ITEM', 1800),          // 30 minutes
        'formItem' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_FORM_ITEM', 1800),          // 30 minutes
        'presentationItem' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_PRESENTATION_ITEM', 900), // 15 minutes

        'response:json' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_RESPONSE', 300),  // 5 minutes (formatted JSON)
        'response:index' => (int) env('MODULAROUS_RESOURCE_CACHE_TTL_RESPONSE', 300), // 5 minutes (index page)
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags Support
    |--------------------------------------------------------------------------
    |
    | Enable cache tags for more granular cache invalidation. Note that
    | cache tags are only supported by certain cache drivers (Redis, Memcached).
    |
    */
    'use_tags' => env('MODULAROUS_RESOURCE_CACHE_USE_TAGS', true),

    /*
    |--------------------------------------------------------------------------
    | User-Aware Caching
    |--------------------------------------------------------------------------
    |
    | When enabled, cache keys for index/list queries and count queries will
    | include the authenticated user's ID. This is essential when repository
    | traits like CreatorTrait or AssignmentTrait add user-dependent scopes.
    |
    | Example scopes that require user-aware caching:
    | - hasAccessToCreation (CreatorTrait)
    | - everAssignedToYourRoleOrHasAuthorization (AssignmentTrait)
    |
    | Cache key format with user-aware caching:
    | {prefix}:{module}:{user_id}:{type}:{params_hash}
    |
    | Without user-aware caching:
    | {prefix}:{module}:{type}:{params_hash}
    |
    | Disable this only if all queries are truly public and user-independent.
    |
    */
    'user_aware' => env('MODULAROUS_RESOURCE_CACHE_USER_AWARE', true),

    /*
    |--------------------------------------------------------------------------
    | Relationship Graph Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | The caching system automatically builds a relationship graph from all
    | module models that have getEloquentRelationships() method. This graph
    | is used to automatically invalidate dependent caches when related
    | models are updated.
    |
    | How it works:
    | 1. On first cache operation, the graph is built by scanning all modules
    | 2. For each module's main entity, relationships are extracted
    | 3. When any model is updated, the graph is consulted to find which
    |    modules display that model's data and need cache invalidation
    |
    | Graph supports:
    | - HasMany, BelongsTo, HasOne, MorphMany, MorphTo
    | - BelongsToMany (with pivot tables)
    | - HasOneThrough, HasManyThrough (with middleman tables)
    |
    | Commands:
    | - php artisan modularous:cache:graph show    -- Display the graph
    | - php artisan modularous:cache:graph rebuild -- Rebuild the graph
    | - php artisan modularous:cache:graph stats   -- Show statistics
    |
    */
    'graph' => [
        'enabled' => env('MODULAROUS_RESOURCE_CACHE_GRAPH_ENABLED', true),
        'ttl' => (int) env('MODULAROUS_RESOURCE_CACHE_GRAPH_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Module Cache Settings
    |--------------------------------------------------------------------------
    |
    | Override default cache settings for specific modules. Each module can
    | have its own enabled flag and TTL settings.
    | Module names should be in StudlyCase (PascalCase).
    |
    | Example:
    | 'modules' => [
    |     'Package' => [
    |         'enabled' => true,
    |         'ttl' => [
    |             'counts' => 60,  // 1 minute for frequently changing data
    |         ],
    |         'routes' => [
    |             'Package' => [
    |                 'enabled' => true,
    |                 'ttl' => [
    |                     'counts' => 60,  // 1 minute for frequently changing data
    |                 ],
    |                 'types' => [
    |                     'counts' => true,
    |                     'index' => true,
    |                     'record' => true,
    |                     'formattedItem' => true,
    |                     'formItem' => true,
    |                 ],
    |                 'rewarmItem' => false,
    |             ],
    |             'PackageContinent' => [
    |                 'enabled' => true,
    |                 'ttl' => [
    |                     'counts' => 900,  // 15 minutes for rarely changing data
    |                 ],
    |             ],
    |         ],
    |     ],
    |     'Faq' => [
    |         'enabled' => true,
    |         'ttl' => [
    |             'counts' => 900,  // 15 minutes for rarely changing data
    |         ],
    |     ],
    | ],
    |
    */
    'modules' => [],

    /*
    |--------------------------------------------------------------------------
    | Cache Dependencies (Manual Override)
    |--------------------------------------------------------------------------
    |
    | In addition to automatic graph discovery, you can define explicit
    | dependencies here. These are merged with graph-discovered dependencies.
    |
    | Use this when:
    | - You need to define dependencies for vendor/external models
    | - The automatic discovery misses a relationship
    | - You want to add additional invalidation paths
    |
    | Format: Use full class names as keys, module names in StudlyCase.
    |
    | Example: PressRelease displays Company name via creator.company relationship.
    | When Company is updated, PressRelease cache should be invalidated.
    |
    | 'dependencies' => [
    |     'Modules\Company\Entities\Company' => [
    |         [
    |            'moduleName' => 'PressRelease', // Module name
    |            'moduleRouteName' => 'PressReleasePayment', // Module Route name
    |            'types' => [
    |                'counts' => false,
    |                'index' => false,
    |                'record' => true,
    |                'formattedItem' => true,
    |                'formItem' => true,
    |            ],
    |            'targetRelationshipName' => 'pressReleasePayments', // Target relationship name
    |            'isSelf' => false, // Whether the target relationship is self
    |            'selfModelClass' => null, // Self model class name
    |        ],
    |     ],
    ],
    */
    'dependencies' => [],

    /*
    |--------------------------------------------------------------------------
    | Cache Operation Logging
    |--------------------------------------------------------------------------
    |
    | Structured logs for invalidation and warmup (including dependent-triggered
    | presentationItem warmup). Writes to the channel below when it exists.
    |
    | - enabled: true|false forces on/off; null (default) logs only when the
    |   channel is registered in config/logging.php or by Modularous.
    | - channel: Laravel log channel name (daily file recommended).
    |
    | Register in your app config/logging.php, or rely on the built-in channel:
    |
    | 'modularous-resource-cache' => [
    |     'driver' => 'daily',
    |     'path' => storage_path('logs/modularous-resource-cache.log'),
    |     'level' => env('LOG_LEVEL', 'info'),
    |     'days' => 10,
    | ],
    |
    | Artisan: modularous:cache:warm ... --logChannel=modularous-resource-cache
    |
    */
    'logging' => [
        'enabled' => env('MODULAROUS_RESOURCE_CACHE_LOGGING_ENABLED', null),
        'channel' => env('MODULAROUS_RESOURCE_CACHE_LOG_CHANNEL', 'modularous-resource-cache'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Observer Queue Settings
    |--------------------------------------------------------------------------
    |
    | When queue is true and QUEUE_CONNECTION is not sync, CacheObserver dispatches
    | invalidation/warmup jobs instead of blocking the admin save request.
    |
    */
    'observer' => [
        'queue' => env('MODULAROUS_CACHE_OBSERVER_QUEUE', true),
        'queue_connection' => env('MODULAROUS_CACHE_QUEUE_CONNECTION', null),
        'queue_name' => env('MODULAROUS_CACHE_QUEUE_NAME', 'modularous-cache'),
        'auto_invalidate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Manual Purge (Global Default)
    |--------------------------------------------------------------------------
    |
    | When true, the observer skips automatic invalidate/warm for routes that inherit
    | this default. Per-route manual_purge and purge[type] overrides apply.
    |
    */
    'manual_purge' => false,

    /*
    |--------------------------------------------------------------------------
    | Dedicated Cache Queue (alias for observer queue settings)
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('MODULAROUS_CACHE_QUEUE_CONNECTION', null),
        'name' => env('MODULAROUS_CACHE_QUEUE_NAME', 'modularous-cache'),
    ],
];

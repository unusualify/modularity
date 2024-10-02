<?php

return [
    'enabled' => false,
    'path' => base_path(env('MODULARITY_STUB_PATH') ?? 'vendor/unusualify/modularity/src/Console/stubs'),
    'files' => [
        'routes/web' => 'Routes/web.php',
        'routes/api' => 'Routes/api.php',
        'routes/front' => 'Routes/front.php',
        'views/index' => 'Resources/views/$SNAKE_CASE$/index.blade.php',
        'views/form' => 'Resources/views/$SNAKE_CASE$/form.blade.php',
        // 'repository' => 'Repositories/$STUDLY_NAME$Repository.php',
        // 'route-controller' => 'Http/Controllers/$STUDLY_NAME$Controller.php',
        // 'route-controller-api' => 'Http/Controllers/API/$STUDLY_NAME$Controller.php',
        'composer' => 'composer.json',
    ],
    'replacements' => [
        'routes/web' => ['LOWER_NAME', 'STUDLY_NAME'],
        'routes/api' => ['LOWER_NAME'],
        'routes/front' => ['LOWER_NAME'],
        'views/index' => ['STUDLY_NAME'],
        'views/form' => ['STUDLY_NAME'],
        // 'route-controller' => ['NAMESPACE', 'MODULE', 'MODULE_NAMESPACE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
        // 'route-controller-api' => ['NAMESPACE', 'MODULE', 'CLASS', 'STUDLY_NAME', 'LOWER_NAME'],
        // 'repository' => ['NAMESPACE', 'CLASS', 'MODULE','STUDLY_NAME', 'MODEL'],
        'composer' => [
            'KEBAB_MODULE_NAME',
            'STUDLY_MODULE_NAME',
            'LOWER_NAME',
            'STUDLY_NAME',
            'VENDOR',
            'AUTHOR',
            'AUTHOR_EMAIL',
            'MODULE_NAMESPACE',
            'PROVIDER_NAMESPACE',
        ],
    ],
    'gitkeep' => true,
];

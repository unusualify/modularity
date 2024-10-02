<?php

return [
    'generator' => [
        'route-resource' => [
            'path' => 'Transformers',
            'generate' => true,
        ],
        'model' => [
            'path' => 'Entities',
            'generate' => true,
        ],
        'repository' => [
            'path' => 'Repositories',
            'generate' => true,
        ],
        'route-controller' => [
            'path' => 'Http/Controllers',
            'generate' => true,
        ],
        'route-controller-api' => [
            'path' => 'Http/Controllers/API',
            'generate' => true,
        ],
        'route-controller-front' => [
            'path' => 'Http/Controllers/Front',
            'generate' => true,
        ],
        'route-request' => [
            'path' => 'Http/Requests',
            'generate' => true,
        ],
        // 'view' => ['path' => 'Resources/views/$SNAKE_NAME$', 'generate' => true],

    ],
];

<?php

use Symfony\Component\Console\Input\InputOption;

return [
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
        'model' => 'HasImages',
        'repository' => 'ImagesTrait',
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
    'addSlug' => [
        'model' => 'HasSlug',
        'repository' => 'SlugsTrait',
        'question' => 'Do you need the slugs on this route?',
        'command_option' => [
            'shortcut' => '--S',
            'input_type' => InputOption::VALUE_NONE,
            'description' => 'Whether model has sluggable trait or not'
        ]
    ],
    'addPrice' => [
        'model' => 'HasPriceable',
        'repository' => 'PricesTrait',
        'question' => 'Do you need to add pricing feature on this route?',
        'command_option' => [
            'shortcut' => null,
            'input_type' => InputOption::VALUE_NONE,
            'description' => 'Whether model has pricing trait or not'
        ]
    ],
    'addAuthorized' => [
        'model' => 'IsAuthorizedable',
        'repository' => 'AuthorizedTrait',
        'question' => 'Do you need to add authorized feature on this module?',
        'command_option' => [
            'shortcut' => '--A',
            'input_type' => InputOption::VALUE_NONE,
            'description' => 'Authorized models to indicate scopes'
        ]
    ],
];


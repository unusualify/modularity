<?php

return [
    /**
     * Named presets for `php artisan down`.
     * Option keys map to Artisan option names (without leading --).
     * Empty / null option values are omitted when running.
     *
     * System Settings → General → `down_presets` (render/retry/refresh/secret)
     * overrides the `default` preset options when non-empty; otherwise these
     * config values (including MODULAROUS_MAINTENANCE_SECRET) are used.
     *
     * Example equivalent:
     * php artisan down --render='modularous::maintenance' --retry=60 --refresh=30 --secret="…"
     *
     * @var array<string, array{label: string, options: array<string, mixed>}>
     */
    'down_presets' => [
        'default' => [
            'label' => 'Default maintenance',
            'options' => [
                'render' => 'modularous::maintenance',
                'retry' => 60,
                'refresh' => 30,
                'secret' => env('MODULAROUS_MAINTENANCE_SECRET'),
            ],
        ],
    ],

    'default_down_preset' => 'default',

    /**
     * Granular cache / optimize actions shown in SystemConsoleWidget.
     * Commands must be runnable via ArtisanRunner (superadmin or allowlist).
     *
     * @var list<array{
     *     command: string,
     *     label: string,
     *     confirm?: bool,
     *     color?: string,
     *     variant?: string,
     *     icon?: string
     * }>
     */
    'cache_commands' => [
        'route:clear' => [
            'command' => 'route:clear',
            'label' => 'Route Clear',
            'confirm' => false,
            'color' => 'warning',
            'icon' => 'mdi-routes',
        ],
        'route:cache' => [
            'command' => 'route:cache',
            'label' => 'Route Cache',
            'confirm' => true,
            'color' => 'primary',
            'icon' => 'mdi-routes-clock',
        ],
        'optimize:clear' => [
            'command' => 'optimize:clear',
            'label' => 'Optimize Clear',
            'confirm' => true,
            'color' => 'warning',
            'icon' => 'mdi-cached',
        ],
        'optimize' => [
            'command' => 'optimize',
            'label' => 'Optimize',
            'confirm' => true,
            'color' => 'primary',
            'icon' => 'mdi-rocket-launch',
        ],
    ],
];

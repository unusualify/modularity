<?php

return [
    'enabled' => env('MODULAROUS_ARTISAN_RUNNER_ENABLED', false),

    /**
     * Who can open the ArtisanRunner panel (page + API).
     *
     * @var list<string>
     */
    'allowed_roles' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('MODULAROUS_ARTISAN_RUNNER_ALLOWED_ROLES', 'superadmin'))
    ))),

    /**
     * Command allowlist for non-superadmin users in allowed_roles.
     * Superadmin always bypasses this and sees/runs all Artisan commands.
     * Supports exact names and globs (e.g. modularous:cache:*).
     *
     * SystemConsoleWidget uses down, up, route:clear, route:cache, optimize,
     * and optimize:clear via the same runner. If you grant admin (non-superadmin)
     * access to that widget, include those command names in this allowlist.
     *
     * @var list<string>
     */
    'allowlist' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('MODULAROUS_ARTISAN_RUNNER_ALLOWLIST', ''))
    ))),

    /**
     * How ArtisanRunner executes commands:
     * - auto: in-process Kernel::handle, except subprocess_commands (CLI parity)
     * - in_process: always Kernel::handle (interactive prompts work)
     * - subprocess: always spawn `php artisan …` (no BridgedQuestionHelper)
     */
    'execution' => env('MODULAROUS_ARTISAN_RUNNER_EXECUTION', 'auto'),

    /**
     * Commands that must run as a real CLI subprocess when execution=auto.
     *
     * route:cache (and optimize) bootstrap a fresh app to serialize routes. Under
     * PHP-FPM, runningInConsole() is false and request()/isPanelUrl() still reflect
     * the panel host, so Modularous skips CMS public catch-alls and may omit module
     * routes — producing an incomplete route cache vs terminal `php artisan`.
     *
     * Supports exact names and globs (via AllowlistMatcher).
     *
     * @var list<string>
     */
    'subprocess_commands' => array_values(array_filter(array_map(
        'trim',
        explode(',', env(
            'MODULAROUS_ARTISAN_RUNNER_SUBPROCESS_COMMANDS',
            'route:cache,route:clear,config:cache,config:clear,event:cache,event:clear,view:cache,view:clear,optimize,optimize:clear'
        ))
    ))),

    'timeout' => (int) env('MODULAROUS_ARTISAN_RUNNER_TIMEOUT', 120),

    'prompt_timeout' => (int) env('MODULAROUS_ARTISAN_RUNNER_PROMPT_TIMEOUT', 60),

    'max_output_bytes' => (int) env('MODULAROUS_ARTISAN_RUNNER_MAX_OUTPUT_BYTES', 1_048_576),
];

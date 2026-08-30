---
sidebarPos: 2
sidebarTitle: System Console
outline: deep
---

# System Console configuration

System Console is a dashboard widget (`ue-system-console`) for one-click Artisan actions: maintenance mode, cache/optimize, and optional custom commands.

Command lists come from **`modularous.system_console`** (`config/merges/system_console.php`). The dashboard block only places the card — see [Dashboard widgets](./overview). Execution goes through **Artisan Runner**.

## Runtime payload

`SystemConsoleWidget::hydrateAttributes()` fills Vue props from config (not from `ui_settings` attributes):

| Prop | Source |
|------|--------|
| `downPresets` / `defaultDownPreset` | `system_console.down_presets` / `default_down_preset` |
| `cacheCommands` | `system_console.cache_commands` |
| `customCommands` | `system_console.custom_commands` |
| `maintenanceMode` | `app()->isDownForMaintenance()` |
| `endpoints` / `runnerDisabled` | Artisan Runner access |

::: warning Keep command arrays off the widget block
Do not put `cacheCommands`, `customCommands`, or `downPresets` on `ui_settings.dashboard.blocks.system-console.attributes`. Recursive merge duplicates those lists in the UI.
:::

## Package defaults (`config/merges/system_console.php`)

Loaded as `config('modularous.system_console')`. Override in the host `config/modularous.php` (or an equivalent merge), not on the dashboard block.

```php
return [
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
    'cache_commands' => [ /* route:clear, route:cache, optimize:clear, optimize */ ],
    'custom_commands' => [ /* empty — row hidden until you add entries */ ],
];
```

### `down_presets`

Named `php artisan down` option sets. Keys are preset ids. Option names match Artisan flags without `--`. Empty / `null` values are omitted.

| Field | Purpose |
|-------|---------|
| `label` | Select label in the widget |
| `options.render` | Blade view for the maintenance page |
| `options.retry` | `Retry-After` seconds |
| `options.refresh` | Meta refresh seconds |
| `options.secret` | Bypass token (`MODULAROUS_MAINTENANCE_SECRET`) |

System Settings → General → `down_presets` overrides the **default** preset when those values are non-empty.

`Bring application up` runs `up` (no extra options).

### `cache_commands`

First button row (**Cache & optimize**). Deduped by **command name**.

| Field | Required | Purpose |
|-------|----------|---------|
| `command` | yes | Artisan command (`route:clear`) |
| `label` | no | Button text (defaults to `command`) |
| `tooltip` | no | Hover text; the command name is always shown in the tooltip |
| `confirm` | no | Confirm dialog before run (`false`) |
| `color` / `variant` / `icon` | no | Vuetify button props (`variant` defaults to `tonal`) |

### `custom_commands`

Second button row, independent of cache. Deduped by **array key**, so the same artisan command may appear twice with different arguments. An empty list hides the row.

| Field | Required | Purpose |
|-------|----------|---------|
| `command` | yes | Artisan command |
| `label` | no | Button text |
| `tooltip` | no | Description above the command name on hover |
| `confirm` | no | Confirm dialog (`false`) |
| `color` / `variant` / `icon` | no | Vuetify button props |
| `arguments` | no | Named artisan arguments (`runDirect` args) |
| `options` | no | Named artisan options (flags without `--`) |

```php
'custom_commands' => [
    'queue-restart' => [
        'command' => 'queue:restart',
        'label' => 'Restart queues',
        'tooltip' => 'Signals all queue workers to restart after the current job.',
        'confirm' => true,
        'color' => 'secondary',
        'icon' => 'mdi-restart',
    ],
    'inspire' => [
        'command' => 'inspire',
        'label' => 'Inspire',
        'tooltip' => 'Print a random quote.',
    ],
],
```

Host override:

```php
// config/modularous.php
'system_console' => [
    'custom_commands' => [
        'queue-restart' => [
            'command' => 'queue:restart',
            'label' => 'Restart queues',
            'tooltip' => 'Signals workers to restart.',
            'confirm' => true,
        ],
    ],
],
```

## Artisan Runner

System Console does not spawn Artisan itself. It posts to the Artisan Runner run endpoint (`useArtisanRunner` / `runDirect`).

| Key | Env | Default | Notes |
|-----|-----|---------|-------|
| `artisan_runner.enabled` | `MODULAROUS_ARTISAN_RUNNER_ENABLED` | `false` | Master switch; when off the widget shows a disabled hint |
| `artisan_runner.allowed_roles` | `MODULAROUS_ARTISAN_RUNNER_ALLOWED_ROLES` | `superadmin` | Who may open the runner API |
| `artisan_runner.allowlist` | `MODULAROUS_ARTISAN_RUNNER_ALLOWLIST` | empty | Non-superadmin command names / globs. Superadmin bypasses this |
| `artisan_runner.subprocess_commands` | `MODULAROUS_ARTISAN_RUNNER_SUBPROCESS_COMMANDS` | `route:cache`, `optimize`, … | CLI subprocess for cache parity under PHP-FPM |
| `artisan_runner.execution` | `MODULAROUS_ARTISAN_RUNNER_EXECUTION` | `auto` | `auto` / `in_process` / `subprocess` |

Commands must exist in the Artisan catalog. Custom commands that need a real CLI bootstrap should be added to `subprocess_commands`. If you grant a non-superadmin role access to the widget, include every `cache_commands` and `custom_commands` name in `allowlist`.

The dashboard block `allowedRoles` (`superadmin` by default) is separate: it hides the card. Artisan Runner `enabled` / `allowed_roles` gates actually running commands.

## Dashboard block

Package registration (`config/defers/ui_settings.php`):

```php
'system-console' => [
    'widget' => 'SystemConsoleWidget',
    'widgetCol' => ['cols' => 12, 'lg' => 6],
    'allowedRoles' => ['superadmin'],
    'attributes' => [
        'title' => 'System Console',
        'subtitle' => 'Maintenance mode and cache / optimize commands.',
        'elevation' => 2,
    ],
],
```

Title, subtitle, elevation, and column width belong here. Command lists belong in `system_console`.

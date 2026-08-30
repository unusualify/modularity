---
sidebarPos: 6
sidebarTitle: Overview
sidebarGroupTitle: Dashboard
outline: deep
---

# Dashboard widgets

The admin dashboard is a grid of **blocks** from `modularous.ui_settings.dashboard.blocks`. `DashboardController` keeps blocks the current user may see (`allowedRoles`) and renders each one through `Component::create()`.

Package defaults live in `config/defers/ui_settings.php`. Host apps override via `modularous/ui_settings.php` (deferred config) or published `config/modularous.php`.

Internals: [DashboardController](/system-reference/backend/http/controllers/dashboard-controller). Role filtering: [Allowable](/guide/generics/allowable).

## Block schema

Each block is an associative array. The `widget` key is the usual path for dashboard cards:

```php
'system-console' => [
    'widget' => 'SystemConsoleWidget',
    'widgetCol' => [
        'cols' => 12,
        'lg' => 6,
    ],
    'widgetAttributes' => [
        'class' => 'h-50',
        'style' => 'min-height: 160px',
    ],
    'allowedRoles' => ['superadmin'],
    'attributes' => [
        'class' => 'h-100',
        'title' => 'System Console',
        'subtitle' => 'Maintenance mode and cache / optimize commands.',
        'elevation' => 2,
    ],
],
```

| Key | Required | Purpose |
|-----|----------|---------|
| `widget` | one of `widget` / `component` / `tag` | Class under `Unusualify\Modularous\View\Widgets\{Name}` (no namespace prefix) |
| `widgetCol` | no | Vuetify `v-col` breakpoints (`cols`, `lg`, …) merged onto the wrapper |
| `widgetAttributes` | no | Extra attributes on the wrapper column (height, overflow, style) |
| `widgetSlots` | no | Slots on the wrapper column |
| `attributes` | no | Props for the inner Vue component (`title`, `subtitle`, `elevation`, `class`) |
| `allowedRoles` | no | Roles that may see the block. Omit for every authenticated dashboard user |
| `widgetAlias` | no | Optional alias into `modularous.widgets.{alias}` templates |

`Component::create()` also accepts `component` (Vue tag via `setComponent`) or `tag` (raw tag) instead of `widget`. Dashboard defaults ship as widgets.

## Wrapper vs inner component

A widget renders as a column wrapping the Vue card:

```
v-col  ← widgetTag + widgetCol + widgetAttributes
  └── ue-system-console  ← tag + attributes (hydrated at runtime)
```

Layout and height belong on `widgetCol` / `widgetAttributes`. Card copy belongs on `attributes`.

::: warning Do not put live command lists on the block
Widgets such as [System Console](./system-console) hydrate `cacheCommands`, `customCommands`, and `downPresets` in `hydrateAttributes()` from `modularous.system_console`. Putting those arrays on the block `attributes` (or `modularous.widgets.{alias}.attributes`) recursive-merges them and **duplicates** the buttons.
:::

## Package defaults

`config/defers/ui_settings.php` registers two superadmin blocks side by side:

| Block key | Widget | Vue tag |
|-----------|--------|---------|
| `system-console` | `SystemConsoleWidget` | `ue-system-console` |
| `artisan-runner` | `ArtisanRunnerWidget` | `ue-artisan-runner` |

Both require Artisan Runner access (`modularous.artisan_runner.enabled` and `allowed_roles`). System Console command lists are configured separately — see [System Console](./system-console).

## Host overrides

Copy or publish `ui_settings` and change `dashboard.blocks`:

```php
// modularous/ui_settings.php
return [
    'dashboard' => [
        'blocks' => [
            'system-console' => [
                'widget' => 'SystemConsoleWidget',
                'widgetCol' => ['cols' => 12, 'lg' => 12],
                'allowedRoles' => ['superadmin'],
                'attributes' => [
                    'title' => 'System Console',
                    'subtitle' => 'Maintenance and cache tools.',
                ],
            ],
            // omit artisan-runner to hide it
        ],
    ],
];
```

Deferred files replace the package `ui_settings` tree for keys you set. Keep unrelated keys (`sidebar`, `topbar`, …) if you publish the full file.

## Adding a widget

1. Create `src/View/Widgets/{Name}Widget.php` extending `ModularousWidget`.
2. Set `$tag` to the Vue component (`ue-…`).
3. Hydrate runtime props in `hydrateAttributes()` — do not bake lists into `ui_settings` attributes.
4. Register a block under `ui_settings.dashboard.blocks`.
5. Restrict with `allowedRoles` when the widget is privileged.

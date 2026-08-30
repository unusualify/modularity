---
sidebarPos: 9
sidebarTitle: DashboardController
---

# DashboardController

**File**: `src/Http/Controllers/DashboardController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `BaseController`  
**Traits**: `ManageUtilities`, `Allowable`

Admin dashboard controller. Renders a grid of configurable block items, filtered by the authenticated user's roles. Supports both Blade and Inertia rendering.

## Properties

| Property | Value | Description |
|----------|-------|-------------|
| `$moduleName` | `'Dashboard'` | Fixed module name |
| `$routeName` | `'Dashboard'` | Fixed route name |

## Constructor

```php
public function __construct(Application $app, Request $request)
```

Removes the default `view` permission middleware (dashboard is accessible to all authenticated users) and replaces it with a `can:dashboard` gate check.

## Methods

### `index($parentId = null): View|Response`

Renders the dashboard. Collects block items from `modularous.ui_settings.dashboard.blocks`, filters them by `allowedRoles` for the current user, renders each block's component, then passes the result to the view.

**Passed to view**:

| Variable | Description |
|----------|-------------|
| `blockItems` | Filtered and rendered dashboard blocks |
| `endpoints` | API endpoint map for dashboard components |
| `config` | Dashboard configuration object |

When Inertia is active, delegates to `renderInertiaDashboard()`.

### `renderInertiaDashboard(array $data): Response`

Renders the dashboard using Inertia with shared store variables and metadata required by Vue dashboard components.

## Block Configuration

Blocks are defined in deferred `ui_settings` under `dashboard.blocks` (`config/defers/ui_settings.php`, host override `modularous/ui_settings.php`).

User-facing schema, widget keys, and System Console command config: [Dashboard widgets](/guide/dashboard/overview) and [System Console](/guide/dashboard/system-console).

Each block's `allowedRoles` is checked against the current user's roles via the `Allowable` trait. Blocks with no `allowedRoles` key are visible to all users.

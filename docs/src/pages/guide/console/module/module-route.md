---
sidebarPos: 7
sidebarTitle: ModuleRoute
sidebarGroupTitle: Module
---

# ModuleRoute

First-class object for a single route inside a Modularous `Module`. Aggregates:

| Source | API |
|--------|-----|
| `config.php` → `routes.*` | `config()`, `rawConfig()`, `inConfig()` |
| Status store (filesystem / database) | `isEnabled()`, `inStatuses()`, `enable()`, `disable()` |
| Blueprint / presentation (config / class / database) | `inputs()`, `headers()`, `tableOptions()`, `presentationDriver()` |
| Model / repository traits | `features()`, `hasFeature()` |
| Classes / schema | `modelClass()`, `repositoryClass()`, `controllerClass()`, `hasTable()` |

Inspection **findings** stay in [`ModuleRouteInspector`](/guide/console/module/route-inspect) — `ModuleRoute` is the data surface, not the doctor.

Blueprint drivers (full index/form map including filters/actions): see [ModuleRoute Blueprint](./module-route-blueprint).

## Resolve routes

```php
use Unusualify\Modularous\Facades\Modularous;

$module = Modularous::findOrFail('Cms');

$routes = $module->routes();           // Collection<string, ModuleRoute>
$page   = $module->route('Page');      // Studly or snake
$live   = $module->enabledRoutes();
```

Canonical name is **Studly** (`Page`, `StyleSheet`). Registry union = status keys ∪ config route names (same as inspect).

## Examples

```php
$route = $module->route('style_sheet');

$route->name();          // StyleSheet
$route->snakeName();     // style_sheet
$route->isEnabled();
$route->isParent();
$route->hasFeature('revisions');
$route->modelClass();
$route->hasTable();

$route->disable();
$route->enable();
```

## Module facades (Phase 4)

Legacy `Module` APIs remain available. **Hot-path** facades (`isSingleton`, `hasRoute`, `getRouteNames`, filesystem `isEnabledRoute`) must stay registry-free — see [ADR — ModuleRoute Hot Path](/system-reference/adr-module-route-hot-path).

| Module (hot path) | Implementation note |
|-------------------|---------------------|
| `isEnabledRoute` / `hasRoute` / `getRouteNames` | Activator statuses + memo — not `ModuleRouteRegistry` |
| `isParentRoute` / `isSingleton` | Parent config / class-string + memo |
| `routeHasTable` | Prefer rare; `Schema::hasTable` is expensive |
| `hasRemoteApiSource` / `isResourceCacheEnabled` | Class-string trait checks |

When you already hold a `ModuleRoute`, prefer `$route->isSingleton()` / `$route->isEnabled()` (same cheap impl where shared).

URL helpers (`getRouteUrls` / `getRoutePanelUrls` / `getRouteActionUrl`) stay on `Module` so nested names like `item.nested.comment` keep working; `ModuleRoute::urls()` / `panelUrls()` / `actionUrl()` call those Module methods.

`enableRoute` / `disableRoute` stay on `Module` (events + status store); `ModuleRoute::enable()` / `disable()` call them.

Prefer new domain code: `$module->moduleRoute('Page')->…`, without violating the hot-path ADR.

`sidebarRoutes()` returns ModuleRoute instances for **config-listed** routes only (not full status∪config union). Prefer it for inspect / explicit domain use — **not** for `ModularousNavigation` (that must stay on `getRawRouteConfigs` + `Module::isSingleton`; see hot-path ADR).

## Related

- [ModuleRoute Blueprint](./module-route-blueprint)
- [ADR — ModuleRoute Blueprint](/system-reference/adr-module-route-blueprint)
- [ADR — ModuleRoute Hot Path](/system-reference/adr-module-route-hot-path)
- [Route Inspect](./route-inspect)

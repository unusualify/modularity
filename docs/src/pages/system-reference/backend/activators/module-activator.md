---
sidebarPos: 3
sidebarTitle: ModuleActivator
---

# ModuleActivator

**File**: `src/Activators/ModuleActivator.php`  
**Namespace**: `Unusualify\Modularous\Activators`  
**Extends**: `Nwidart\Modules\Activators\FileActivator`

`ModuleActivator` is the route-level status manager used by Modularous to enable or disable specific route actions (for example `create`, `edit`, `destroy`) inside a module.

## Constructor Inputs

| Parameter | Purpose |
|-----------|---------|
| `Container $app` | Resolves cache/files/config services |
| `string $cacheKey` | Cache key used for route status map |
| `string $statusesFile` | Per-module JSON file path (`routes_statuses.json`) |

The class uses a fixed cache lifetime of `604800` seconds (7 days).

## Core Responsibilities

| Method | Purpose |
|--------|---------|
| `enable($route)` / `disable($route)` | Toggle one route status |
| `setActiveByName(string $name, bool $status)` | Persist one route status |
| `hasStatus($route, bool $status)` | Check route status with default-false semantics |
| `readJson()` | Resolve statuses from cache or JSON |
| `delete($route)` | Remove explicit status record for one route |
| `reset()` | Delete statuses file, clear map and cache |
| `getRoutes()` | Return all stored route keys |

## Storage Format

The route statuses file stores an object keyed by route/action key:

```json
{
  "index": true,
  "create": false,
  "destroy": true
}
```

## Notes

- If `modules.cache.enabled` is `false`, statuses are read directly from file.
- Every write operation updates JSON and flushes cache.
- Unset routes are treated as `false` in `hasStatus(...)`.

## Related

- [ModularousActivator](./modularous-activator) — module-level status manager
- [Module System](/system-reference/modules)
- [Commands · route:enable](/guide/console/module/route-enable)
- [Commands · route:disable](/guide/console/module/route-disable)

---
sidebarPos: 11
sidebarTitle: Overview
sidebarGroupTitle: Module
---

# Module Commands

Manage the lifecycle of a module and the routes inside it. These commands correspond to the PHP classes under `src/Console/Module/` and cover creation fix-ups, removal, and per-route enable/disable controls.

| Command | Signature | Description |
|---------|-----------|-------------|
| [fix-module](./fix-module) | `modularous:fix:module` | Patch a module's config file after scaffolding changes (add translation, media, file, position, slug, price, authorized, filepond, uuid, snapshot) |
| [remove-module](./remove-module) | `modularous:remove:module` | Completely remove a module — roll back its migrations and delete its files |
| [route:enable](./route-enable) | `modularous:route:enable` | Re-enable a previously disabled route within a module |
| [route:disable](./route-disable) | `modularous:route:disable` | Disable a single route without removing the module |
| [route:status](./route-status) | `modularous:route:status` | List route enable/disable status per module |
| [route:inspect](./route-inspect) | `modularous:route:inspect` | Inspect route features (translation, CMR, revisions, …) and consistency findings |

## Common Workflows

### Toggle a route off without losing its data

```bash
php artisan modularous:route:disable Blog posts
php artisan modularous:route:status
```

Use [route:disable](./route-disable) to deactivate a route; its records and migrations stay intact. Re-enable with [route:enable](./route-enable) when ready.

### Fix config drift after scaffolding

When a generator adds a new feature (translation, media, file, etc.) but the module config hasn't been updated, run:

```bash
php artisan modularous:fix:module Blog posts --addTranslation --addMedia
```

See [fix-module](./fix-module) for the full option list.

### Completely remove a module

```bash
php artisan modularous:remove:module Blog
```

::: danger Destructive
[remove-module](./remove-module) rolls back migrations and deletes the module directory. There is no undo.
:::

## Related

- [ModuleRoute](./module-route) — first-class route object (config + status + features)
- [ModuleRoute Blueprint](./module-route-blueprint) — index/form UI drivers (config|class|database)
- [ADR — ModuleRoute Blueprint](/system-reference/adr-module-route-blueprint) — locked field map and class tree
- [Generators](../generators/overview) — scaffold modules, models, routes, and traits
- [Database commands](../migration/overview) — migrate / rollback used by `remove-module`
- [System Reference → Modules](/system-reference/modules) — module system internals

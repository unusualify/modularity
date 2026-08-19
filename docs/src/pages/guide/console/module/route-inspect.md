---
sidebarPos: 6
sidebarTitle: Route Inspect
---

# Route Inspect (Doctor)

> Inspect module routes for enable/disable status, feature trait wiring (translation, CMR, revisions, …), and consistency findings.

## Command Information

- **Signature:** `modularous:route:inspect {module?} {route?}`
- **Alias:** `modularous:route:doctor`
- **Category:** Module

## What it reports

For each route the inspector returns:

| Field | Source |
|-------|--------|
| `enabled` | Status store via `ModuleRoute` |
| `parent` | Module route config `parent` |
| `features.*` | Detected from model/repository traits via `config/merges/traits.php` |
| `findings` | Produced by inspector (orphan status, companions, CMR Front, …) |

Route data is resolved through [`ModuleRoute`](/guide/console/module/module-route) (`$module->route()` / `$module->routes()`). Findings remain inspector-only.

## Examples

### Inspect all enabled modules

```bash
php artisan modularous:route:inspect
# or
php artisan modularous:route:doctor
```

### Inspect one module

```bash
php artisan modularous:route:inspect PrimaryPage
```

### Inspect one route

```bash
php artisan modularous:route:inspect PrimaryPage Home
```

### JSON (Web UI contract)

```bash
php artisan modularous:route:inspect PrimaryPage --json
```

### Findings only / feature filter

```bash
php artisan modularous:route:inspect --findings-only
php artisan modularous:route:inspect --feature=revisions,cmr
```

## Options

| Option | Description |
|--------|-------------|
| `--json` | Machine-readable list of entry arrays (includes `remedy` on findings) |
| `--findings-only` | Only routes that have findings |
| `--feature=` | Comma-separated feature keys; keeps routes that **present** any of them |
| `--suggest` | Print suggested `modularous:remake:*` artisan lines for healable findings |
| `--heal` | Run **safe** allowlisted remakes (dry-run by default) |
| `--no-dry-run` | With `--heal`, write files |
| `--force-heal` | Allow unsafe remedies that need remake `--force` |

### Heal / remake (opt-in)

Inspect stays report-first. Heal only runs from this CLI or the gated panel API — **never** on sidebar / controller hot path.

| Finding code | Remedy |
|--------------|--------|
| `cmr_missing_front_controller` | `modularous:remake:cmr` (safe) |
| `cmr_front_not_cms_controller` | `modularous:remake:cmr --force` (unsafe; needs `--force-heal`) |
| companion + feature `cmr` / `revisions` | matching remake |
| other companions / status / missing model | manual tip only |

```bash
php artisan modularous:route:inspect PrimaryPage --findings-only --suggest
php artisan modularous:route:inspect PrimaryPage AboutUs --heal
php artisan modularous:route:inspect PrimaryPage AboutUs --heal --no-dry-run
```

## Admin Web UI panel

Inertia page + JSON API (ArtisanRunner-style), gated by config — not Spatie route permissions.

| Setting | Env | Default |
|---------|-----|---------|
| `enabled` | `MODULAROUS_MODULE_ROUTE_INSPECT_ENABLED` | `false` |
| `allowed_roles` | `MODULAROUS_MODULE_ROUTE_INSPECT_ALLOWED_ROLES` | `superadmin` |
| `allow_status_toggle` | `MODULAROUS_MODULE_ROUTE_INSPECT_ALLOW_STATUS_TOGGLE` | `true` |
| `allow_heal` | `MODULAROUS_MODULE_ROUTE_INSPECT_ALLOW_HEAL` | `false` |

| Route | Name | Purpose |
|-------|------|---------|
| `GET module-route-inspect` | `admin.module-route-inspect` | Inertia panel |
| `GET api/module-route-inspect` | `admin.module-route-inspect.inspect` | JSON report (+ remedies) |
| `PATCH api/module-route-inspect/status` | `admin.module-route-inspect.status` | Enable/disable a route |
| `POST api/module-route-inspect/heal` | `admin.module-route-inspect.heal` | Run allowlisted remake (dry-run default) |

Sidebar: `_module_route_inspect` under `sidebar.superadmin`.

Vue: `Pages/ModuleRouteInspect.vue` + `hooks/useModuleRouteInspect.js`.

Findings menu shows suggested artisan + **Copy**; when `allow_heal` is on, safe remakes get a dry-run heal button.

Enable in `.env`:

```env
MODULAROUS_MODULE_ROUTE_INSPECT_ENABLED=true
# optional panel heal
MODULAROUS_MODULE_ROUTE_INSPECT_ALLOW_HEAL=true
```

## Status store adapter

Config: `modularous.module_route_inspect.driver` (env `MODULAROUS_MODULE_ROUTE_STATUS_DRIVER`).

| Driver | Behaviour |
|--------|-----------|
| `filesystem` (default) | `{module}/routes_statuses.json` via `ModuleActivator` |
| `database` | `um_module_route_statuses` table (env-specific; not git-tracked) |

Runtime (`Module::enableRoute` / `isEnabledRoute`), panel toggles, and `route:enable` / `route:disable` all resolve `ModuleRouteStatusStoreInterface`.

```env
# default
MODULAROUS_MODULE_ROUTE_STATUS_DRIVER=filesystem

# production / shared envs — avoid dirtying git with routes_statuses.json
MODULAROUS_MODULE_ROUTE_STATUS_DRIVER=database
```

Run package migrations so `um_module_route_statuses` exists before switching to `database`.

## Related

- [`route:status`](./route-status) — enable/disable listing only
- [`route:enable`](./route-enable) / [`route:disable`](./route-disable)
- `modularous:remake:cmr` / `modularous:remake:revisions` — allowlisted heal targets from inspect `--suggest` / `--heal`
- [ADR — ModuleRoute Hot Path](/system-reference/adr-module-route-hot-path) — heal never on sidebar/controller hot path

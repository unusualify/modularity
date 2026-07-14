---
sidebarPos: 8
sidebarTitle: Console Commands
---

# Remote API Console Commands

All Remote API Artisan commands live in `src/Console/Sync/` and are auto-discovered by `CommandDiscovery`.

## `modularous:sync-remote-api`

**Class:** `SyncRemoteApiCommand`  
**Aliases:** `mod:sync:remote-api`

```bash
php artisan modularous:sync-remote-api {module} {route}
    {--id= : Sync a single remote id}
    {--local-id= : Sync using a local record id (reads remote_id)}
    {--dry-run : Preview without HTTP or database writes}
```

### Examples

```bash
# Preview full batch sync
php artisan modularous:sync-remote-api ModuleName entity_name --dry-run

# Sync all linked + import new remote rows
php artisan modularous:sync-remote-api ModuleName entity_name

# Sync one remote id
php artisan modularous:sync-remote-api ModuleName entity_name --id=42

# Sync by local primary key
php artisan modularous:sync-remote-api ModuleName entity_name --local-id=7

# Preview single record
php artisan modularous:sync-remote-api ModuleName entity_name --id=42 --dry-run
```

### Behavior

| Options | Action |
|---------|--------|
| Neither `--id` nor `--local-id` | `syncAllFromRemote()` or preview all |
| `--id` and/or `--local-id` | `syncFromRemote()` or preview one |
| `--dry-run` | Preview only; uses `InteractsWithRemoteApiSyncPreview` output |

Requires repository `RemoteApiSourceTrait`. Exits with failure if trait or connector is missing.

Dry-run output includes connector summary (module, route, base URL, endpoint, token configured, cache enabled, includes).

## `modularous:sync-remote-api-catalog`

**Class:** `SyncRemoteApiCatalogCommand`  
**Aliases:** `mod:sync:remote-api-catalog`

```bash
php artisan modularous:sync-remote-api-catalog {module} {route} {catalog?}
    {--dry-run : Preview without HTTP or cache writes}
```

### Examples

```bash
# Preview default list endpoint
php artisan modularous:sync-remote-api-catalog ModuleName entity_name --dry-run

# Fetch and print default catalog JSON lines
php artisan modularous:sync-remote-api-catalog ModuleName entity_name

# Named catalog from connector catalogs.* config
php artisan modularous:sync-remote-api-catalog ModuleName entity_name regions --dry-run
php artisan modularous:sync-remote-api-catalog ModuleName entity_name regions
```

Uses `listRemoteCatalog()` — respects connector cache (`rememberPaginatedCatalog`).

## `modularous:make:remote-api-adapter`

**Class:** `MakeRemoteApiAdapterCommand`  
**Aliases:** `modularous:make:remote-api`, `mod:c:remote-api-adapter`

Scaffolds Remote API classes from stubs in `src/Console/stubs/remote-api/`.

```bash
php artisan modularous:make:remote-api-adapter {module} {route}
    {--dto : Generate DTO only}
    {--adapter : Generate adapter only}
    {--connector : Generate connector only}
    {--all : Generate DTO, adapter and connector}
    {--endpoint= : Initial API endpoint (default: kebab-case route name)}
    {--f|force : Overwrite existing files}
```

### Default behavior

When no `--dto`, `--adapter`, or `--connector` flag is passed, **all three** files are generated (`--all` equivalent).

### Output location

```
modules/{ModuleName}/RemoteApi/
├── {Route}RemoteApiDto.php
├── {Route}RemoteApiAdapter.php
└── {Route}RemoteApiConnector.php
```

Namespace: `{ModulesNamespace}\{ModuleName}\RemoteApi`

### Stub contents

**Connector** (`connector.stub`):

- `enabled => true`, placeholder `endpoint` / `show_endpoint`
- Default `response.list_path` / `item_path` for nested Modularous API envelopes
- Mapping: `remote_id`, `remote_payload` (`@raw`), `remote_synced_at` (`@now`)
- Default `actions`: sync_record, sync_all, clear_cache, preview
- Class references for adapter and DTO

**Adapter** (`adapter.stub`):

- Extends `AbstractRemoteApiAdapter`
- Override hooks: `afterMap`, `transformField`

**DTO** (`dto.stub`):

- Extends `RemoteApiRecordDto`

### Example

```bash
php artisan modularous:make:remote-api-adapter Catalog package \
    --endpoint=packages \
    --force
```

Creates `PackageRemoteApiConnector.php` with `'endpoint' => 'packages'`.

## Preview Trait

`InteractsWithRemoteApiSyncPreview` shared by sync commands:

| Method | Purpose |
|--------|---------|
| `renderRemoteApiConfigurationSummary` | Two-column connector details |
| `renderRemoteApiSyncAllPreview` | Linked / unlinked local tables |
| `renderRemoteApiSyncRecordPreview` | Single record action table |
| `renderRemoteApiCatalogPreview` | Catalog key and available catalogs |

## Scheduling

Register in your app scheduler for periodic sync:

```php
Schedule::command('modularous:sync-remote-api ModuleName entity_name')
    ->hourly()
    ->withoutOverlapping();
```

Consider cache TTL and rate limits when choosing frequency.

## See Also

- [Sync Flow](./sync-flow) — Algorithm details
- [Integration Guide](./integration) — Full module wiring checklist

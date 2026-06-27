---
sidebarPos: 12
sidebarTitle: Remote API
---

# Remote API

Modularous can mirror records from an external JSON API into local CMS entities. A **connector** fetches remote rows, an **adapter** maps them to attributes, and a polymorphic **`um_remote_api_sources`** row stores the link (`remote_id`), synced mirror fields, and the raw payload.

## In This Section

| Page | Purpose |
|------|---------|
| [Overview](./overview) | Architecture, request flow, component roles |
| [Configuration](./configuration) | Package config, connector `remoteApiConfiguration()`, env vars |
| [Database](./database) | `um_remote_api_sources` schema and morph relationship |
| [Core Classes](./core-classes) | Traits, synchronizer, client, factory, hydrate |
| [Sync Flow](./sync-flow) | `syncRecord`, `syncAll`, preview, cache clear |
| [Caching & Rate Limiting](./caching-and-rate-limiting) | Cache keys, tags, pagination validation, outgoing limits |
| [Admin UI](./admin-ui) | `remote-api` input, table toolbar actions |
| [Console Commands](./console-commands) | `sync-remote-api`, catalog fetch, code generator |
| [Integration Guide](./integration) | Step-by-step module setup using stubs |

## Quick Start

```bash
# 1. Run the package migration (creates um_remote_api_sources)
php artisan migrate

# 2. Set global Remote API credentials
# MODULAROUS_REMOTE_API_BASE_URL=https://api.example.com/api/v1
# MODULAROUS_REMOTE_API_TOKEN=your-bearer-token

# 3. Generate connector classes for a module route
php artisan modularous:make:remote-api-adapter ModuleName entity_name --endpoint=entities

# 4. Wire entity, repository, controller, and form input (see Integration Guide)

# 5. Preview sync without HTTP or DB writes
php artisan modularous:sync-remote-api ModuleName entity_name --dry-run

# 6. Sync all linked records
php artisan modularous:sync-remote-api ModuleName entity_name
```

## When to Use

Use Remote API when a module entity should stay linked to a canonical record in another Modularous (or compatible JSON) API — for example pricing catalogs, reference data, or cross-instance content mirrors — without duplicating HTTP logic in every module.

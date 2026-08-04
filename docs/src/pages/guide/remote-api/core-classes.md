---
sidebarPos: 4
sidebarTitle: Core Classes
outline: deep
---

# Remote API Core Classes

Reference for the main PHP classes in `src/Services/RemoteApi/`, traits, and the admin hydrate.

## Model & Repository Traits

### `HasRemoteApiSource` (`src/Entities/Traits/HasRemoteApiSource.php`)

| Method | Purpose |
|--------|---------|
| `remoteApiSource()` | `MorphOne` to `RemoteApiSource` |
| `getRemoteApiId()` | Current remote id (virtual or persisted) |
| `hydrateRemoteApiAttributes()` | Load synced fields onto the model |
| `persistRemoteApiVirtualAttributes()` | Write virtual dirty fields to side table |
| `stripRemoteApiVirtualAttributes()` | Remove virtual keys before parent save |
| `toRemoteApiArray()` | Merged array for API transformers |

Bootstraps `RemoteApiSourceableObserver`.

### `RemoteApiSourceTrait` (`src/Repositories/Traits/RemoteApiSourceTrait.php`)

Repository entry point. Requires `Moduleable` for module/route resolution.

| Method | Purpose |
|--------|---------|
| `remoteApiConnector()` | Resolve connector via `RemoteApiConnectorFactory` |
| `syncFromRemote($localId, $remoteId)` | Single-record sync |
| `syncAllFromRemote()` | List-first batch sync |
| `previewSyncFromRemote()` / `previewSyncAllFromRemote()` | Dry-run summaries (no HTTP) |
| `previewRemoteCatalog($catalogKey)` | Catalog fetch preview |
| `clearRemoteApiCache($remoteId)` | Flush connector cache |
| `listRemoteCatalog($catalogKey)` | Paginated catalog rows (cached) |
| `previewRemote($remoteId)` | Fetch one row through cache |
| `getRemoteApiActionSchema()` | Table/row action definitions from `actions` config |

## Controller Trait

### `ManageRemoteApiSync` (`src/Http/Controllers/Traits/ManageRemoteApiSync.php`)

Included on `BaseController`. Registers table toolbar actions when the repository uses `RemoteApiSourceTrait` and the connector is enabled.

| HTTP action | Method | Route pattern |
|-------------|--------|---------------|
| Sync one record | `syncRemote` | `PUT …/sync-remote/{id}` |
| Sync all linked | `syncRemoteAll` | `POST …/sync-remote-all` |
| Clear cache | `clearRemoteCache` | `POST …/clear-remote-cache` |
| Preview payload | `previewRemote` | `PUT …/preview-remote/{id}` |
| Catalog list (combobox) | `listRemoteCatalog` | `GET …/list-remote-catalog` |

`RemoteApiSyncException` is converted to `ValidationException` on the `remote_api` key for frontend error display.

`setTableActionsManageRemoteApiSync()` merges `syncRemoteAll` and `clearRemoteCache` (scope `table`) into `$this->tableActions`.

## Services

### `RemoteApiSynchronizer`

Orchestrates create/update logic:

- **`syncRecord($connector, $repository, $remoteId)`** — `fetchOne(..., forceRefresh: true)` → map → partition → repository create/update → sync `RemoteApiSource`.
- **`syncAll($connector, $repository)`** — Stream remote list pages (`eachListPage` + forceRefresh) → update linked locals → optionally import new remote ids → `clearCache()` → return stats + skipped stale links + HTTP request counts.
- **`previewSyncRecord` / `previewSyncAll`** — Configuration summary without HTTP or DB writes.

Throws `RemoteApiSyncException` when a single-record fetch returns null.

### `RemoteApiClient`

Low-level HTTP via Laravel `Http` facade:

- **`get($endpoint, $query, $allowNotFound)`** — Asserts rate limit, records request, handles 404/429.
- **`eachPaginatedListPage`** — Streams pages via callback (no full-list accumulation); validates complete fetch; max 100 pages safety cap.
- **`fetchPaginatedListResult`** — Collects all pages from `eachPaginatedListPage` into `{ items, expected_total }`.
- **`getItem`** — Extracts single item via `item_path`.
- Merges `http.includes` as `include=` query param unless `_skip_sync_includes` is set (catalog fetches).

Uses `RemoteApiRateLimiter` before each request and `RemoteApiRequestTracker` after.

### `RemoteApiCache`

| Method | Behavior |
|--------|----------|
| `remember($key, $callback)` | Standard TTL cache |
| `rememberNonEmpty($key, $callback)` | Skips caching empty arrays |
| `rememberPaginatedCatalog($key, $callback)` | Stores `{ expected_total, items }`; validates on read |
| `forget($key)` | Drop one key |
| `flush($remoteId)` | Per-record keys or full tag/version flush |

Cache key format:

```
remote-api:{module_snake}:{route_snake}:{version}:{key}
```

Tag format (when store supports tags): `remote-api.{module}.{route}` from `RemoteApiConfiguration::cacheTag()`.

Without tags, `flush(null)` bumps a version key so prefixed keys effectively invalidate.

### `RemoteApiRateLimiter`

Outgoing client guard using Laravel `RateLimiter`:

- Keys: `remote-api-outgoing:{host}:{path-without-trailing-id}` + `:minute` / `:hour` suffixes.
- **`assertCanRequest($url)`** — Throws `RemoteApiSyncException::rateLimitExceeded()` when over limit.
- **`hit($url)`** — Increments counters (60s / 3600s decay).

Also surfaces remote **429** responses from `RemoteApiClient::get()` as `RemoteApiSyncException` with `Retry-After`.

### `RemoteApiRequestTracker`

In-memory per-connector request stats:

- **`record($url)`** — Normalizes URL (scheme + host + path, strips query).
- **`flush()`** — Returns `{ total, by_url }` and resets.

Used by `syncAll()` response meta and connector `flushRequestStats()`.

### `RemoteApiConnectorFactory`

Builds a wired connector:

```php
app(RemoteApiConnectorFactory::class)->make('ModuleName', 'entity_name');
// → new EntityNameRemoteApiConnector($configuration, $client, $cache, $adapter)
```

Shares one package-level `RemoteApiRateLimiter` instance across connectors.

### `RemoteApiConnectorResolver`

Resolves `RemoteApiConfiguration`, connector/adapter/DTO class names from module route config or connector static config.

### `RemoteApiConfiguration`

Typed accessor for all connector settings: URLs, paths, mapping, cache, catalogs, actions, `toSummaryArray()` for previews.

Factory methods:

- `fromConnectorClass($module, $routeName, $connectorClass)`
- `fromRouteConfig($module, $routeName, $config)`

### `AbstractRemoteApiConnector`

Default fetch/cache pipeline:

| Method | Cache key | Notes |
|--------|-----------|-------|
| `fetchList($query, $forceRefresh)` | `list:v2:{md5(query)}` | Paginated list via `rememberPaginatedCatalog` |
| `eachListPage($callback, $query, $forceRefresh)` | — | Streams pages; forceRefresh skips list cache |
| `fetchOne($id, $query, $forceRefresh)` | `record:{id}` | Single item; forceRefresh bypasses cache read |
| `listCatalog($key)` | `catalog:v2:…` | Default list or named catalog endpoint |
| `clearCache($id)` | — | Delegates to `RemoteApiCache::flush` |

Override hooks: `beforeFetch`, `afterFetch`, `afterFetchOne`, `beforeCatalogFetch`.

### `AbstractRemoteApiAdapter` / `RemoteApiFieldMapper`

`mapToAttributes($row, $existingAttributes)` applies config `mapping`, field rules, and optional `afterMap` / `transformField` overrides in module adapters.

### `RemoteApiAttributePartition`

Splits mapped attributes into local entity fillable vs remote side-table payload (see [Database](./database)).

## Hydrate Input

### `RemoteApiHydrate` (`src/Hydrates/Inputs/RemoteApiHydrate.php`)

| Config type | Hydrated schema type | Vue component |
|-------------|---------------------|---------------|
| `remote-api` | `input-remote-api` | `VInputRemoteApi` (`vue/src/js/components/inputs/RemoteApi.vue`) |

Hydrate behavior:

- Sets `name` default to `remote_id`.
- Loads catalog items via `listRemoteCatalog()` when not in console.
- Exposes `catalogEndpoint` for frontend async refresh.
- Resolves `itemValue` from mapping (`remote_id` → remote JSON id key).

Registry: `'input-remote-api': 'VInputRemoteApi'` in `vue/src/js/components/inputs/registry.js`.

## Observers

| Observer | Trigger | Effect |
|----------|---------|--------|
| `RemoteApiSourceableObserver` | Entity retrieved/saving/saved/forceDeleting | Virtual attribute lifecycle |
| `RemoteApiSourceObserver` | `RemoteApiSource` saved | `touchQuietly()` parent |

## Exceptions

| Class | When |
|-------|------|
| `RemoteApiConfigurationException` | Missing base URL, endpoint, disabled connector, invalid catalog |
| `RemoteApiSyncException` | Record not found, missing remote id, incomplete pagination, rate limit exceeded |

`RemoteApiSyncException::rateLimitExceeded()` sets `isRateLimit = true` and `retryAfterSeconds`.

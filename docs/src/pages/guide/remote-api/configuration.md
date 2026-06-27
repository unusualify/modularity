---
sidebarPos: 2
sidebarTitle: Configuration
---

# Remote API Configuration

Configuration is layered: package defaults in `config/merges/`, optional connector-level overrides, and environment variables.

## Package Defaults (`config/merges/remote_api.php`)

Loaded as `config('modularous.remote_api')`:

```php
return [
    'base_url' => env('MODULAROUS_REMOTE_API_BASE_URL', ''),
    'token' => env('MODULAROUS_REMOTE_API_TOKEN'),
    'cache_ttl' => (int) env('MODULAROUS_REMOTE_API_CACHE_TTL', 3600),
    'timeout' => (int) env('MODULAROUS_REMOTE_API_TIMEOUT', 30),
    'rate_limiting' => [
        'enabled' => env('MODULAROUS_REMOTE_API_RATE_LIMITING_ENABLED', env('MODULAROUS_API_RATE_LIMITING_ENABLED', true)),
        'per_minute' => (int) env('MODULAROUS_REMOTE_API_RATE_LIMITING_PER_MINUTE', env('MODULAROUS_API_RATE_LIMITING_PER_MINUTE', 60)),
        'per_hour' => (int) env('MODULAROUS_REMOTE_API_RATE_LIMITING_PER_HOUR', env('MODULAROUS_API_RATE_LIMITING_PER_HOUR', 1000)),
    ],
];
```

| Key | Env variable | Default | Purpose |
|-----|--------------|---------|---------|
| `base_url` | `MODULAROUS_REMOTE_API_BASE_URL` | `''` | Root URL for all connectors unless overridden |
| `token` | `MODULAROUS_REMOTE_API_TOKEN` | `null` | Bearer token sent as `Authorization: Bearer …` |
| `cache_ttl` | `MODULAROUS_REMOTE_API_CACHE_TTL` | `3600` | Default cache TTL (seconds) |
| `timeout` | `MODULAROUS_REMOTE_API_TIMEOUT` | `30` | HTTP timeout (seconds) |
| `rate_limiting.*` | `MODULAROUS_REMOTE_API_RATE_LIMITING_*` | Falls back to `modularous.api.rate_limiting` | Outgoing client limits |

## Incoming API Rate Limits (`config/merges/api.php`)

Remote API **outgoing** limits default to the same values as **incoming** Modularous API rate limits:

```php
'rate_limiting' => [
    'enabled' => env('MODULAROUS_API_RATE_LIMITING_ENABLED', true),
    'per_minute' => env('MODULAROUS_API_RATE_LIMITING_PER_MINUTE', 60),
    'per_hour' => env('MODULAROUS_API_RATE_LIMITING_PER_HOUR', 1000),
    // … blocking_time, blocking_maximum_attempts, blocking_time_threshold (incoming only)
],
```

`RemoteApiRateLimiter` reads `modularous.remote_api.rate_limiting` first, then falls back to `modularous.api.rate_limiting`. The blocking settings apply to incoming API middleware, not the outgoing Remote API client.

## Connector Configuration

Define configuration on your connector class via `remoteApiConfiguration()`. The generator stub provides a starting point:

```php
public static function remoteApiConfiguration(): array
{
    return [
        'enabled' => true,
        'base_url' => null,              // null → uses modularous.remote_api.base_url
        'token' => null,                 // null → uses modularous.remote_api.token
        'endpoint' => 'entities',
        'show_endpoint' => 'entities/{id}',
        'remote_id_column' => 'remote_id',

        'http' => [
            'timeout' => 30,
            'query' => [],               // Default query params on every request
            'includes' => [],            // Appended as include=… unless skipped
        ],

        'catalog_http' => [
            'query' => ['per_page' => 100],  // Lighter query for admin combobox
        ],

        'response' => [
            'list_path' => 'data.data',       // JSON path to list items
            'item_path' => 'data',            // JSON path to single item
            'meta_path' => 'data',            // Pagination meta (current_page, last_page, total)
            'catalog_list_path' => 'data.data', // Optional override for named catalogs
        ],

        'mapping' => [
            'remote_id' => 'id',
            'remote_payload' => '@raw',
            'remote_synced_at' => '@now',
            'synced_name' => 'name',          // Stored in synced_attributes JSON
        ],

        'fields' => [
            'name' => ['local' => true],      // Persist on parent entity table
            'synced_name' => ['sync' => true],
        ],

        'sync' => [
            'preserve_local_fields' => ['published'],
            'import_new_from_list' => true,
        ],

        'cache' => [
            'enabled' => true,
            'ttl' => 3600,
            'tag' => 'remote-api.module_name.entity_name',
        ],

        'catalogs' => [
            'regions' => [
                'endpoint' => 'regions',
                'list_path' => 'data.data',
            ],
        ],

        'catalog_title_key' => 'name',      // Used by RemoteApiHydrate for itemTitle

        'actions' => [
            'sync_record',
            'sync_all',
            'clear_cache',
            'preview',
        ],

        'classes' => [
            'connector' => self::class,
            'adapter' => EntityNameRemoteApiAdapter::class,
            'dto' => EntityNameRemoteApiDto::class,
        ],
    ];
}
```

### Mapping Special Sources

`RemoteApiFieldMapper` supports these mapping source tokens:

| Source | Result |
|--------|--------|
| `'id'` (dot path) | `data_get($row, 'id')` |
| `'@raw'` | Full API row (stored as `remote_payload`) |
| `'@now'` | Current timestamp (`remote_synced_at`) |
| `'@json:relationships'` | JSON-encoded subtree |
| `'@cast:int:price.amount'` | Typed cast from nested path |

### Route Config Fallback

When no dedicated connector class exists, put equivalent settings under the module route config key `api_connector`. `RemoteApiConfiguration::fromRouteConfig()` validates `enabled`, `base_url`, and `endpoint` the same way.

## Environment Variables (Summary)

```env
MODULAROUS_REMOTE_API_BASE_URL=https://api.example.com/api/v1
MODULAROUS_REMOTE_API_TOKEN=secret-token
MODULAROUS_REMOTE_API_CACHE_TTL=3600
MODULAROUS_REMOTE_API_TIMEOUT=30

MODULAROUS_REMOTE_API_RATE_LIMITING_ENABLED=true
MODULAROUS_REMOTE_API_RATE_LIMITING_PER_MINUTE=60
MODULAROUS_REMOTE_API_RATE_LIMITING_PER_HOUR=1000
```

Per-connector `base_url`, `token`, `cache.ttl`, and `http.timeout` override these globals when set.

## Enabled Check

Sync endpoints, table actions, and the admin combobox only activate when:

1. The repository uses `RemoteApiSourceTrait`.
2. Connector configuration has `'enabled' => true`.
3. `base_url` and `endpoint` resolve to non-empty values.

`ManageRemoteApiSync::remoteApiConnectorIsEnabled()` catches `RemoteApiConfigurationException` and hides actions when misconfigured.

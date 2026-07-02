---
sidebarPos: 2
sidebarTitle: Configuration
---

# Module Route Cache Configuration

Configuration is layered: package defaults in `config/merges/cache.php`, merged into `config('modularous.cache')`, with optional per-app overrides in `config/modularous.php` or `config/modularity.php`.

## Package Defaults (`config/merges/cache.php`)

```php
return [
    'enabled' => env('MODULAROUS_RESOURCE_CACHE_ENABLED', true),
    'all_modules' => env('MODULAROUS_RESOURCE_CACHE_ALL_MODULES', false),
    'environment_variable' => env('MODULAROUS_RESOURCE_CACHE_MODE', 'local'),
    'driver' => env('MODULAROUS_RESOURCE_CACHE_DRIVER', 'redis'),
    'prefix' => env('MODULAROUS_RESOURCE_CACHE_PREFIX', 'modularous'),
    'ttl' => [ /* counts, index, record, formItem, formattedItem, response:* */ ],
    'use_tags' => env('MODULAROUS_RESOURCE_CACHE_USE_TAGS', true),
    'user_aware' => env('MODULAROUS_RESOURCE_CACHE_USER_AWARE', true),
    'graph' => [
        'enabled' => env('MODULAROUS_RESOURCE_CACHE_GRAPH_ENABLED', true),
        'ttl' => (int) env('MODULAROUS_RESOURCE_CACHE_GRAPH_TTL', 86400),
    ],
    'modules' => [],
    'dependencies' => [],
];
```

## Global Environment Variables

| Key | Env variable | Default | Purpose |
|-----|--------------|---------|---------|
| `enabled` | `MODULAROUS_RESOURCE_CACHE_ENABLED` | `true` | Master switch; when false, all cache ops bypass |
| `all_modules` | `MODULAROUS_RESOURCE_CACHE_ALL_MODULES` | `false` | Default enable for every module when no per-module flag |
| `environment_variable` | `MODULAROUS_RESOURCE_CACHE_MODE` | `local` | Cache logging mode (`local`, `development`, `production`) |
| `driver` | `MODULAROUS_RESOURCE_CACHE_DRIVER` | `redis` | Laravel cache store name |
| `prefix` | `MODULAROUS_RESOURCE_CACHE_PREFIX` | `modularous` | Prepended to all keys and tags |
| `use_tags` | `MODULAROUS_RESOURCE_CACHE_USE_TAGS` | `true` | Tag-based invalidation (Redis / Memcached) |
| `user_aware` | `MODULAROUS_RESOURCE_CACHE_USER_AWARE` | `true` | Include auth user in count/index key hashes |

## TTL Environment Variables

| TTL key | Env variable | Default (seconds) |
|---------|--------------|-------------------|
| `counts` | `MODULAROUS_RESOURCE_CACHE_TTL_COUNTS` | 300 |
| `index` | `MODULAROUS_RESOURCE_CACHE_TTL_INDEX` | 600 |
| `record` | `MODULAROUS_RESOURCE_CACHE_TTL_RECORD` | 1800 |
| `formattedItem` | `MODULAROUS_RESOURCE_CACHE_TTL_FORMATTED_ITEM` | 1800 |
| `formItem` | `MODULAROUS_RESOURCE_CACHE_TTL_FORM_ITEM` | 1800 |
| `response:json` | `MODULAROUS_RESOURCE_CACHE_TTL_RESPONSE` | 300 |
| `response:index` | `MODULAROUS_RESOURCE_CACHE_TTL_RESPONSE` | 300 |

## Graph Settings

| Key | Env variable | Default | Purpose |
|-----|--------------|---------|---------|
| `graph.enabled` | `MODULAROUS_RESOURCE_CACHE_GRAPH_ENABLED` | `true` | Auto-build relationship graph for cross-module invalidation |
| `graph.ttl` | `MODULAROUS_RESOURCE_CACHE_GRAPH_TTL` | `86400` | How long the built graph is cached |

Commands: `php artisan modularous:cache:graph show|rebuild|stats|analyze`

## Enable Resolution Order

`ModularousCacheService::isEnabled($module, $route, $type)` evaluates in order:

1. **Redis connected?** — if not, return `false`
2. **Global `enabled`** — if false, return `false`
3. **Module `modules.{Module}.enabled`** — falls back to `all_modules`
4. **Route `modules.{Module}.routes.{Route}.enabled`** — falls back to `all_modules`
5. **Type `modules.{Module}.routes.{Route}.types.{type}`** — per-type toggle

TTL resolution (`getTtl`) checks route-level TTL, then module-level TTL, then global `ttl.{type}`.

## Per-Module Override Structure

Module names use **StudlyCase**. Each module can define module-wide TTL and per-route overrides:

```php
'cache' => [
    'modules' => [
        'PressRelease' => [
            'enabled' => true,
            'ttl' => [
                'counts' => 900,
                'formattedItem' => 1800,
                'formItem' => 900,
            ],
            'routes' => [
                'PressRelease' => [
                    'enabled' => true,
                    'types' => [
                        'counts' => true,
                        'index' => false,
                        'record' => false,
                        'formattedItem' => true,
                        'formItem' => true,
                    ],
                ],
                'PressReleasePayment' => [
                    'enabled' => true,
                    'types' => [
                        'counts' => true,
                        'formattedItem' => true,
                        'formItem' => true,
                    ],
                ],
            ],
        ],
    ],
],
```

### Real-World Example: b2press-app

`b2press-app/config/modularity.php` (lines 726–865) enables `PressRelease` and `SystemPayment` with:

- **Counts + formattedItem + formItem** on — index and record off (large tables, row-level cache preferred)
- **`dependencies`** mapping `PressRelease`, `PressReleasePackage`, `Payment` updates to related route invalidation

Copy that pattern when a submodule displays data owned by another entity.

## Manual Dependencies

Use `cache.dependencies` when graph auto-discovery misses a path (vendor models, polymorphic edges, custom presenters):

```php
'dependencies' => [
    'Modules\Company\Entities\Company' => [
        [
            'moduleName' => 'PressRelease',
            'moduleRouteName' => 'PressReleasePayment',
            'types' => [
                'counts' => false,
                'index' => false,
                'record' => true,
                'formattedItem' => true,
                'formItem' => true,
            ],
            'targetRelationshipName' => 'pressReleasePayments',
        ],
    ],
],
```

Keys must be **full model class names**. Entries merge with graph-discovered dependents in `CacheObserver`.

## App Override Locations

| App | Typical file |
|-----|--------------|
| Standalone Modularous app | `config/modularous.php` → `'cache' => [ … ]` |
| b2press-app | `config/modularity.php` → `'cache' => [ … ]` |
| b2press-cms (admin pilot) | `config/modularous.php` — add `cache` block when enabling admin cache |

Laravel merges package `config/merges/cache.php` automatically; app keys override defaults.

## Observer Queue (async invalidation)

When `cache.observer.queue` is true and `QUEUE_CONNECTION` is not `sync`, `CacheObserver` dispatches `InvalidateModelCacheJob` and `InvalidateDependentCachesJob` instead of blocking the admin save request.

| Env variable | Config key | Default |
|--------------|------------|---------|
| `MODULAROUS_CACHE_OBSERVER_QUEUE` | `observer.queue` | `true` |
| `MODULAROUS_CACHE_QUEUE_CONNECTION` | `observer.queue_connection` | `null` (uses `queue.default`) |
| `MODULAROUS_CACHE_QUEUE_NAME` | `observer.queue_name` | `modularous-cache` |

Run a dedicated worker:

```bash
php artisan queue:work --queue=modularous-cache
```

Set `observer.queue=false` in `phpunit.xml` to keep tests synchronous.

## Manual purge and admin cache actions

Per route you can defer observer invalidation and expose superadmin purge/warm buttons:

```php
'PackageCountry' => [
    'manual_purge' => true,
    'admin_cache_actions' => true,
    'purge' => [
        'presentationItem' => true,  // observer skips this type
        'formItem' => false,       // observer still auto-invalidates
    ],
],
```

- `manual_purge: true` — observer skips auto invalidate/warm unless `purge.{type}` is explicitly `false`
- `admin_cache_actions: true` — form/index cache buttons (requires `ResourceCacheActionsTrait` on the repository)
- Endpoints: `POST .../cache/purge/{id}`, `POST .../cache/warm/{id}`, `POST .../cache/purge-all`, `POST .../cache/warm-all`

## See Also

- [Per-Module Setup](./per-module-setup) — checklist after editing config
- [Invalidation](./invalidation) — how `dependencies` interact with the graph
- [Cache Types](./cache-types) — which `types` flags to enable per route

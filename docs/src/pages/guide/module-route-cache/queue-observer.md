---
sidebarPos: 4
sidebarTitle: Queue Observer
---

# Async Cache Observer (Queue)

When a CMS editor saves a record, cache invalidation and warmup can take tens of seconds (especially `presentationItem` locale loops). The **async observer** moves that work to a dedicated queue so the admin HTTP response returns immediately.

## Configuration

Package defaults live in `config/merges/cache.php`:

```php
'observer' => [
    'queue' => env('MODULAROUS_CACHE_OBSERVER_QUEUE', true),
    'queue_connection' => env('MODULAROUS_CACHE_QUEUE_CONNECTION', null),
    'queue_name' => env('MODULAROUS_CACHE_QUEUE_NAME', 'modularous-cache'),
    'auto_invalidate' => true,
],
'queue' => [
    'connection' => env('MODULAROUS_CACHE_QUEUE_CONNECTION', null),
    'name' => env('MODULAROUS_CACHE_QUEUE_NAME', 'modularous-cache'),
],
```

| Env variable | Default | Purpose |
|--------------|---------|---------|
| `MODULAROUS_CACHE_OBSERVER_QUEUE` | `true` | When `false`, observer always runs sync (tests/local) |
| `MODULAROUS_CACHE_QUEUE_CONNECTION` | `null` | Queue connection; `null` uses `queue.default` |
| `MODULAROUS_CACHE_QUEUE_NAME` | `modularous-cache` | Dedicated worker queue name |

Async dispatch runs only when **both** are true:

1. `observer.queue` is enabled
2. The resolved queue connection is **not** `sync`

## Jobs

| Job | Responsibility |
|-----|----------------|
| `InvalidateModelCacheJob` | Relation-tag flush + model invalidate/refresh |
| `InvalidateDependentCachesJob` | Dependent module warmup via `DependentCacheInvalidator` |
| `WarmPresentationItemJob` | Single record presentationItem refresh (skip tag flush) |
| `WarmModuleRouteCachesJob` | Chunk warm for an entire route (admin warm-all) |

All jobs use `WithoutOverlapping` per `{module}:{route}:{id}` and log through `ModularousCacheLogger`.

## Worker setup (b2press-cms)

Run a Horizon/supervisor worker for the dedicated queue:

```bash
php artisan queue:work redis --queue=modularous-cache --sleep=1 --tries=3
```

Recommended `.env`:

```env
MODULAROUS_CACHE_OBSERVER_QUEUE=true
MODULAROUS_CACHE_QUEUE_NAME=modularous-cache
QUEUE_CONNECTION=redis
```

PHPUnit sets sync behaviour via `MODULAROUS_CACHE_OBSERVER_QUEUE=false` or default `sync` connection.

## Smart warmup (`refreshModelCaches`)

For `presentationItem` with `warmup: true`, the service uses **`refreshModelCaches`** (or `skipInvalidation: true`) so Redis entries are overwritten without a route-level tag flush first.

Deletes always invalidate (no warmup) even on manual-purge routes.

## Related

- [Manual Purge](./manual-purge)
- [Invalidation](./invalidation)
- [Admin Cache Actions](./admin-cache-actions)

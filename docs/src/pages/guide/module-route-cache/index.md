---
sidebarPos: 13
sidebarTitle: Module Route Cache
---

# Module Route Cache

Modularous caches admin panel data **per module route** — filter counts, paginated index queries, single records, form payloads, and formatted table rows. A **relationship graph** and **relation tags** keep dependent routes consistent when related models change.

This cache layer targets the **admin CRUD stack** (repositories and controllers). It does **not** automatically cache public CMS blade rendering; see [CMS Public Pages](./cms-public-pages) for that gap and the recommended b2press strategy.

## In This Section

| Page | Purpose |
|------|---------|
| [Overview](./overview) | Architecture, data flow, key format, trait map |
| [Configuration](./configuration) | `modularous.cache` merge, app overrides, env vars |
| [Cache Types](./cache-types) | `counts`, `index`, `record`, `formItem`, `formattedItem`, response types |
| [Invalidation](./invalidation) | `CacheObserver`, graph, dependencies, relation tags |
| [Queue Observer](./queue-observer) | Async invalidation/warmup jobs, env vars, workers |
| [Manual Purge](./manual-purge) | Per-route manual purge config |
| [Admin Cache Actions](./admin-cache-actions) | Superadmin purge/warm UI + API |
| [Warmup](./warmup) | `warmupByModel`, artisan warm commands |
| [Per-Module Setup](./per-module-setup) | `HasCaching` + config checklist |
| [CMS Public Pages](./cms-public-pages) | Admin cache vs public CMS; b2press presentation + full-page plan |
| [SWR](./swr) | Stale-while-revalidate and warm jobs |
| [URL Stale Resilience](./url-stale-resilience) | File-primary URL cache |
| [Webhook Revalidate](./webhook-revalidate) | HMAC webhook for external purge/warm |
| [Console Commands](./console-commands) | Links to `guide/console/cache/` command reference |

## Quick Start

```bash
# 1. Ensure Redis is available (default driver)
# MODULAROUS_RESOURCE_CACHE_ENABLED=true
# MODULAROUS_RESOURCE_CACHE_DRIVER=redis

# 2. Enable caching for a module in config/modularous.php (or modularity.php)
# See Configuration and Per-Module Setup pages

# 3. Add HasCaching to the entity model
# use Unusualify\Modularous\Entities\Traits\Core\HasCaching;

# 4. Rebuild the relationship graph after enabling new modules
php artisan modularous:cache:graph rebuild

# 5. Pre-warm caches (optional, recommended after deploy)
php artisan modularous:cache:warm PressRelease PressRelease --counts --formattedItems
```

## When to Use

Enable module-route cache when:

- Admin index pages or filter badges are slow under production data volume
- Form edit payloads (`formItem`) or table row formatting (`formattedItem`) are expensive to build
- Related models (payments, packages, companies) should invalidate dependent admin routes automatically

Skip or narrow cache types when data changes every few seconds and stale UI is unacceptable — use shorter TTLs or disable specific types per route instead of turning off the whole system.

## Related

- [ModularousCacheService](/system-reference/backend/services/modularous-cache-service) — service internals
- [CacheRelationshipGraph](/system-reference/backend/services/cache-relationship-graph) — auto-discovery graph
- [Cache Commands](../console/cache/overview) — clear, warm, stats, graph

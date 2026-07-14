---
sidebarPos: 8
sidebarTitle: Console Commands
---

# Module Route Cache — Console Commands

Artisan commands for clearing, warming, inspecting, and rebuilding module-route cache live under [Cache Commands](../console/cache/overview). They wrap [`ModularousCacheService`](/system-reference/backend/services/modularous-cache-service) and complement [Flush commands](../console/flush/overview).

## Command Reference

| Page | Signature | Use when |
|------|-----------|----------|
| [Overview](../console/cache/overview) | — | Workflows and command index |
| [cache:clear](../console/cache/cache-clear) | `modularous:cache:clear` | Invalidate all or targeted module/route caches |
| [cache:warm](../console/cache/cache-warm) | `modularous:cache:warm` | Pre-populate after deploy or clear |
| [cache:warm-presentation](../console/cache/cache-warm-presentation) | `modularous:cache:warm-presentation` | Pre-warm public presentationItem URL caches |
| [cache:purge-presentation](../console/cache/cache-purge-presentation) | `modularous:cache:purge-presentation` | Purge public presentationItem filesystem caches |
| [cache:stats](../console/cache/cache-stats) | `modularous:cache:stats` | Inspect key counts per module |
| [cache:graph](../console/cache/cache-graph) | `modularous:cache:graph` | Show, rebuild, or analyze relationship graph |
| [cache:versions](../console/cache/cache-versions) | `modularous:cache:versions` | Print cache version counters |
| [cache:list](../console/cache/cache-list) | `modularous:cache:list` | List file-cache entries (hidden command) |

## Common Workflows

### Enable a new cached module

```bash
php artisan modularous:cache:graph rebuild
php artisan modularous:cache:warm MyModule MyRoute --counts --formattedItems
php artisan modularous:cache:stats MyModule
```

### After deploy

```bash
php artisan modularous:cache:clear
php artisan modularous:cache:warm
php artisan modularous:cache:versions
```

### Debug stale admin data

```bash
php artisan modularous:cache:stats PressRelease --keys --deps
php artisan modularous:cache:graph analyze --model=PressRelease
php artisan modularous:cache:clear PressRelease PressRelease --formattedItems
php artisan modularous:cache:warm PressRelease PressRelease --formattedItems --limit=10
```

### Inspect cross-module invalidation

```bash
php artisan modularous:cache:graph show
php artisan modularous:cache:graph stats --format=json
php artisan modularous:cache:stats --graph
```

## cache:clear Options

Target specific cache types per route:

```bash
php artisan modularous:cache:clear Blog Post --index
php artisan modularous:cache:clear Blog Post --counts --formattedItems --formItems
```

| Option | Clears |
|--------|--------|
| `--counts` | Filter badge caches |
| `--index` | Paginated list caches |
| `--records` | Single record caches |
| `--formattedItems` | Table row formatting caches |
| `--formItems` | Edit form payload caches |

## cache:warm Options

```bash
php artisan modularous:cache:warm Blog Post --counts
php artisan modularous:cache:warm Blog Post --formattedItems --limit=100
php artisan modularous:cache:warm Blog Post --items --eager=author,category
```

Align warm flags with enabled `types` in config — warming disabled types has no effect.

## cache:graph Actions

| Action | Purpose |
|--------|---------|
| `show` (default) | Display relationship graph |
| `rebuild` | Rescan modules after new `HasCaching` entities or routes |
| `stats` | Graph statistics |
| `analyze` | Impact for a specific `--model` or table |

Run `rebuild` after adding modules to `cache.modules` or changing `getEloquentRelationships()` on entities.

## Related System Reference

- [ModularousCacheService](/system-reference/backend/services/modularous-cache-service)
- [CacheRelationshipGraph](/system-reference/backend/services/cache-relationship-graph)
- [Flush overview](../console/flush/overview) — broader runtime flush (`flush`, `flush:sessions`)

## See Also

- [Warmup](./warmup) — what happens inside warm commands
- [Invalidation](./invalidation) — observer vs manual clear
- [Per-Module Setup](./per-module-setup) — when to run graph rebuild

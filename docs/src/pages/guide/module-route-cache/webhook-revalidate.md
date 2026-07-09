---
sidebarPos: 9
sidebarTitle: Webhook Revalidate
outline: deep
---

# Cache Revalidate Webhook

HMAC-signed HTTP endpoint for external systems (CDN, deploy hooks, CMS workers) to **purge** and/or **warm** Modularous `presentationItem` caches without blocking the caller.

Disabled by default.

## Endpoint

```
POST /api/modularous/cache/revalidate
```

### Headers

| Header | Value |
|--------|-------|
| `Content-Type` | `application/json` |
| `X-Modularous-Signature` | `sha256=<hex>` HMAC of raw request body |

### Body

```json
{
  "module": "PrimaryPage",
  "route": "Home",
  "id": 42,
  "action": "both",
  "types": ["presentationItem"],
  "locales": ["en"]
}
```

| Field | Required | Values |
|-------|----------|--------|
| `module` | yes | StudlyCase module name |
| `route` | yes | StudlyCase route name |
| `id` | no | Record ID — omit to affect entire route |
| `action` | yes | `purge`, `warm`, or `both` |
| `types` | no | Cache types; defaults to `presentationItem` only |
| `locales` | no | Reserved for locale-scoped warm (future) |

All requests are **queued** via `RevalidatePresentationCacheJob` on the `modularous-cache` queue.

## Configuration

```php
'webhook' => [
    'enabled' => env('MODULAROUS_RESOURCE_CACHE_WEBHOOK_ENABLED', false),
    'secret' => env('MODULAROUS_RESOURCE_CACHE_WEBHOOK_SECRET'),
],
```

| Env var | Default | Purpose |
|---------|---------|---------|
| `MODULAROUS_RESOURCE_CACHE_WEBHOOK_ENABLED` | `false` | Enable endpoint |
| `MODULAROUS_RESOURCE_CACHE_WEBHOOK_SECRET` | — | Shared HMAC secret |

When disabled, the route returns **404**. When enabled without a secret, returns **503**.

## Signature Example (PHP)

```php
$payload = json_encode([
    'module' => 'PrimaryPage',
    'route' => 'Home',
    'id' => 1,
    'action' => 'both',
    'types' => ['presentationItem'],
]);

$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

// POST with headers: Content-Type: application/json, X-Modularous-Signature: $signature
```

## Actions

| Action | With `id` | Without `id` |
|--------|-----------|--------------|
| `purge` | `purgeModelCacheTypes` + relation tag flush | `invalidateAllItemCaches` (no warmup) |
| `warm` | `WarmPresentationItemJob` | `warmModuleRouteCaches` |
| `both` | Purge then warm for record | Route-level purge then warm |

## Staging Pilot Notes

1. Set `MODULAROUS_RESOURCE_CACHE_WEBHOOK_ENABLED=true` and a strong `MODULAROUS_RESOURCE_CACHE_WEBHOOK_SECRET` in staging only.
2. Ensure a Horizon/worker process listens on `modularous-cache`.
3. After CMS publish, call webhook with `action=both` for the affected module/route/id.
4. Verify `X-Modularous-Cache: presentationItem=HIT` on the next browser request.
5. Do **not** enable in production until SWR + `presentationItem` pilots are validated.

## See Also

- [SWR](./swr) — stale-while-revalidate flow
- [Queue Observer](./queue-observer) — worker setup
- [CMS Public Pages](./cms-public-pages) — public cache boundary

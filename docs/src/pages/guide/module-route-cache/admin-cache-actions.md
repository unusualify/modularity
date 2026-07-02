---
sidebarPos: 6
sidebarTitle: Admin Cache Actions
---

# Admin Cache Actions

Superadmin-only purge/warm controls for routes with `admin_cache_actions: true`.

## Enabling

```php
'PackageCountry' => [
    'manual_purge' => true,
    'admin_cache_actions' => true,
    'purge' => ['presentationItem' => true],
],
```

Add `ResourceCacheActionsTrait` to the route repository. `BaseController` already includes `ManageResourceCache`.

## Endpoints

| Method | Path | Scope | Action |
|--------|------|-------|--------|
| `POST` | `{route}/cache/purge/{id}` | record | Purge selected types |
| `POST` | `{route}/cache/warm/{id}` | record | Refresh (skip invalidation) |
| `POST` | `{route}/cache/purge-all` | index | Flush route tags |
| `POST` | `{route}/cache/warm-all` | index | Queue `WarmModuleRouteCachesJob` |

Request body:

```json
{ "types": ["presentationItem"] }
```

or `{ "types": "all" }`.

Response:

```json
{ "queued": true, "message": "...", "types": ["presentationItem"] }
```

## UI actions

`ResourceCacheActionsTrait` registers form buttons (per record) and table toolbar buttons (route-wide), following the same pattern as `RemoteApiSourceTrait`:

- **Clear cache** / **Refresh cache** on the edit form
- **Clear all cache** / **Refresh all cache** on the index (with confirmation modal)

Buttons appear only when cache is enabled, `admin_cache_actions` is true, and the user is superadmin.

## Artisan parity

```bash
php artisan modularous:cache:clear BusinessPackage PackageCountry --presentationItems
php artisan modularous:cache:warm BusinessPackage PackageCountry --presentationItems
```

## Related

- [Manual Purge](./manual-purge)
- [Console Commands](./console-commands)

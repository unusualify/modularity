---
sidebarPos: 5
sidebarTitle: Manual Purge
---

# Manual Purge Configuration

Some public CMS routes (heavy `presentationItem` renders) should **not** auto-invalidate on every save. Editors publish content first, then purge/warm cache explicitly.

## Global defaults

```php
// config/merges/cache.php
'manual_purge' => false,
'observer' => [
    'auto_invalidate' => true,
],
```

## Per-route override

In `config/modularous.php`:

```php
'BusinessPackage' => [
    'routes' => [
        'PackageCountry' => [
            'manual_purge' => true,
            'admin_cache_actions' => true,
            'types' => [
                'presentationItem' => true,
            ],
            'purge' => [
                'presentationItem' => true,  // manual — observer skips
                'formItem' => false,         // auto — observer continues
                'formattedItem' => false,
                'counts' => false,
            ],
        ],
    ],
],
```

## Resolution (`shouldAutoInvalidate`)

`ModularousCache::shouldAutoInvalidate($module, $route, $type)` merges:

1. Global `observer.auto_invalidate`
2. Route `manual_purge` (falls back to global `manual_purge`)
3. Per-type `purge[$type]` when present

When a type is manual:

- `created` / `updated` / `restored` → observer **skips** invalidate/warm for that type
- `deleted` / `forceDeleted` → still invalidates enabled types (no warmup)
- Dependent routes with `shouldWarm: true` → warmup still runs (often queued)

## Pilot routes (b2press-cms)

- `BusinessPackage/PackageCountry`
- `PrimaryPage/CountryPackagesHub`

Both use manual `presentationItem` with admin cache buttons enabled.

## Related

- [Admin Cache Actions](./admin-cache-actions)
- [Queue Observer](./queue-observer)

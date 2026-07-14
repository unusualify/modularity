---
sidebarPos: 17
sidebarTitle: Refresh
---

# Refresh

> Republish Modularous frontend assets to `public/vendor/modularous` and clear view/application caches.

## Command Information

- **Signature:** `modularous:refresh`
- **Category:** Module

## What It Does

1. Deletes `public/vendor/modularous` entirely.
2. Runs `vendor:publish --provider=LaravelServiceProvider --tag=modularous-assets --force` to copy fresh assets.
3. Calls `cache:clear` and `view:clear`.

Run this after upgrading the Modularous package to ensure the browser receives the updated JS/CSS files.

## Example

```bash
php artisan modularous:refresh
```

## Related

- [build](./build) — rebuild custom Vue assets
- [get:version](./get-version) — confirm the installed Modularous version

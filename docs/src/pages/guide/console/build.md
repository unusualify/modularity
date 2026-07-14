---
sidebarPos: 3
sidebarTitle: Build
---

# Build

> Build Modularous Vue assets with custom components, themes, and Inertia pages.

## Command Information

- **Signature:** `modularous:build`
- **Alias:** `build:modularous`
- **Category:** Assets

## Options

| Option | Description |
|--------|-------------|
| `--noInstall` | Skip `npm ci` and reuse existing dependencies |
| `--hot` | Start the Vite dev server with hot reload |
| `-w`, `--watch` | Run the asset watcher |
| `-c`, `--copyOnly` | Copy custom components/pages/themes without building |
| `--copyComponents` | Copy only custom Vue components |
| `--copyInertiaPages` | Copy only custom Inertia pages |
| `--copyTheme` | Copy only the selected custom theme styles |
| `--copyThemeScript` | Copy only the selected custom theme script |
| `--theme=` | Theme name when using copy-theme options |

## What It Does

1. Installs npm dependencies in the Modularous Vue package (unless `--noInstall`).
2. Publishes and copies custom components, Inertia pages, and themes from the host app and modules.
3. Runs `npm run build`, `npm run dev`, or `npm run watch` depending on the selected mode.
4. Calls [`modularous:refresh`](./refresh) after a production build to republish compiled assets.

## Examples

```bash
php artisan modularous:build
php artisan modularous:build --hot
php artisan modularous:build --watch
php artisan modularous:build --copyOnly
php artisan modularous:build --copyTheme --theme=my-theme
```

## Related

- [refresh](./refresh) — republish compiled assets to `public/vendor/modularous`
- [ModularousVite](/system-reference/backend/support/modularous-vite) — how published assets are loaded in Blade

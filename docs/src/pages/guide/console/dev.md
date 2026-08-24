---
sidebarPos: 2
sidebarTitle: Dev
---

# Dev

> Start Modularous frontend development with hot reload.

## Command Information

- **Signature:** `modularous:dev`
- **Alias:** `modularity:dev`
- **Category:** Assets

## Options

| Option | Description |
|--------|-------------|
| `--noInstall` | Skip `npm ci`; reuse existing `node_modules` (recommended for daily use) |

## What it does

Equivalent to:

```bash
php artisan modularous:build --hot --noInstall   # when --noInstall is passed
php artisan modularous:build --hot               # without the flag
```

1. Copies custom Vue components, Inertia pages, and the active custom theme from your app
2. Starts file watchers for app overrides (including theme Sass and JS when `VUE_APP_THEME` is a custom theme)
3. Runs the Vite dev server (`npm run dev`) with HMR

**Prefer this command** while developing custom themes, auth components, or other app-level frontend overrides.

## Examples

```bash
# Daily development (recommended)
php artisan modularous:dev --noInstall

# First run or after dependency changes
php artisan modularous:dev
```

## Related

- [Build](./build) — production compile (`modularous:build --noInstall`)
- [Custom Themes — Dev & build](/guide/custom-themes/developing) — theme workflow

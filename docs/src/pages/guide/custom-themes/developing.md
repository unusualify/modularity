---
sidebarPos: 2
sidebarTitle: Dev & build
---

# Developing your theme

After scaffolding a theme and setting `VUE_APP_THEME` in `.env`, use the commands below to sync your app files and run the frontend.

## Daily development (recommended)

```bash
php artisan modularous:dev --noInstall
```

This is the **preferred command while working on a theme**. It:

1. Copies your custom theme (and other app overrides) from `resources/vendor/modularous/` into the frontend build
2. Starts the **Vite dev server** with hot module reload (HMR)
3. Watches theme files — when you save `{name}.js` or files under `themes/{name}/sass/`, copies run automatically (for the active custom theme)

Leave this process running in a terminal while you edit `_abstract.scss`, `{name}.js`, or `sass/icons/`. Changes usually appear in the browser without a manual rebuild.

### `--noInstall`

Skip `npm ci` and reuse existing `node_modules` in the Modularous Vue package. **Use this on every day** once dependencies are installed — it starts much faster.

Run **without** `--noInstall` the first time (or after dependency updates):

```bash
php artisan modularous:dev
```

## Production / compiled assets

When you need a **production build** (deploy, CI, or no dev server):

```bash
php artisan modularous:build --noInstall
```

This:

1. Copies custom components, pages, and your theme (same as dev)
2. Runs a full Vite **production build**
3. Publishes compiled assets to `public/vendor/modularous` (via `modularous:refresh`)

Use `--noInstall` here too when `node_modules` is already present.

First-time or after package upgrade:

```bash
php artisan modularous:build
```

## Quick comparison

| | `modularous:dev --noInstall` | `modularous:build --noInstall` |
|--|------------------------------|--------------------------------|
| **When** | Local theme/UI work | Deploy, staging, CI |
| **Vite** | Dev server + HMR | Production compile |
| **Process** | Keep terminal open | Exits when done |
| **Theme edits** | Auto-copy while dev runs* | Re-run command after edits |

\*Requires `VUE_APP_THEME` to match your custom theme folder name.

## Typical workflow

```bash
# Once: create theme
php artisan modularous:make:theme:folder b2pressV2 --extend=b2press

# .env
# VUE_APP_THEME=b2pressV2

# Every dev session (preferred)
php artisan modularous:dev --noInstall

# Edit resources/vendor/modularous/themes/b2pressV2/ …

# Before deploy
php artisan modularous:build --noInstall
```

## Troubleshooting

**Theme changes not showing in dev**

- Confirm `VUE_APP_THEME` matches the folder name
- Ensure `modularous:dev` is still running
- Hard-refresh the browser; Sass token changes may need a full Vite reload

**Command fails on npm install**

- Run once without `--noInstall`: `php artisan modularous:dev`

**Only need to copy theme without building**

Advanced — rarely needed for theme authors:

```bash
php artisan modularous:build --copyOnly --noInstall
```

## See also

- [Build command](/guide/console/build) — all `modularous:build` options
- [Dev command](/guide/console/dev) — `modularous:dev` alias

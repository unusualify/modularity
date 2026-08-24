---
sidebarPos: 21
sidebarTitle: make:theme
---

# make:theme

> Promote a custom theme into the built-in theme set

**Signature**: `modularous:make:theme`

**Category**: Make

---

## Description

Moves a completed custom theme from `resources/vendor/modularous/themes/{name}/` into the Modularous vendor asset paths. It copies JS and Sass files to `vue/src/js/config/themes/` and `vue/src/sass/themes/`, removes the `customs/` variants, deletes the source from `resources/`, and appends an export line to the themes `index.js` so the theme is available in the Vue build.

---

## Usage

```
modularous:make:theme [options] <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Theme name (must match the folder under `resources/vendor/modularous/themes/`) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--force` | `-f` | Overwrite existing files |

---

## Examples

```bash
php artisan modularous:make:theme mytheme
```

---

## What this command does

1. Copies `resources/vendor/modularous/themes/mytheme/mytheme.js` → `vue/src/js/config/themes/mytheme.js`
2. Copies `resources/vendor/modularous/themes/mytheme/sass/` → `vue/src/sass/themes/mytheme/`
3. Deletes `vue/src/js/config/themes/customs/mytheme/` and `vue/src/sass/themes/customs/mytheme/`
4. Deletes `resources/vendor/modularous/themes/mytheme/`
5. Appends to `vue/src/js/config/themes/index.js`:
   ```js
   export { default as mytheme } from './mytheme'
   ```

---

## Prerequisites

Run [`make:theme:folder`](./theme-folder) first to create and customize the theme.

---

## See also

- [make:theme:folder](./theme-folder) — create the theme working folder
- [System Reference](/guide/console/make/overview)

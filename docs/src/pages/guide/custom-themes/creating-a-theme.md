---
sidebarPos: 1
sidebarTitle: Creating a theme
---

# Creating a custom theme

Use the Artisan command — no manual copy-paste from the package.

## Command

```bash
php artisan modularous:make:theme:folder b2pressV2
```

**Alias:** `modularous:create:theme`

### Choose a base theme

```bash
php artisan modularous:make:theme:folder b2pressV2 --extend=b2press
```

If you omit `--extend`, the command asks which built-in theme to copy (default: `unusualify`).

### Output

```
resources/vendor/modularous/themes/b2pressV2/
├── sass/
│   ├── _abstract.scss
│   ├── _settings.scss
│   ├── _additional.scss
│   ├── _mixins.scss
│   ├── main.scss
│   └── icons/
└── b2pressV2.js
```

All further work happens in **`resources/vendor/modularous/themes/b2pressV2/`**.

## Workflow

| Step | What to do |
|------|------------|
| 1 | `php artisan modularous:make:theme:folder {name} --extend=b2press` |
| 2 | Edit `{name}.js` and `sass/_abstract.scss` (and `sass/icons/` if needed) |
| 3 | Set `VUE_APP_THEME={name}` in `.env` |
| 4 | `php artisan modularous:dev --noInstall` — keep running while you edit ([details](./developing)) |
| 5 | Before deploy: `php artisan modularous:build --noInstall` |

## First edits after scaffold

| File | Start here |
|------|------------|
| `b2pressV2.js` | `colors.primary`, `colors.secondary`, grey scale |
| `sass/_abstract.scss` | `$body-font-family`, `$button-border-radius`, `$button-height` |
| `sass/icons/` | Replace logos (SVG) |
| `sass/main.scss` | Google Fonts `@import url(...)` |

See [Sass](./sass), [Colors (JS)](./colors), [Icons](./icon-sets).

## Options

| Option | Description |
|--------|-------------|
| `--extend=` | Built-in theme to copy as starting point |
| `--force` | Overwrite if folder already exists |

Full reference: [make:theme:folder](/guide/console/make/theme-folder).

## Troubleshooting

**Theme not visible**

- `VUE_APP_THEME` must match the folder name exactly (`b2pressV2` → folder `themes/b2pressV2/`)
- Run `php artisan modularous:dev --noInstall` (or `modularous:build --noInstall` for production)

See [Dev & build](./developing) for more.

**Folder already exists**

- Use `--force` or pick a new name

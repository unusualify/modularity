---
sidebarPos: 6
sidebarTitle: Custom Themes
---

# Custom Themes

Your Laravel app can ship its own Vuetify theme — colors, typography, component sizing, and brand icons — without modifying Modularous package code.

Everything lives under **`resources/vendor/modularous/themes/{name}/`**.

Activate the theme in `.env`:

```env
VUE_APP_THEME=b2pressV2
```

After changing the theme, use [`modularous:dev --noInstall`](/guide/custom-themes/developing) while working locally, or [`modularous:build --noInstall`](/guide/custom-themes/developing) for a production compile.

## What you can change (in your app)

| What | Where in your app |
|------|-------------------|
| Brand colors (`primary`, `secondary`, grey scale) | `themes/{name}/{name}.js` |
| Button/input size, fonts, border-radius | `themes/{name}/sass/_abstract.scss` |
| Google Fonts, table layout tweaks | `themes/{name}/sass/main.scss` |
| Logos, OAuth icons | `themes/{name}/sass/icons/*.svg` |
| Extra global CSS | bottom of `themes/{name}/sass/main.scss` |

Sass handles **sizes and shapes** (compile-time). The JS file handles **colors** (runtime). See [Overview](./overview).

## Quick start

```bash
php artisan modularous:make:theme:folder b2pressV2 --extend=b2press

# Edit resources/vendor/modularous/themes/b2pressV2/

# .env → VUE_APP_THEME=b2pressV2

# Development (recommended — hot reload)
php artisan modularous:dev --noInstall
```

Details: [Creating a theme](./creating-a-theme) · [Dev & build](./developing).

## Documentation

- [Creating a theme](./creating-a-theme) — Artisan scaffold command
- [Dev & build](./developing) — `modularous:dev` vs `modularous:build`
- [Overview](./overview) — Sass vs JS, what each file does
- [Sass](./sass) — `_abstract.scss`, `main.scss`, tokens
- [Colors (JS)](./colors) — `{name}.js` palette
- [Icons](./icon-sets) — theme SVG icons

## Related

- [Custom Auth Pages](/guide/custom-auth-pages/) — uses the active theme automatically

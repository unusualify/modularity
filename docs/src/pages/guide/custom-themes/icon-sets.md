---
sidebarPos: 6
sidebarTitle: Icons
---

# Icons

## Theme icons (your app)

Put SVG files in:

```
resources/vendor/modularous/themes/{name}/sass/icons/
```

Examples:

- `main-logo-light.svg`, `main-logo-dark.svg` — auth / sidebar logos
- `google.svg`, `apple.svg` — social login buttons
- `mini-logo-light.svg` — compact header mark

After adding or changing icons, save while **`modularous:dev --noInstall`** is running, or run `php artisan modularous:build --noInstall`.

### How to reference

Sprite id = filename without extension, prefixed with `icon--`:

| File | Sprite id |
|------|-----------|
| `icons/google.svg` | `icon--google` |
| `icons/main-logo-light.svg` | `icon--main-logo-light` |

In Vue (with [v-svg](/guide/directives/svg)):

```vue
<span v-svg="'main-logo-light'" />
```

Or direct SVG use:

```html
<svg><use href="#icon--google" /></svg>
```

Theme icons **switch with `VUE_APP_THEME`** — each theme folder has its own `icons/` directory.

### Tips

- Use optimized, simple SVGs
- Prefer single-color paths when you need CSS `fill` control
- Filename becomes the id — use kebab-case (`main-logo-light.svg`)

## Standard UI icons (built-in)

Dashboard actions, CRUD buttons, and navigation use **Material Design Icons** via Vuetify — you don't add these to your theme folder.

```vue
<v-icon icon="mdi-pencil-box-outline" />
<v-btn prepend-icon="mdi-trash-can-outline">Delete</v-btn>
```

Modularous also registers short aliases where configured (e.g. `icon="dashboard"`, `icon="edit"`). These look the same regardless of your custom theme.

| Use case | Where |
|----------|-------|
| Brand logo, OAuth mark, theme-specific art | Your `themes/{name}/sass/icons/` |
| Edit, delete, menu, filter, dashboard | Vuetify / MDI (`v-icon`, `prepend-icon`) |

## Related

- [v-svg directive](/guide/directives/svg)
- [Creating a theme](./creating-a-theme) — scaffold includes an `icons/` folder from the base theme

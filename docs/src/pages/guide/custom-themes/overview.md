---
sidebarPos: 3
sidebarTitle: Overview
---

# Overview

A custom theme is a folder in your app. Modularous reads it when `VUE_APP_THEME` matches the folder name and after `modularous:build` syncs it into the frontend build.

## Your theme folder

Created by [`modularous:make:theme:folder`](/guide/custom-themes/creating-a-theme):

```
resources/vendor/modularous/themes/b2pressV2/
├── b2pressV2.js          ← Vuetify color palette
└── sass/
    ├── _abstract.scss    ← design tokens (button radius, heights, fonts)
    ├── _typography.scss  ← optional `$typography` overlay (Figma / MD3)
    ├── _settings.scss    ← usually leave as scaffolded
    ├── _additional.scss
    ├── _mixins.scss
    ├── main.scss         ← fonts, optional global CSS
    └── icons/            ← your SVG logos & brand icons
```

You only edit files **inside this folder** (plus `.env`).

## Two channels: Sass and JS

| Channel | File | Effect | Example |
|---------|------|--------|---------|
| **Sass** | `sass/_abstract.scss` | Component **size & shape** in compiled CSS | Pill buttons via `$button-border-radius: 9999px` |
| **JS** | `{name}.js` | **Colors** as Vuetify CSS variables | `colors.primary: '#05979B'` |

They work together:

```vue
<v-btn color="primary">Save</v-btn>
```

- `color="primary"` → value from **`b2pressV2.js`**
- Button height and border-radius → values from **`sass/_abstract.scss`**

## Simple flow

```
1. php artisan modularous:make:theme:folder mytheme --extend=b2press
2. Edit resources/vendor/modularous/themes/mytheme/
3. VUE_APP_THEME=mytheme in .env
4. php artisan modularous:dev --noInstall    ← daily development (hot reload)
5. php artisan modularous:build --noInstall  ← before deploy
```

See [Dev & build](./developing) for command details.

## Which file for which change?

| I want to… | Edit |
|------------|------|
| Change primary / secondary brand color | `{name}.js` → `colors` |
| Change grey scale / error / success colors | `{name}.js` → `colors` |
| Pill or rounded buttons globally | `sass/_abstract.scss` → `$button-border-radius` |
| Taller buttons or inputs | `sass/_abstract.scss` → `$button-height`, `$input-control-height` |
| App font (e.g. Roboto) | `sass/_abstract.scss` → `$body-font-family` + font `@import` in `main.scss` |
| Type scale (Figma MD3 roles) | `sass/_typography.scss` + `$typography` / `$font-size: 100%` in `_abstract.scss` |
| Logo on auth / sidebar | `sass/icons/*.svg` |
| One-off styling on a single page | That Vue component's scoped CSS |

## Built-in base vs your custom theme

When you run `make:theme:folder`, you pick a **built-in base** (`--extend=b2press`, `unusualify`, …). That base is only the starting copy — your folder in `resources/vendor/modularous/themes/` is what you maintain.

You do **not** need to copy files by hand or edit anything inside the Modularous vendor package.

## Next steps

- [Creating a theme](./creating-a-theme) — scaffold command
- [Sass](./sass) — token reference
- [Colors (JS)](./colors) — `{name}.js` structure
- [Icons](./icon-sets) — adding SVGs to your theme

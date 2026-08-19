# Sass / Theme Instructions

Modularous styles are built on **Vuetify 3 SASS variables** plus a **core abstract token layer**. Custom app themes live under `resources/vendor/modularous/themes/{name}/` and are copied into this package during `php artisan modularous:build`.

**Copy direction** (app → package):

| App source | Package destination |
|---|---|
| `resources/vendor/modularous/themes/{name}/sass/` | `vue/src/sass/themes/customs/{name}/` |
| `resources/vendor/modularous/themes/{name}/{name}.js` | `vue/src/js/config/themes/customs/{name}.js` |

Set the active theme in `.env`:

```
VUE_APP_THEME=b2pressV2
```

Restart Vite after changing `VUE_APP_THEME`. During development, use `php artisan modularous:dev --noInstall` instead of manual restarts.

---

## Theme types

| Type | Sass path | JS colors path | Activation |
|---|---|---|---|
| **Built-in** | `sass/themes/{name}/` | `js/config/themes/{name}.js` | Listed in `themes/index.js` |
| **Custom (app)** | `sass/themes/customs/{name}/` | `js/config/themes/customs/{name}.js` | Auto-registered by Vite plugin when `{name}.js` exists in `customs/` |

Custom themes are detected by `vite-plugin-modularous.js` (`isCustomTheme()`).

---

## Custom theme file roles

Each theme folder should contain:

```
themes/customs/{name}/
├── _abstract.scss      # Design tokens → forwarded to core/abstract
├── _typography.scss    # Optional `$typography` overlay (Figma / MD3 roles)
├── _settings.scss      # Vuetify compile-time settings (vite-plugin-vuetify configFile)
├── _additional.scss    # Auto-injected into every Vue SFC style block
├── _mixins.scss        # Theme mixins (forwarded via _additional)
├── main.scss           # Full stylesheet entry (colors, base overrides, fonts)
└── icons/              # SVG icons for vite-plugin-svg-spritemap
```

### `_abstract.scss` — primary override point

Forward core abstract with theme-specific token values:

```scss
@forward 'styles/core/abstract' with(
  $body-font-family: 'Roboto',
  $font-size: 100%,
  $button-border-radius: 9999px,  // pill buttons (b2pressV2)
);
```

**Prefer `_abstract.scss`** for single design tokens that core already exposes. Values flow into `core/_settings.scss` → `@use 'vuetify/settings' with (...)`.

### `_settings.scss` — Vuetify compile config

Used as Vite `configFile` for `vite-plugin-vuetify`:

```js
// vite.config.mjs
Vuetify({ styles: { configFile: 'src/sass/themes/customs/b2pressV2/_settings.scss' } })
```

Default pattern delegates to core:

```scss
@use 'abstract' as *;
@use 'styles/core/settings';
```

For **advanced** Vuetify-only overrides not wired through abstract tokens, replace the delegate with a direct forward (see commented template inside existing theme `_settings.scss` files):

```scss
@use 'abstract' as *;

@forward 'vuetify/settings' with (
  $button-height: $button-height,
  $button-font-size: $button-font-size,
  // …include all vars from core/_settings.scss…
  $some-vuetify-only-var: 12px,
);
```

### `main.scss` — runtime stylesheet + legacy theme-config

Imported automatically into `vuetify.js` by the Vite plugin:

```scss
@use 'abstract';
@use 'styles/core/base' with (
  $theme-config: (
    'primary-color': #0056b3,
    'table-row-height': 49px,
  )
);
@import url(https://fonts.googleapis.com/css?family=Roboto:…);
```

Use for Google Fonts, `$theme-config` map overrides, and ad-hoc global CSS at the bottom.

### `_additional.scss` — SFC helper scope

Injected globally via `sass-preprocessor-options.mjs` into every component `<style lang="scss">` block:

```scss
@forward 'abstract';
@forward 'mixins';
```

Use `_mixins.scss` for theme-specific mixins available inside Vue SFCs without manual imports.

### `{name}.js` — Vuetify color theme (JS)

Runtime palette (`primary`, `secondary`, `grey`, opacity variables). Lives in `js/config/themes/customs/{name}.js`, not in sass — but belongs to the same theme bundle. See `js/AGENTS.md` for Vue/component guidance.

---

## Override layers (when to use what)

| Goal | Where | Scope |
|---|---|---|
| Button height, font, border-radius, input height | `_abstract.scss` | Theme-specific, compile-time |
| Any Vuetify setting not in abstract | `_settings.scss` direct `@forward 'vuetify/settings'` | Theme-specific, compile-time |
| Global radius for cards/fields/chips too | `$border-radius-root` in `_abstract.scss` | Wider blast radius — use carefully |
| Table row height, legacy CSS vars | `main.scss` → `$theme-config` | Theme-specific |
| Google Fonts | `main.scss` `@import url(...)` | Theme-specific |
| Component default props (`variant`, `density`, `rounded`) | `js/plugins/vuetify.js` | **All themes** unless branched on `APP_THEME` |
| One-off layout styling | Component scoped CSS | Local only |

**Rule of thumb:** visual tokens → sass `_abstract.scss`; behavioral props → `vuetify.js` defaults/aliases; page-specific exceptions → component CSS.

---

## Vuetify 4 — viewport / height

Vuetify 4 height utilities (`h-50`, `h-100`) live in `@layer` without `!important`. Unlayered inline styles beat them. `v-main` is `flex: 1 0 auto`, so child percentage heights often compute as auto.

Do **not** restore fill with global `height: calc(97vh)` (or similar) in theme / core CSS. Viewport fit is a flex-column job (`flex-grow-1 min-height-0`), not a Sass height hack. Canonical table and hard rules: `vue/src/js/AGENTS.md`.

---

## Core tokens available in `_abstract.scss`

Defined in `sass/core/abstract/_variables.scss`. Common overrides:

**Buttons**
- `$button-height`, `$button-min-width`, `$button-max-width`
- `$button-font-size`, `$button-font-weight`, `$button-icon-font-size`
- `$button-density`
- `$button-border-radius` (defaults to `$border-radius-root`; set `9999px` for pill)

**Inputs & fields**
- `$input-font-size`, `$input-control-height`, `$input-line-height`, `$input-density`
- `$field-font-size`, `$field-control-height`, `$field-label-floating-scale`
- `$field-border-radius`, `$text-field-border-radius` (independent of `$button-border-radius`)
- `$field-outline-opacity` (outlined border fade; default `0.38`)
- `$field-control-padding-start/end/top/bottom`

**Global / Vuetify root**
- `$body-font-family`, `$heading-font-family`, `$font-size`, `$font-size-root`, `$line-height-root`
- `$typography` — overlay for Vuetify MD3 type roles; `()` keeps framework defaults (deep-merged in `vuetify/settings`)
- `$border-radius-root` (affects cards, chips, fields — not just buttons)
- `$spacer`

**Sidebar**
- `$sidebar-padding`, `$sidebar-icon-size`, `$sidebar-list-item-overlay-color`, …

**Lists**
- `$list-item-nav-subtitle-font-size`

Core forwards these into `vuetify/settings` via `sass/core/_settings.scss`. Check that file for the full list of wired Vuetify variables.

---

## Example: b2pressV2 pill buttons

`_abstract.scss`:

```scss
@forward 'styles/core/abstract' with(
  $body-font-family: 'Roboto',
  $font-size: 100%,
  $button-border-radius: 9999px,
);
```

All `v-btn` instances get pill radius when `VUE_APP_THEME=b2pressV2`. No per-component `rounded="pill"` needed.

To affect cards/inputs radius separately, override `$border-radius-root` instead of (or in addition to) `$button-border-radius`.

---

## Icons

Place SVGs in `themes/customs/{name}/icons/`. Vite builds a spritemap from:

```
./src/sass/themes/customs/{name}/icons/**/*.svg
```

Generated SCSS: `~svg-sprite-icons-theme.scss` (package root).

---

## Directory map

```
sass/
├── AGENTS.md                 ← this file
├── core/
│   ├── abstract/             # Shared design tokens (_variables.scss)
│   ├── _settings.scss        # Core → vuetify/settings bridge
│   ├── base.scss             # Full Vuetify + component styles entry
│   ├── components/           # Global component overrides
│   ├── directives/
│   └── mixins/
└── themes/
    ├── unusualify/           # Built-in default theme
    └── customs/
        └── {name}/           # App-provided custom themes
```

---

## Workflow checklist

1. `php artisan modularous:make:theme:folder {name} --extend=…` — scaffold ([docs](/guide/custom-themes/creating-a-theme))
2. Edit theme under `resources/vendor/modularous/themes/{name}/`
3. Set `VUE_APP_THEME={name}` in app `.env`
4. **Development:** `php artisan modularous:dev --noInstall` (hot reload)
5. **Deploy:** `php artisan modularous:build --noInstall`

See [Custom Themes — Dev & build](/guide/custom-themes/developing).

---

## Related docs

- [Custom Themes guide](/guide/custom-themes/) — app theme folder (for application developers)
- `vue/src/js/AGENTS.md` — Vue components, custom auth, build copy for JS
- `sass/core/_settings.scss` — which abstract tokens are passed to Vuetify
- `sass/core/abstract/_variables.scss` — full token list with defaults
- `vue/vite.config.mjs` — theme folder resolution, Vuetify configFile path

---
sidebarPos: 4
sidebarTitle: Sass
---

# Sass customization

Edit files under **`resources/vendor/modularous/themes/{name}/sass/`** to change component sizes, fonts, and border-radius across the UI.

Colors are **not** set here — use [`{name}.js`](./colors) instead.

## Files you edit

| File | Purpose |
|------|---------|
| **`_abstract.scss`** | Main place for design tokens — start here |
| **`_typography.scss`** | Optional Vuetify `$typography` overlay (e.g. Figma type scale) |
| **`main.scss`** | Google Fonts import, optional `$theme-config`, extra global CSS |
| **`icons/`** | Brand SVGs — see [Icons](./icon-sets) |
| `_settings.scss` | Usually keep the scaffolded default |
| `_additional.scss` / `_mixins.scss` | Advanced; rarely need changes |

## `_abstract.scss` — primary overrides

Override only what you need:

```scss
@forward 'styles/core/abstract' with(
  $body-font-family: 'Roboto',
  $font-size: 100%,
  $button-border-radius: 9999px,
  $button-height: 48px,
  $input-control-height: 48px,
);
```

Unlisted variables keep Modularous defaults from the base theme you extended.

### Common tokens

**Buttons**

| Token | Typical use |
|-------|-------------|
| `$button-height` | Global button height |
| `$button-font-size` | Button label size |
| `$button-border-radius` | `9999px` for pill buttons |
| `$button-max-width` | Max width cap |

**Inputs & fields**

| Token | Typical use |
|-------|-------------|
| `$input-control-height` | Outlined field height |
| `$input-font-size` | Input text size |
| `$field-label-floating-scale` | Floating label scale |

**Global**

| Token | Typical use |
|-------|-------------|
| `$body-font-family` | App font stack |
| `$heading-font-family` | Heading stack (defaults to `$body-font-family`) |
| `$font-size` | Root html font-size. Use `100%` (16px) if your type scale is specified in px |
| `$typography` | Overlay for Vuetify MD3 roles (`display-*`, `headline-*`, `title-*`, `body-*`, `label-*`). Empty keeps Vuetify defaults. Partial maps deep-merge. |
| `$border-radius-root` | Global radius — affects cards and fields too, not just buttons |

**Sidebar**

`$sidebar-padding`, `$sidebar-icon-size`, … — adjust if your layout needs different sidebar spacing.

## Example: pill buttons (b2pressV2)

```scss
// resources/vendor/modularous/themes/b2pressV2/sass/_abstract.scss
@forward 'styles/core/abstract' with(
  $body-font-family: 'Roboto',
  $font-size: 100%,
  $button-border-radius: 9999px,
);
```

All `v-btn` elements get pill radius without adding `rounded="pill"` in every template.

## Example: lock the type scale (b2pressV2)

Keep `$font-size: 100%` so rem values match Figma px (16px root). Put the overlay in `_typography.scss` and pass it through `_abstract.scss`:

```scss
// sass/_typography.scss — Figma 57/64 → rem at 16px root
@use 'sass:math';
@function _px($px) { @return math.div($px, 16) * 1rem; }

$b2p-roboto-typography: (
  'display-large': ('size': _px(57), 'weight': 400, 'line-height': math.div(64, 57)),
  'body-large': ('size': _px(16), 'weight': 400, 'line-height': math.div(24, 16)),
  // …remaining MD3 roles
);
```

```scss
// sass/_abstract.scss
@use 'typography' as *;

@forward 'styles/core/abstract' with(
  $body-font-family: 'Roboto',
  $font-size: 100%,
  $typography: $b2p-roboto-typography,
);
```

Utility classes (`text-display-large`, `text-body-medium`, …) pick this up via `core/_settings.scss`. Templates must use those MD3 class names — leftover `text-h1` / `text-body-1` will not follow the overlay.

## `main.scss` — fonts and extras

```scss
@use 'abstract';

@use 'styles/core/base' with (
  $theme-config: (
    'table-row-height': 49px,
    'table-header-height': 49px,
  )
);

@import url(https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap);

// Optional theme-specific global CSS below
```

Use `$theme-config` for table row heights and similar layout constants. Prefer `{name}.js` for brand **colors**.

## Sass vs JS — don't duplicate

| Property | Set in Sass (`_abstract.scss`) | Set in JS (`{name}.js`) |
|----------|-------------------------------|-------------------------|
| Button border-radius | ✅ | ❌ |
| Button height | ✅ | ❌ |
| Type scale (`$typography`) | ✅ | ❌ |
| Primary color | ❌ | ✅ |
| Hover/border opacity | ❌ | ✅ |

## Workflow

1. Edit `resources/vendor/modularous/themes/{name}/sass/`
2. With `modularous:dev --noInstall` running, saves are picked up automatically — or run `modularous:build --noInstall` for a one-off compile
3. Check in browser DevTools: `.v-btn { border-radius: … }`

See [Dev & build](./developing).

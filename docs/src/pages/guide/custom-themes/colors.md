---
sidebarPos: 5
sidebarTitle: Colors (JS)
---

# Colors (JS theme file)

Brand colors live in **`resources/vendor/modularous/themes/{name}/{name}.js`**.

This file is created when you run [`modularous:make:theme:folder`](/guide/custom-themes/creating-a-theme). Edit it in your app — sizes and border-radius belong in [Sass](./sass), not here.

## File location

```
resources/vendor/modularous/themes/b2pressV2/b2pressV2.js
```

## Structure

```js
export default {
  dark: false,
  colors: {
    background: '#F5F5F5',

    primary: '#05979B',
    'primary-lighten-1': '#33ABAE',
    'primary-darken-1': '#05868A',
    'on-primary': '#FFFFFF',

    secondary: '#F26B42',
    'on-secondary': '#FFFFFF',

    grey: '#0A415A',
    'grey-lighten-5': '#E2E8EB',

    error: '#DE3730',
    success: '#54AF4C',
    warning: '#FDB022',
    info: '#2196F3',
  },
  variables: {
    'border-opacity': 0.12,
    'high-emphasis-opacity': 0.87,
    'medium-emphasis-opacity': 0.60,
    'disabled-opacity': 0.38,
    'hover-opacity': 0.04,
    'focus-opacity': 0.12,
  },
}
```

## What each section does

| Section | Purpose |
|---------|---------|
| `dark` | `false` for light theme, `true` for dark |
| `colors.primary`, `colors.secondary` | Main brand colors — used by `color="primary"` on Vuetify components |
| `colors['primary-lighten-N']` / `darken-N` | Tonal steps for surfaces, chips, backgrounds |
| `colors['on-primary']` | Text/icon color on primary-filled surfaces |
| `colors.grey` + lighten/darken | Neutral scale for text, borders, surfaces |
| `colors.error`, `success`, `warning`, `info` | State colors |
| `variables['hover-opacity']`, etc. | Overlay and emphasis strengths |

## Usage in Vue

```vue
<v-btn color="primary">Save</v-btn>
<v-sheet color="grey-lighten-5" />
<v-alert type="error" />
```

Vuetify maps these to CSS variables (`--v-theme-primary`, …) at runtime based on your `{name}.js`.

## Workflow

1. Edit `resources/vendor/modularous/themes/{name}/{name}.js`
2. Use [dev mode](./developing) while editing, or `modularous:build --noInstall` for production
3. Verify in DevTools: `--v-theme-primary` on the active theme class

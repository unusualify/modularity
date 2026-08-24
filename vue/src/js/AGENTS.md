# Vue / Build Instructions

**Copy direction**: `modularous:build` copies app → package. App `resources/vendor/modularous/` flows into package `vue/src/`:
- `js/components/` → `components/customs/` (UeCustom*)
- `js/components/Auth.vue` → customs/ as UeCustomAuth (custom design for app-specific layouts)
- `themes/{name}/sass` → `sass/themes/customs/{name}`
- `themes/{name}/{name}.js` → `js/config/themes/customs/{name}/{name}.js`
- `themes/{name}/defaults.js` → `js/config/themes/customs/{name}/defaults.js`
- `js/Pages/` → `Pages/customs/`

## Frontend hard rules

- Vue 3 **Composition API** only (no Options API)
- Vuetify 3 components (not plain HTML)
- Helpers: `import { isObject, dataGet } from '@/utils/helpers'` — no new `window.__*` usage
- Tests colocated under package Vue test conventions when touching components
- Directives live in `vue/src/js/directives/` and register via `plugins/UEConfig.js` (`app.use(...)`) — docs: `docs/src/pages/guide/directives/`

## Form inputs (Hydrate contract)

New form input requires matching PHP Hydrate (`src/Hydrates/AGENTS.md`).

1. Create `vue/src/js/components/inputs/{Studly}.vue`
2. Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
3. Component registers as `VInput{Studly}` via inputs glob
4. Schema `type` is usually `input-{kebab}`; map via `mapTypeToComponent` / optional `hydrateTypeMap` in `registry.js`

Docs: `docs/src/pages/guide/form-inputs/`, `docs/src/pages/system-reference/hydrates.md`, `docs/src/pages/system-reference/frontend/`

---

## Vuetify 4 — viewport / height

Vuetify 4 utilities live in CSS `@layer` and no longer use `!important`. Unlayered inline styles beat `.h-50` / `.h-100`. `v-main` is `flex: 1 0 auto` (not a definite viewport height), so child `height: 50%` / `100%` often computes as **auto**. Grid `gap` adds extra vertical space vs V3. Do **not** use `height: calc(97vh)` (or similar) to fake fill — it overflows at 1440×900 and fights layered utilities.

`ue-form` `fillHeight` means flex-fill the **parent** (`d-flex flex-column flex-grow-1 min-height-0`), not 97vh. Callers that need true viewport height pass a page-local class after subtracting chrome; do not restore 97vh on `Form.vue`.

| Goal | Do | Don't |
|------|----|-------|
| Split a column 50/50 | Parent `d-flex flex-column`; children `flex-grow-1 min-height-0 d-flex flex-column` | `h-50` on stacked widgets/forms |
| Stretch to leftover space | `flex-grow-1 min-height-0` | `h-100` when parent height is auto |
| Pin button to card bottom | Flex column + `v-spacer` / `pushButtonToBottom` | `fillHeight` + 97vh |
| Page fills leftover `v-main` | Page root `d-flex flex-column flex-grow-1 min-height-0`; **one** `pa-3` | Double `pa-3` + `h-100` |
| Min size | `min-height: 160px` plus flex grow | `h-50` alone |
| True viewport height (rare) | Page-local class after subtracting chrome | Global 97vh on `Form.vue` |

Hard rules:

- Prefer flex column + `flex-grow-1 min-height-0` over `h-50` / `h-100` / 97vh for viewport fit. `h-auto`, `h-screen` (100dvh), and real content height are still fine.
- `h-100` is OK **only** if an ancestor already has a definite used height (flex item with a definite parent, or explicit px/vh on the ancestor).
- Pages / layouts / Blocks: page root is a flex column that grows into leftover `v-main`. Put `pa-3` on `#ue-main-body` **or** the page, not both.
- Dashboard `widgetAttributes.class` / `attributes.class`: same mapping — replace height-split `h-50`/`h-100` with flex classes; keep `min-height` where needed. Example: Profile cards use `flex-grow-1 min-height-0`.
- Sass layers vs height utilities: `vue/src/sass/AGENTS.md`. PHP grids (`makeGridSection`): `src/Http/AGENTS.md`.

---

## Auth Component Architecture

### Package Auth (UeAuth) – Default
- **Location**: `vue/src/js/components/Auth.vue`
- **Purpose**: Minimal, slot-based layout. No banner content props.
- **Props**: `slots`, `noDivider`, `noSecondSection`, `logoLightSymbol`, `logoSymbol`
- **Slots**: `description`, `cardTop`, default (form), `bottom`
- **Banner area**: Renders `<slot name="description" />` only when `noSecondSection` is false. No default content.
- **`inheritAttrs: false`**: Custom attributes (e.g. bannerDescription) are not applied to the root; they are intended for custom auth components.

### Custom Auth (UeCustomAuth)
- **Location**: `resources/vendor/modularous/js/components/Auth.vue` (published from package)
- **Purpose**: App-specific layouts (split layout, banner, etc.)
- **Props**: Declare any props needed (e.g. `bannerDescription`, `bannerSubDescription`, `redirectButtonText`, `redirectUrl`)
- **Activation**: Set `auth_pages.component_name` to `ue-custom-auth` in app config (e.g. `modularous/auth_pages.php`)

### Attribute Flow
- Layout passes `v-bind='@json($attributes)'` to the auth component.
- `$attributes` are built from: `auth_pages.layout` + `layoutPreset` + `auth_pages.attributes` + `pages.[key].attributes`.
- **Full flexibility**: Custom auth components receive all attributes. Add any props in `modularous/auth_pages.php` under `attributes` or `pages.[page].attributes`.
- Banner-related attributes (`bannerDescription`, `bannerSubDescription`, `redirectButtonText`) are app-provided and used only by custom auth components, not by the package Auth.vue.

---

## Legacy Auth
Run `php artisan vendor:publish --tag=modularous-auth-legacy` to get the legacy Auth design. Set `auth_component.useLegacy => true` in config.

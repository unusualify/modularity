# Vue / Build Instructions

**Copy direction**: `modularous:build` copies app → package. App `resources/vendor/modularous/` flows into package `vue/src/`:
- `js/components/` → `components/customs/` (UeCustom*)
- `js/components/Auth.vue` → customs/ as UeCustomAuth (custom design for app-specific layouts)
- `themes/{name}/sass` → `sass/themes/customs/{name}`
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

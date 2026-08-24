---
sidebarPos: 4
sidebarTitle: Custom Auth Component
---

# Custom Auth Component

Use a custom Auth component when you need app-specific layouts (split layout, banner, custom branding) that the package default does not provide.

## Enabling Custom Auth

1. **Publish the Auth component** (if not already):

```bash
php artisan vendor:publish --tag=modularous-auth-legacy
```

This copies `Auth.vue` to `resources/vendor/modularous/js/components/Auth.vue`.

2. **Set component name** in `modularous/auth_pages.php`:

```php
return [
    'component_name' => 'ue-custom-auth',
    'attributes' => [
        'bannerDescription' => __('authentication.banner-description'),
        'bannerSubDescription' => __('authentication.banner-sub-description'),
        'redirectButtonText' => __('authentication.redirect-button-text'),
    ],
];
```

3. **Build assets** so the custom component is included:

```bash
php artisan modularous:build
```

## Custom Auth Structure

The layout blade renders:

```blade
<{{ $authComponentName }} v-bind='@json($attributes)'>
    <ue-form v-bind='...'>...</ue-form>
    <template v-slot:bottom>...</template>
</{{ $authComponentName }}>
```

Your custom Auth.vue receives:
- **Props**: All keys from `$attributes` that you declare as props
- **Slots**: `cardTop`, default (form content), `bottom`, `description`

## Required Slots

| Slot | Purpose |
|------|---------|
| default | Form content (ue-form) — provided by layout |
| `cardTop` | Optional content above form |
| `bottom` | Optional content below form (OAuth buttons, links) |
| `description` | Banner/right-section content (when using split layout) |

## Example: Split Layout with Banner

Do **not** wrap the form in `v-layout` / `v-main`. Vuetify 4’s `VMain` delays default-slot rendering; nested `<slot>` inside `v-main` can crash Vue (`renderSlot` / `currentRenderingInstance.ce`). Auth pages have no drawers, so a native `<main>` is enough — same pattern as the package `ue-auth` component.

```vue
<template>
  <v-app>
    <main class="auth-main">
      <v-row>
        <!-- Left: form -->
        <v-col cols="12" md="6">
          <ue-svg-icon :symbol="lightSymbol" />
          <slot name="cardTop" />
          <v-sheet :style="{ width }">
            <slot />
          </v-sheet>
          <div v-if="!noDivider && $slots.bottom">
            <v-divider />
            <span>{{ dividerText }}</span>
            <v-divider />
          </div>
          <slot name="bottom" />
        </v-col>
        <!-- Right: banner -->
        <v-col v-if="!noSecondSection" cols="12" md="6" class="bg-primary">
          <slot name="description">
            <h2>{{ bannerDescription }}</h2>
          </slot>
          <v-btn v-if="redirectUrl" :href="redirectUrl">
            {{ redirectButtonText }}
          </v-btn>
        </v-col>
      </v-row>
    </main>
  </v-app>
</template>
```

## Reading Config in Vue

Auth components can read `window.__MODULAROUS_AUTH_CONFIG__` (or `window.MODULAROUS?.AUTH_COMPONENT`) for:

- `formWidth` — form width by breakpoint
- `dividerText` — divider label
- `layout`, `banner` — class overrides

```js
const config = window.__MODULAROUS_AUTH_CONFIG__ || {}
const width = config.formWidth?.[breakpoint] ?? '450px'
```

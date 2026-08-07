---
sidebarPos: 2
sidebarTitle: Viewport Fit
---

# v-viewport-fit

Fits an element to the **leftover viewport** below dynamic chrome (breadcrumbs, alerts, filters). Measures from the bound element's top to the viewport (or nearest scroll parent) bottom, so margins are included.

**File:** `vue/src/js/directives/viewport-fit.js`

Prefer a **plain wrapper `div`**, not `v-data-table` itself. Vuetify's `height` prop only sizes the scrollable table pane; the pagination footer sits below it.

## Basic example

```vue
<script setup>
import { computed, ref } from 'vue'

const tableHeight = ref(360)
/** Vuetify footer (~pagination) sits outside `height` — reserve space */
const tableBodyHeight = computed(() => Math.max(160, tableHeight.value - 64))

const viewportFitOptions = {
  chrome: '.page__chrome',
  heightRef: tableHeight,
  offset: 12,
  min: 240,
}
</script>

<template>
  <div
    class="page"
    data-viewport-fit-root
  >
    <div class="page__chrome">
      <!-- breadcrumbs, alerts, filters -->
    </div>

    <div
      v-viewport-fit="viewportFitOptions"
      class="page__table-shell"
    >
      <v-data-table
        :items="items"
        :headers="headers"
        fixed-header
        :height="tableBodyHeight"
      />
    </div>
  </div>
</template>
```

## Binding options

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `chrome` | selector \| Element \| Ref | previous sibling | Region to observe for reflow |
| `offset` | number | `16` | Gap below the fill target (px) |
| `min` | number | `240` | Minimum height (px) |
| `heightRef` | `Ref<number>` | — | Reactive sink for Vuetify `:height` |
| `onUpdate` | `(h: number) => void` | — | Callback when height changes |
| `applyStyle` | boolean | `true` | Set `height` / `max-height` on the element (`false` to skip) |
| `cssVar` | boolean \| string | — | Set `--viewport-fit-height` (or custom name) |

## Modifiers

| Modifier | Effect |
|----------|--------|
| `.style` | Force applying inline height styles |
| `.css-var` | Set `--viewport-fit-height` on the element |

```vue
<div v-viewport-fit.css-var="{ chrome: '.page__chrome', min: 200 }" />
```

## Tips

- Put `data-viewport-fit-root` on the page root so selector lookups stay scoped.
- Omit `chrome` to use the previous sibling of the fill element.
- After async data loads, a `resize` event (or chrome size change) remeasures automatically via `ResizeObserver`.
- For `v-data-table`: shell height = leftover viewport; `:height` = shell − footer (~64px) so pagination stays visible.

## See also

- [Directives overview](./overview)
- Live usage: `vue/src/js/Pages/ModuleRouteInspect.vue`

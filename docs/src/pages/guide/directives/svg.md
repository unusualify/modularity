---
sidebarPos: 6
sidebarTitle: SVG
---

# v-svg

Injects an SVG sprite icon into the element via `addSvg` / `removeSvg` helpers.

**File:** `vue/src/js/directives/svg.js`

## Example

```vue
<span v-svg="'icon-dashboard'" />
```

Re-applies when the binding changes (`updated` removes then re-adds).

Prefer package SVG helpers / Vuetify icons for new UI unless you specifically need sprite injection.

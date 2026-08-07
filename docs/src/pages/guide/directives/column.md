---
sidebarPos: 3
sidebarTitle: Column
---

# v-column

Adds Vuetify grid column classes (`v-col-*`) from a binding object.

**File:** `vue/src/js/directives/column.js`

## Examples

Default (full width):

```vue
<div v-column>
  Content
</div>
<!-- → class "v-col-12" -->
```

Responsive columns:

```vue
<div v-column="{ cols: 12, md: 6, lg: 4 }">
  Card
</div>
<!-- → v-col-12 v-col-md-6 v-col-lg-4 -->
```

## Notes

- Binding must be an object of breakpoint → size (or omit for `{ cols: 12 }`).
- Uses Vuetify utility class naming (`v-col-{bp}-{n}`).

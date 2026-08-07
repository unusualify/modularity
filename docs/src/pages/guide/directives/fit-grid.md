---
sidebarPos: 4
sidebarTitle: Fit Grid
---

# v-fit-grid

Makes the host a flex container and stretches its **first child** to full height and width.

**File:** `vue/src/js/directives/fit-grid.js`

## Example

```vue
<div
  v-fit-grid
  style="height: 400px"
>
  <div>
    Fills the parent
  </div>
</div>
```

Adds:

- Host: `d-flex`
- First child: `h-100 w-100`

Useful when a nested panel should occupy the entire cell of a fixed-height layout.

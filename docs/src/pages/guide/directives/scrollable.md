---
sidebarPos: 5
sidebarTitle: Scrollable
---

# v-scrollable

Marks an element as a scroll region (`ue-scrollable`). With the `.height` modifier, sets a fixed height and `overflow-y: auto`.

**File:** `vue/src/js/directives/scrollable.js`

## Examples

Mark only:

```vue
<div v-scrollable>
  Long content…
</div>
```

Fixed height (number = px):

```vue
<div v-scrollable.height="240">
  Scrolls inside 240px
</div>
```

CSS length string:

```vue
<div v-scrollable.height="'50vh'">
  Scrolls inside half the viewport
</div>
```

## Notes

- Valid heights: number, or string matching `px|em|rem|vh|%`.
- Updating the bound height updates `el.style.height` on `updated`.

---
sidebarPos: 33
sidebarTitle: Recursive Data Viewer
---
# Recursive Data Viewer

`ue-recursive-data-viewer` renders arbitrary JSON data — objects, arrays, and primitives — as an interactive, collapsible tree. It is the equivalent of a browser DevTools JSON inspector for use inside views.

## Usage

```html
<ue-recursive-data-viewer :data="responsePayload" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | `Array\|Object\|String\|Number\|Boolean` | required | The value to render |
| `allArrayItemsClosed` | `Boolean` | `false` | Collapse all array items on initial render |
| `allArrayItemsOpen` | `Boolean` | `false` | Expand all array items on initial render |
| `allObjectsClosed` | `Boolean` | `false` | Collapse all objects on initial render |
| `allObjectsOpen` | `Boolean` | `false` | Expand all objects on initial render |
| `objectDepth` | `Number` | `0` | Current recursion depth (set automatically by child instances) |
| `arrayIndex` | `Number` | `null` | Index in a parent array (set automatically) |
| `objectTitle` | `String` | `null` | Optional label shown above the object (internal use) |

## Behaviour

- **Arrays** are rendered with an item count badge. Each element can be expanded or collapsed independently.
- **Objects** render as a collapsible table of key/value pairs. The first object at depth 0 is expanded by default.
- **Primitives** (string, number, boolean) are rendered as plain text inline.
- Object keys are styled in a monospace purple font, matching common JSON viewer conventions.
- The component is fully recursive — nested objects and arrays are rendered by child `ue-recursive-data-viewer` instances.

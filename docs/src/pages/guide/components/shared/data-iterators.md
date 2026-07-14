---
sidebarPos: 37
sidebarTitle: Data Iterators
---
# Data Iterators

Modularous ships three data-iterator components that render a single row/card from a data set with support for column formatters and row actions. They are used by `ue-data-table` when the display mode is set to `row`, `card`, or `expandable`.

All three share the same prop interface via `makeTableIteratorProps()`.

## Common Props

| Prop | Type | Description |
|------|------|-------------|
| `item` | `Object` | The data record for this row/card |
| `headers` | `Array` | Column header definitions (same shape as `ue-data-table` headers) |
| `rowActions` | `Array` | Action button definitions (icon, name, condition) |
| `iteratorOptions` | `Object` | Display configuration — which fields map to which visual areas (see per-component details below) |

## `ue-rich-row-iterator`

Renders a record as a horizontal card with a title, four named columns (firstColumn, secondColumn, featured, lastColumn), and action buttons.

```js
iteratorOptions: {
  headerKey:    'name',       // field shown as card title
  firstColumn:  ['field1', 'field2'],
  secondColumn: ['field3'],
  featured:     'status',     // visually prominent field (col 3)
  lastColumn:   'created_at',
}
```

## `ue-rich-card-iterator`

Renders a record as a vertical card with a cover image, a title row, and a compact table of all header fields. Used in grid/masonry layouts.

```js
iteratorOptions: {
  imgSrc: 'thumbnail_url',    // header key whose value is the image URL
}
```

## `ue-expandable-iterator`

Renders a record as a collapsible row. Clicking the row toggles a second field visible below the header.

```js
iteratorOptions: {
  headerKey:   'name',        // field shown in the collapsed row
  expandedKey: 'description', // field revealed on expand
}
```

## Row Actions

All iterators emit row action events when action buttons are clicked. Actions are defined as:

```js
rowActions: [
  { name: 'edit',   icon: 'mdi-pencil',  condition: (item) => item.can_edit },
  { name: 'delete', icon: 'mdi-trash-can' },
]
```

::: tip
You typically do not use these components directly — set `display-mode` on `ue-data-table` to `row`, `card`, or `expandable` and the table switches iterator automatically.
:::

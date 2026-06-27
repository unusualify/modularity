---
sidebarPos: 15
sidebarTitle: List Section
---
# List Section

`ue-list-section` is a flexible, multi-column list component that renders rows from an array of items. It supports optional headers, column width control, striped/hoverable rows, a "show more/less" collapse feature, an optional expansion-panel wrapper, and a per-row actions slot.

## Usage

```html
<ue-list-section
  :items="users"
  :item-fields="['name', 'email', 'role']"
  :headers="['Name', 'Email', 'Role']"
  show-header
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `Array` | required | Array of data objects to render |
| `itemFields` | `Array` | `['name']` | Dot-notation field paths to display per column |
| `headers` | `Array` | `null` | Column header labels. Defaults to title-cased field names |
| `showHeader` | `Boolean` | `false` | Show the header row |
| `title` | `String` | — | Optional title above the list |
| `titleTag` | `String` | `'h3'` | HTML element for the title |
| `titleClasses` | `String` | `'text-body-1 font-weight-medium'` | Classes applied to the title |
| `itemClasses` | `String` | `'text-body-2'` | Classes applied to each data row |
| `headerClasses` | `String` | `'text-body-2 font-weight-bold'` | Classes applied to the header row |
| `colClasses` | `Array` | `[]` | Per-column CSS class array |
| `colWidths` | `Array` | `[]` | Fixed widths per column, e.g. `['120px', '1fr']` |
| `colRatios` | `Array` | `[]` | Flex ratios per column, e.g. `[2, 1, 1]` |
| `actionsHeader` | `String` | `''` | Header label for the actions column |
| `striped` | `Boolean` | `false` | Alternate row background colors |
| `hoverable` | `Boolean` | `false` | Highlight rows on hover |
| `hasRowBottomBorder` | `Boolean` | `false` | Add a bottom border to each row |
| `verticalAlignTop` | `Boolean` | `false` | Align cell content to the top |
| `emptyMessage` | `String` | `'No items to display'` | Message shown when `items` is empty |
| `rowClassFn` | `Function` | `null` | `(item, index) => String` — return extra classes per row |
| `dividerAttributes` | `Object` | `{}` | Attributes forwarded to `v-divider` for divider rows |
| `collapsible` | `Boolean` | `false` | Wrap the entire list in an expansion panel |
| `collapseLimit` | `Number` | `null` | Auto-wrap in expansion panel when item count exceeds this value |
| `shrinkAfter` | `Number` | `20` | Number of items shown before "show more" button appears |
| `showMoreText` | `String` | `'Show more'` | Label for the expand trigger |
| `shrinkText` | `String` | `'Show less'` | Label for the collapse trigger |
| `moreItemsText` | `String` | `'more items'` | Suffix label next to the hidden item count |
| `modelValue` | `Array\|String\|Number` | — | Controls the expansion panel open state externally |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `field.{n}` | `{value, item, index}` | Override cell content for column `n` (0-based) |
| `header.{n}` | `{header}` | Override header cell for column `n` |
| `row-actions` | `{item, index}` | Append an actions column to every row |
| `actions-header` | — | Header for the actions column |
| `title-content` | — | Custom title markup (used with `collapsible`) |
| `before-items` | — | Content injected before the first row |
| `after-items` | — | Content injected after the last row |

## Divider Rows

Insert a divider between items by adding `{ _type: 'divider' }` to the `items` array:

```js
const items = [
  { name: 'Alice', role: 'Admin' },
  { _type: 'divider' },
  { name: 'Bob', role: 'Editor' },
]
```

## Example — Compact Table with Actions

```html
<ue-list-section
  :items="orders"
  :item-fields="['reference', 'total', 'status']"
  :headers="['Ref', 'Total', 'Status']"
  show-header
  striped
  hoverable
  :col-ratios="[2, 1, 1]"
  :shrink-after="10"
>
  <template #field.2="{ value }">
    <v-chip :color="value === 'paid' ? 'success' : 'warning'" size="small">
      {{ value }}
    </v-chip>
  </template>
  <template #row-actions="{ item }">
    <v-btn icon="mdi-eye" size="small" variant="text" :href="`/orders/${item.id}`" />
  </template>
</ue-list-section>
```

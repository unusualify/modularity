---
sidebarPos: 43
sidebarTitle: Table Internals
---
# Table Internals

These components are internal building blocks of `ue-data-table`. You do not use them directly — they are rendered automatically by the table.

## `TableFormatterCell`

Renders a single cell in the data table, applying the correct formatter strategy based on `col.formatter`.

### Formatter strategies

| Formatter value | Render |
|-----------------|--------|
| `'edit'` / `'activate'` | Clickable primary-colour text. Clicking calls `itemAction` |
| `'switch'` | `v-switch` that calls `itemAction` on change |
| `'dynamic'` | Delegates to `ue-dynamic-component-renderer` |
| Any other / array | Delegates to `ue-recursive-stuff` via `handleFormatter()` |

### Key Props

| Prop | Type | Description |
|------|------|-------------|
| `col` | `Object` | Column header definition (key, formatter, formatterName, isFormatting, hasCopy, target) |
| `item` | `Object` | The data record for the row |
| `cellValue` | any | Explicit cell value — overrides `item[col.key]` when set |
| `cellOptions` | `Object` | Extra cell config — currently supports `maxChars` for pre-shorten |
| `handleFormatter` | `Function` | Formatter resolution function provided by the table |
| `itemAction` | `Function` | Row action handler provided by the table |
| `clickableRow` | `Boolean` | True when the row itself is clickable (affects edit/activate cells) |
| `groupContext` | `Boolean` | True when the cell is inside a `group-by` header row |
| `disableTooltip` | `Boolean` | Skip `v-tooltip` — used in mobile/touch layouts |

---

## `TableGroupHeaderRow`

Renders the sticky group header row when `group-by` is active in `ue-data-table`. It shows the group value (with optional formatter), a row count badge, a collapse/expand toggle, and an optional select-all checkbox.

### Key Props

| Prop | Type | Description |
|------|------|-------------|
| `group` | `Object` | Vuetify group object (`value`, `depth`, `items`) |
| `columns` | `Array` | Full column definitions — used for `colspan` |
| `formatterColumn` | `Object\|null` | Column definition whose formatter should render the group value |
| `syntheticItem` | `Object` | Synthetic row object used by `TableFormatterCell` in group context |
| `handleFormatter` | `Function` | Formatter resolution function from the table |
| `itemAction` | `Function` | Row action handler |
| `showSelect` | `Boolean` | Show the group-level select checkbox |
| `disableFormatterTooltip` | `Boolean` | Disable tooltips on the formatter cell (mobile mode) |

---

## `TableActions` (table/TableActions.vue)

A standalone row of action buttons rendered in the table toolbar or below the table. Accepts an `actions` array in the same format as `ue-form`'s actions and renders each as a `v-btn` with optional badge, tooltip, and publish-switch variants.

### Props

| Prop | Type | Description |
|------|------|-------------|
| `actions` | `Array` | Action definitions (same shape as `ue-form` actions) |

### Slots

| Slot | Description |
|------|-------------|
| `prepend` | Content placed before the action buttons |

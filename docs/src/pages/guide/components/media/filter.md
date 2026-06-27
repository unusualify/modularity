---
sidebarPos: 29
sidebarTitle: Filter
---
# Filter

`ue-filter` provides a search input with an optional collapsible hidden-filters panel. It is designed to sit above a data table or list and drives an external `filterState` object via `v-model`.

## Usage

```html
<ue-filter
  v-model:filterState="filterState"
  @submit="applyFilters"
  @clear="clearFilters"
>
  <!-- optional: extra controls next to the search input -->
  <template #additional-actions>
    <v-btn @click="exportCsv">Export</v-btn>
  </template>

  <!-- optional: additional filter fields revealed on toggle -->
  <template #hidden-filters>
    <ue-input-select name="status" label="Status" :items="statusOptions" />
  </template>
</ue-filter>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `filterState` | `Object` | required | Current filter values. Must contain at least a `search` key |
| `initialSearchValue` | `String` | `''` | Pre-populated search value on mount |
| `placeholder` | `String` | i18n `filter.search-placeholder` | Search input placeholder text |
| `closed` | `Boolean` | `false` | Keep the hidden-filters panel closed even after toggle |
| `clearOption` | `Boolean` | `false` | Show a "Clear" button inside the hidden-filters panel |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:filterState` | `Object` | Emitted when the search value changes |
| `submit` | `Object` | Emitted when the form is submitted; payload is the serialised form data |
| `clear` | — | Emitted when the "Clear" button is clicked |

## Slots

| Slot | Description |
|------|-------------|
| `navigation` | Content placed to the left of the search field (e.g. tabs or segment controls) |
| `additional-actions` | Content placed to the right of the search field |
| `hidden-filters` | Filter fields revealed by the toggle button; hidden when this slot is empty |
| `default` | Content rendered below the filter bar |

## Behaviour

- The hidden-filters panel uses a CSS height-transition for smooth expand/collapse.
- If the `#hidden-filters` slot is not provided, the toggle button is not rendered.
- If the `#navigation` slot is not provided, the navigation area is hidden.

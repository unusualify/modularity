---
sidebarPos: 30
sidebarTitle: Dropdown Filter
---
# Dropdown Filter

`ue-dropdown-filter` presents a `v-menu` triggered by a button. The menu contains a `ue-form` built from a `schema` prop, giving users a compact way to apply structured filters without a full-page filter bar.

## Usage

```html
<ue-dropdown-filter
  v-model:filterState="filterState"
  :schema="filterSchema"
  :page="currentPage"
  type="users"
  @submit="loadData"
  @clear="resetFilters"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `filterState` | `Object` | required | Current filter values, kept in sync via `v-model:filterState` |
| `schema` | `Object\|Array` | required | `ue-form` schema describing the filter fields |
| `page` | `Number` | required | Current page number (forwarded for pagination resets) |
| `type` | `String` | required | Resource type identifier used by the parent to scope the filter |
| `buttonText` | `String` | `'Filter'` | Label on the activator button |
| `loading` | `Boolean` | `false` | Shows a loading indicator on the submit button |
| `tags` | `Array` | `[]` | Optional tag list for tag-based filtering UI |
| `filterModel` | `Object` | required | Initial model shape passed to the internal form |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:filterState` | `Object` | Emitted with the new filter values on submit |
| `submit` | — | Emitted after the menu closes following a filter apply |
| `clear` | — | Emitted when "Clear Filters" is clicked |

## Behaviour

- The menu closes automatically after "Apply Filters" or "Clear Filters" is clicked.
- The internal `ue-form` model is a local copy of `filterState`, so the parent state is only updated on explicit submit — not on every field change.

---
sidebarPos: 19
sidebarTitle: Configurable Card
---
# Configurable Card

`ue-configurable-card` is a multi-column card layout engine. It splits an `items` object into equal-width segments separated by dividers, with an optional actions column appended at the end. Each segment renders as `ue-property-list` by default and can be overridden with a named slot.

## Usage

```html
<ue-configurable-card
  title="Order Summary"
  :items="{ info: orderInfo, shipping: shippingInfo }"
  :actions="[{ icon: 'mdi-pencil', color: 'primary', onClick: editOrder }]"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `Object\|Array` | required | Segments to render. Each key/index becomes one column |
| `title` | `String` | `''` | Card title |
| `titleColor` | `String` | — | Title text color |
| `actions` | `Array` | `[]` | Action button definitions appended as the last column |
| `noActions` | `Boolean` | `false` | Suppress the actions column even if `actions` is non-empty |
| `hideSeparator` | `Boolean` | `false` | Remove vertical dividers between columns |
| `maxSegments` | `Number` | `null` | Cap the number of visible segments (1–12) |
| `colRatios` | `Array` | `[]` | Flex ratios for each column, e.g. `[2, 1, 1]` |
| `columnStyles` | `Object` | `{}` | Inline style overrides keyed by column index, e.g. `{ 0: 'flex-basis: 40%' }` |
| `columnClasses` | `Object` | `{}` | CSS class overrides keyed by column index |
| `colPaddingX` | `Number\|String` | `2` | Horizontal padding (Vuetify scale) for all columns |
| `colPaddingY` | `Number\|String` | — | Vertical padding for all columns |
| `rowMarginY` | `Number\|String` | `4` | Vertical margin of the columns row |
| `rowMarginX` | `Number\|String` | — | Horizontal margin of the columns row |
| `rowMinHeight` | `String` | `null` | Minimum height of the row element |
| `alignCenterColumns` | `Boolean` | `false` | Vertically center the content inside each column |
| `justifyCenterColumns` | `Boolean` | `false` | Horizontally center the content inside non-first columns |
| `actionIconSize` | `String` | `'medium'` | Size of action icon buttons |
| `actionIconMinWidth` | `Number` | `44` | Minimum width (px) of action buttons |
| `actionIconMinHeight` | `Number` | `44` | Minimum height (px) of action buttons |
| `mobileBreakpoint` | `String` | `'md'` | Breakpoint below which columns stack vertically |
| `mobileRowGap` | `Number\|String` | `4` | Gap between stacked columns on mobile |
| `mobileColPaddingX` | `Number\|String` | `0` | Horizontal padding per column on mobile |
| `mobileColPaddingY` | `Number\|String` | `0` | Vertical padding per column on mobile |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `segment.1`, `segment.2`, … | `{data, actions, actionProps}` | Override content for column N (1-based) |
| `segment.actions` | `{data, actions, actionProps}` | Override the actions column |
| `title` | — | Replace the entire title row |

## Segment Data Format

Each segment value can be:
- **Object** — rendered as `ue-property-list`
- **Array** — each element becomes a property-list row (single-value pairs)
- **Primitive** — rendered via `ue-dynamic-component-renderer`

## Examples

```html
<!-- 3-column contact card -->
<ue-configurable-card
  title="Contact"
  :items="{
    personal: { Name: 'Alice', Age: 32 },
    contact:  { Email: 'alice@example.com', Phone: '+1 555 000' },
    address:  { City: 'Istanbul', Country: 'Turkey' },
  }"
  :col-ratios="[2, 2, 1]"
/>

<!-- Custom slot override -->
<ue-configurable-card :items="cardData" :actions="rowActions">
  <template #segment.1="{ data }">
    <div class="d-flex flex-column">
      <span class="font-weight-bold">{{ data.name }}</span>
      <span class="text-caption text-grey">{{ data.role }}</span>
    </div>
  </template>
</ue-configurable-card>
```

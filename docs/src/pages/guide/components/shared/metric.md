---
sidebarPos: 14
sidebarTitle: Metric / Metrics / MetricGroups
---
# Metric, Metrics & MetricGroups

Three related components for displaying KPI-style numeric cards. Use `ue-metric` for a single value, `ue-metrics` for a grouped collection with an optional date-range filter, and `ue-metric-groups` for multiple `ue-metrics` groups laid out in a grid.

For the new dashboard glance row (independent cards, title outside a parent card), use [`ue-metric-cards`](./metric-cards) instead of wrapping `ue-metric` in `ue-metrics`.

---

## `ue-metric`

A single KPI card showing a large value and a label.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `Number\|String` | required | The numeric or text value to display |
| `label` | `String` | required | Descriptive label shown below the value |
| `color` | `String` | `null` | Color applied to both value and label text |
| `cardColor` | `String` | `null` | Background color of the card |
| `labelColor` | `String` | `null` | Override label text color independently |
| `valueClass` | `String` | `''` | Extra classes on the value element |
| `labelClass` | `String` | `''` | Extra classes on the label element |
| `dense` | `Boolean` | `false` | Compact mode — smaller text (`text-headline-large` vs `text-display-medium`) |
| `noInline` | `Boolean` | `false` | Render as block instead of inline-block |
| `center` | `Boolean` | `false` | Center-align the content |
| `icon` | `String` | `null` | MDI icon shown before the label |
| `appendIcon` | `String` | `null` | MDI icon shown to the left of the value/label block |
| `appendIconAttributes` | `Object` | `{}` | Attributes forwarded to the `appendIcon` `v-icon` |
| `layout` | `'stacked'\|'horizontal'` | `'stacked'` | `stacked`: value over label. `horizontal`: optional `appendIcon` · label (`text-body-small`, wraps) · 1px vertical divider in the theme border color (`--v-border-color` / `--v-border-opacity`, same as `border-*` utilities) that stretches with the tile height · value (right-aligned, regular-weight `text-headline-large`). In `horizontal`, the label uses `labelColor` when set, otherwise `color`; tiles have 16px padding and a 96px minimum height so one- and two-line labels line up, long single words wrap instead of pushing the value out. Container queries shrink the value to 1.75rem under 180px tile width, and under 150px also tighten horizontal padding to 8px and drop the value to 1.25rem. Vertical padding (16px) and the 96px minimum height never change, and match `ue-metric-card` |
| `variant` | `String` | `undefined` | `v-card` variant (e.g. `flat`, `outlined`). `undefined` keeps the Vuetify default |
| `elevation` | `Number\|String` | `undefined` | `v-card` elevation |
| `rounded` | `String\|Number\|Boolean` | `undefined` | `v-card` rounded (e.g. `lg`) |
| `border` | `String\|Number\|Boolean` | `undefined` | `v-card` border (e.g. `sm`) |

### Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `value` | `{value, classes}` | Replaces the value element |
| `label` | `{label, classes, icon}` | Replaces the label element |

### Example

```html
<ue-metric value="1,248" label="Total Orders" color="primary" />
<ue-metric value="98.5%" label="Uptime" color="success" dense />

<!-- flat bordered tile: label | value -->
<ue-metric value="12" label="In Progress" layout="horizontal" variant="flat" :elevation="0" rounded="lg" border="sm" no-inline />
```

---

## `ue-metrics`

A card that renders a collection of `ue-metric` items in a flex row, with an optional title, subtitle, and date-range filter.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `String` | required | Card title |
| `subtitle` | `String` | `null` | Caption below the title |
| `titleAttributes` | `Object` | `{}` | Props forwarded to the title's `ue-title` (`type`, `weight`, `transform`, …). Empty keeps the `ue-title` defaults (`body-large`, bold, uppercase). Flat dashboard panels use `['type' => 'title-large', 'weight' => 'regular', 'color' => 'grey']` (theme `grey` is the dark teal heading color) |
| `headerDivider` | `Boolean` | `false` | Full-width divider between the header (title + date filter) and the metrics, running edge to edge. Adds extra space below the header (`pb-5`) and gives the metrics row a 16px top padding |
| `items` | `Array` | `[]` | Array of metric objects — each is spread as props into `ue-metric` |
| `color` | `String` | `null` | Text color for all metrics |
| `cardColor` | `String` | `null` | Card background color |
| `filterColor` | `String` | `null` | Color for the date-range filter area |
| `bgHeaderColor` | `String` | `null` | Header background color |
| `noInline` | `Boolean` | `false` | Render card as block |
| `metricWidth` | `String\|Number` | `null` | Fixed width for each metric |
| `minMetricWidth` | `String\|Number` | `130` | Minimum width (px) for each metric |
| `metricAttributes` | `Object` | `{}` | Extra attributes forwarded to every `ue-metric` |
| `metricLayout` | `'stacked'\|'horizontal'` | `'stacked'` | Forwarded to every `ue-metric` as `layout` (overridable via `metricAttributes.layout` or per item). `horizontal` also switches the row to an even CSS grid (`repeat(auto-fill, minmax(minMetricWidth, 1fr))`, 16px gap) and ignores `metricWidth` |
| `metricColumns` | `String\|Number` | `null` | `horizontal` only. Max equal-width columns per row (e.g. `2` for the design's two-column panels). Falls back to fewer columns when a column would be narrower than `minMetricWidth`. `null` keeps the `auto-fill` behavior |
| `compactFilter` | `Boolean` | `false` | 32px-high date-range field (12px text) that shrinks between 132px and 220px next to a non-wrapping title. Default keeps the 280px compact-density field |
| `filterPlaceholder` | `String` | `null` | Placeholder for the date-range field (e.g. `__('Date - Date')`). `null` keeps the Vuetify date-format placeholder |
| `elevation` | `Number\|String` | `0` | Elevation of the outer panel card |
| `rounded` | `String\|Number\|Boolean` | `undefined` | Rounded of the outer panel card (e.g. `lg`) |
| `border` | `String\|Number\|Boolean` | `undefined` | Border of the outer panel card (e.g. `sm`) |
| `endpoint` | `String` | `null` | API endpoint for date-range refresh. Required to activate the date filter |
| `dateLabel` | `String` | `'Today'` | Label shown next to the date |
| `date` | `String` | `null` | Date string displayed alongside the filter |

### Item object shape

```js
{
  value: '1,248',
  label: 'Orders',
  color: 'primary',         // optional, overrides group color
  connectorFilter: {        // optional, marks this metric as filterable
    name: 'date_range',
    args: {}
  }
}
```

### Example

```php
@php
  $metrics = [
    ['value' => $totalOrders,  'label' => 'Total Orders'],
    ['value' => $revenue,      'label' => 'Revenue', 'color' => 'success'],
    ['value' => $pendingCount, 'label' => 'Pending',  'color' => 'warning'],
  ];
@endphp

<ue-metrics
  title="Sales Overview"
  :items='@json($metrics)'
  endpoint="{{ route('metrics.refresh') }}"
/>
```

### Flat dashboard panel (`MetricsWidget`)

Flat bordered panel with flat `label | value` tiles, configured from `ui_settings.dashboard.blocks.*.attributes`:

```php
'attributes' => [
    'title' => __('AT A GLANCE'),
    'titleAttributes' => ['type' => 'title-large', 'weight' => 'regular', 'color' => 'grey'], // 22px regular dark-teal headline
    'headerDivider' => true,                    // edge-to-edge line under title + filter
    'elevation' => 0,
    'rounded' => 'lg',
    'border' => 'sm',
    'metricLayout' => 'horizontal',
    'metricColumns' => 2,                       // even two-column tiles
    'compactFilter' => true,                    // only rendered when an item has connectorFilter
    'filterPlaceholder' => __('Date - Date'),
    'metricAttributes' => [
        'variant' => 'flat',
        'color' => 'primary',      // value color (e.g. 'secondary' for TO DO's)
        'labelColor' => 'grey-darken-1',
        'elevation' => 0,
        'rounded' => 'lg',
        'border' => 'sm',
        'noInline' => true,
    ],
    'items' => [/* ... */],
],
```

---

## `ue-metric-groups`

Renders multiple `ue-metrics` groups in a responsive `v-row / v-col` grid.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `String` | `''` | Card title wrapping all groups |
| `items` | `Array` | required | Array of `ue-metrics` prop objects (each can include a `col` key for responsive overrides) |
| `defaultCol` | `Object` | `{cols: 12}` | Default `v-col` binding applied to every group column |
| `metricsBgHeaderColor` | `String` | `null` | Override `bgHeaderColor` for all groups |
| `metricsNoInline` | `Boolean` | `null` | Override `noInline` for all groups |
| `metricColor` | `String` | `null` | Override `color` for all individual metrics |
| `metricCardColor` | `String` | `null` | Override `cardColor` for all individual metrics |
| `metricLabelColor` | `String` | `null` | Override `labelColor` for all individual metrics |

### Example

```php
@php
  $groups = [
    [
      'title' => 'Sales',
      'items' => [
        ['value' => '320', 'label' => 'Orders'],
        ['value' => '$14,200', 'label' => 'Revenue'],
      ],
      'col' => ['cols' => 12, 'md' => 6],
    ],
    [
      'title' => 'Support',
      'items' => [
        ['value' => '12', 'label' => 'Open Tickets'],
        ['value' => '98%', 'label' => 'Resolution Rate'],
      ],
      'col' => ['cols' => 12, 'md' => 6],
    ],
  ];
@endphp

<ue-metric-groups title="Dashboard" :items='@json($groups)' />
```

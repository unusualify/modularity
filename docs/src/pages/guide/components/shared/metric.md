---
sidebarPos: 14
sidebarTitle: Metric / Metrics / MetricGroups
---
# Metric, Metrics & MetricGroups

Three related components for displaying KPI-style numeric cards. Use `ue-metric` for a single value, `ue-metrics` for a grouped collection with an optional date-range filter, and `ue-metric-groups` for multiple `ue-metrics` groups laid out in a grid.

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
| `dense` | `Boolean` | `false` | Compact mode — smaller text (`text-h4` vs `text-h3`) |
| `noInline` | `Boolean` | `false` | Render as block instead of inline-block |
| `center` | `Boolean` | `false` | Center-align the content |
| `icon` | `String` | `null` | MDI icon shown before the label |
| `appendIcon` | `String` | `null` | MDI icon shown to the left of the value/label block |
| `appendIconAttributes` | `Object` | `{}` | Attributes forwarded to the `appendIcon` `v-icon` |

### Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `value` | `{value, classes}` | Replaces the value element |
| `label` | `{label, classes, icon}` | Replaces the label element |

### Example

```html
<ue-metric value="1,248" label="Total Orders" color="primary" />
<ue-metric value="98.5%" label="Uptime" color="success" dense />
```

---

## `ue-metrics`

A card that renders a collection of `ue-metric` items in a flex row, with an optional title, subtitle, and date-range filter.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `String` | required | Card title |
| `subtitle` | `String` | `null` | Caption below the title |
| `items` | `Array` | `[]` | Array of metric objects — each is spread as props into `ue-metric` |
| `color` | `String` | `null` | Text color for all metrics |
| `cardColor` | `String` | `null` | Card background color |
| `filterColor` | `String` | `null` | Color for the date-range filter area |
| `bgHeaderColor` | `String` | `null` | Header background color |
| `noInline` | `Boolean` | `false` | Render card as block |
| `metricWidth` | `String\|Number` | `null` | Fixed width for each metric |
| `minMetricWidth` | `String\|Number` | `130` | Minimum width (px) for each metric |
| `metricAttributes` | `Object` | `{}` | Extra attributes forwarded to every `ue-metric` |
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

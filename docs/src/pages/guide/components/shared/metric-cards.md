---
sidebarPos: 15
sidebarTitle: Metric Cards
---

# Metric Cards

Independent KPI cards for the V2 dashboard glance row. Unlike [`ue-metrics`](./metric), this layout does **not** wrap items in one parent card: the section title sits above a responsive row of `ue-metric-card` tiles (icon · label · value).

PHP dashboard blocks use `MetricCardsWidget` (`ue-metric-cards`). Admin/reporter dashboards can keep `MetricsWidget` / `ue-metrics`.

---

## `ue-metric-card`

A single horizontal card: icon in a circle, label, then a large value on the right.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `Number\|String` | required | The numeric or text value |
| `label` | `String` | required | Label between the icon and the value |
| `color` | `String` | `'primary'` | Text color for the value |
| `labelColor` | `String` | `'grey-darken-1'` | Label text color |
| `valueClass` | `String` | `''` | Extra classes on the value |
| `labelClass` | `String` | `''` | Extra classes on the label |
| `appendIcon` | `String` | `null` | MDI icon on the left |
| `appendIconAttributes` | `Object` | `{}` | Forwarded to the icon `v-icon` |
| `padDigits` | `Number` | `0` | When `> 0` and the value is numeric, pad with leading zeros (`2` → `02`) |
| `variant` | `String` | `'outlined'` | `v-card` variant |
| `elevation` | `Number\|String` | `0` | `v-card` elevation |

---

## `ue-metric-cards`

Section title + optional date-range filter + a `v-row` of `ue-metric-card` items. Connector values are resolved in `MetricCardsWidget::hydrateAttributes` the same way as `MetricsWidget`.

Do not put `pa-*` or extra `ga-*` on the inner `v-row`: column widths (`lg: 3` × 4) already fill the grid, and CSS gap on top of those percentages wraps the last card. Outer inset belongs on the page (for example Dashboard `pa-3`). `ue-blocks` columns that contain `ue-metric-cards` use `overflow: visible` so nested row gutters do not clip tile borders.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `String` | required | Section title |
| `subtitle` | `String` | `null` | Caption under the title |
| `items` | `Array` | `[]` | Metric objects — spread onto `ue-metric-card` |
| `metricCol` | `Object` | `{cols: 12, sm: 6, lg: 3}` | `v-col` binding for each card |
| `metricAttributes` | `Object` | `{}` | Defaults merged into every card |
| `endpoint` | `String` | `null` | Date-range refresh URL (same metrics endpoint as `ue-metrics`) |
| `filterColor` | `String` | `null` | Color for the date-range filter area |

### Dashboard block

```php
'client-manager-metrics' => [
    'widget' => 'MetricCardsWidget',
    'widgetCol' => [
        'cols' => 12,
        'lg' => 12,
    ],
    'attributes' => [
        'title' => __('AT A GLANCE'),
        'subtitle' => __('Check key metrics about your press release requests.'),
        'items' => [
            [
                'appendIcon' => 'mdi-table-clock',
                'label' => __('Press Release Credit'),
                'connector' => 'PressRelease|PressRelease^repository->getCountFor?method=isStateable&args=[credit]',
            ],
            // ...
        ],
    ],
],
```

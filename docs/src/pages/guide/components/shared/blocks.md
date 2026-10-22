---
sidebarPos: 24
sidebarTitle: Blocks
---
# Blocks

`ue-blocks` renders a collection of `ue-recursive-stuff` configuration objects side-by-side in a `v-row`. It is the top-level block container used by Modularous index and dashboard pages to lay out configurable content areas.

## Usage

```html
<ue-blocks :items="pageBlocks" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `Object` | `{}` | Map of block configurations. Each value is a `ue-recursive-stuff` configuration object |

## Example

```php
@php
  $blocks = [
    'metrics' => [
      'tag'        => 'ue-metrics',
      'attributes' => [
        'title' => 'Overview',
        'items' => $metricsData,
      ],
    ],
    'list' => [
      'tag'        => 'ue-list-section',
      'attributes' => [
        'items'       => $recentItems,
        'item-fields' => ['name', 'status'],
      ],
    ],
  ];
@endphp

<ue-blocks :items='@json($blocks)' />
```

## Auto-height blocks

The blocks row grows into the leftover page height (`flex-grow-1 min-height-0`), and a wrapping flex row splits that leftover height between **all** of its lines. A short block (e.g. a metrics row) therefore gets stretched too, leaving empty space under it.

Mark such a block col with `ue-blocks__col--auto` to keep it at content height. When any block has this class, `ue-blocks` groups consecutive blocks into stacked rows: auto-height rows do not grow, the other rows fill the remaining height. Without the class the single-row layout is unchanged.

From PHP widgets, use the `widgetAutoHeight` flag instead of writing the class (it is appended to `widgetAttributes.class`, so a config `class` does not override it):

```php
'my-summary' => [
    'widget' => 'MetricsWidget',
    'widgetAutoHeight' => true,
    // ...
],
```

`MetricCardsWidget` defaults to `widgetAutoHeight = true`; pass `'widgetAutoHeight' => false` to opt out.

::: tip
`ue-blocks` wraps each block in a `v-row` but does not control column widths internally — the block's own `tag` is responsible for layout. For grid control, use `ue-metric-groups` or wrap blocks in `v-col` manually.
:::

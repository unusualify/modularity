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

::: tip
`ue-blocks` wraps each block in a `v-row` but does not control column widths internally — the block's own `tag` is responsible for layout. For grid control, use `ue-metric-groups` or wrap blocks in `v-col` manually.
:::

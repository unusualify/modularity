---
sidebarPos: 9
sidebarTitle: Property List
---
# Property List

The `ue-property-list` component renders a key–value list in caption-sized text. It accepts either an array of `[key, value]` pairs or a plain object.

## Usage

```html
<!-- From an object -->
<ue-property-list :data="{ Name: 'Alice', Role: 'Admin', Status: 'Active' }" />

<!-- From an array of pairs -->
<ue-property-list :data="[['Name', 'Alice'], ['Role', 'Admin']]" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | `Array\|Object` | `[]` | The data to display. Objects are converted to `[key, value]` pairs internally |
| `noPadding` | `Boolean` | `false` | Remove the default vertical padding |

## Data Formats

**Object** — keys become labels, values become the displayed content:
```js
{ 'Created At': '2025-01-15', 'Updated At': '2025-03-02' }
```

**Array of pairs** — each element is `[label, ...values]`. Multiple values are joined with `, `:
```js
[
  ['Name', 'Alice'],
  ['Tags', 'admin', 'editor'],   // renders "admin, editor"
]
```

## Example — Item Detail Panel

```php
@php
  $details = [
    'ID'         => $item->id,
    'Created'    => $item->created_at->format('Y-m-d'),
    'Status'     => $item->status,
    'Assigned To'=> $item->assignee?->name ?? '—',
  ];
@endphp

<ue-property-list :data='@json($details)' />
```

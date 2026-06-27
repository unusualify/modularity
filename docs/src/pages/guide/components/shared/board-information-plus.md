---
sidebarPos: 35
sidebarTitle: Board Information Plus
---
# Board Information Plus

`ue-board-information-plus` renders an "At a Glance" dashboard card grid. Each card displays a labelled statistic with an icon.

## Usage

```html
<ue-board-information-plus :cards="glanceCards" />
```

## Props

| Prop | Type | Description |
|------|------|-------------|
| `cards` | `Array` | Array of card objects (see Card Shape below) |
| `container` | `Object` | Styles applied to the outer container card (defaults: `{ color: '#F8F8FF', elevation: 10, class: 'px-6 py-5' }`) |
| `cardAttribute` | `Object` | Shared visual attributes for all inner cards (variant, border radius, title/info styles — see defaults in source) |

## Card Shape

Each item in the `cards` array supports:

| Key | Type | Description |
|-----|------|-------------|
| `title` | `String` | Stat label (passed through `$t()` for i18n) |
| `data` | `Object` | Object with an `items` key holding the display value (e.g. `{ items: 42 }`) |
| `flex` | `Number` | Column width in a 12-column grid (e.g. `6` for half-width) |
| `icon` | `String` | MDI icon name |
| `iconColor` | `String` | Icon colour |
| `iconBackground` | `String` | CSS colour for the circular icon container |
| `iconSize` | `Number\|String` | Icon size |
| `infoColor` | `String` | Colour of the stat value text |

## Example

```js
const glanceCards = [
  { title: 'Active Users',  data: { items: 142 }, flex: 6, icon: 'mdi-account-group', iconColor: 'white', iconBackground: '#4CAF50' },
  { title: 'Pending Tasks', data: { items: 8 },   flex: 6, icon: 'mdi-clipboard-list', iconColor: 'white', iconBackground: '#FF9800' },
]
```

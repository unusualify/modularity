---
sidebarPos: 28
sidebarTitle: Navigation Group
---
# Navigation Group

`ue-navigation-group` renders a recursive sidebar navigation list. It supports nested subgroups, badge indicators, flyout menus, and Inertia.js links.

## Usage

```html
<ue-navigation-group :items="navItems" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `Array` | required | Array of navigation item objects (see Item Shape below) |
| `level` | `Number` | `0` | Current nesting depth — set automatically by recursive children |
| `hideIcons` | `Boolean` | `false` | Suppress prepend icons on all items |
| `showTooltip` | `Boolean` | `false` | Show item name in a tooltip — useful in rail/collapsed sidebar mode |
| `profileMenu` | `Boolean` | `false` | Render items as compact menu-route entries (used inside the profile popover) |

## Item Shape

Each item in the `items` array is a plain object. The component determines how to render it based on which keys are present:

| Key | Type | Description |
|-----|------|-------------|
| `name` | `String` | Display label |
| `icon` | `String` | MDI icon name (e.g. `mdi-home`) |
| `route` / `href` | `String` | Navigation target — renders as a link or Inertia link |
| `items` | `Array` | Child items — makes this item a collapsible subgroup |
| `menuItems` | `Object` | Child items rendered as a flyout `v-menu` |
| `attr` | any | Marks the item as an event-trigger rather than a link |
| `badge` | `Number\|String` | Badge content displayed on the icon (capped at `9+`) |
| `badgeProps` | `Object` | Additional props forwarded to the `v-badge` |
| `is_active` | `Boolean\|Number` | Pre-selects this item as active and auto-opens any parent group |
| `blank` | `Boolean` | If `true`, the link opens in a new tab |
| `is_modularous_route` | `Boolean` | Marks the link as an in-app Inertia route |

## Example

```js
const navItems = [
  { name: 'Dashboard', icon: 'mdi-view-dashboard', route: '/dashboard', is_modularous_route: true },
  {
    name: 'Settings',
    icon: 'mdi-cog',
    items: [
      { name: 'Profile',  icon: 'mdi-account', route: '/settings/profile' },
      { name: 'Security', icon: 'mdi-lock',    route: '/settings/security' },
    ],
  },
  { name: 'Notifications', icon: 'mdi-bell', route: '/notifications', badge: 5 },
]
```

::: tip
`ue-navigation-group` is used internally by `ue-sidebar`. You typically do not instantiate it directly — pass `items` to `ue-main` or `ue-sidebar` instead.
:::

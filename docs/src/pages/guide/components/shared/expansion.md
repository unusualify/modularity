---
sidebarPos: 13
sidebarTitle: Expansion
---
# Expansion

The `ue-expansion` component is a single-panel wrapper around Vuetify's `v-expansion-panels`. Use it when you need one collapsible section with an optional action icon in the header.

## Usage

```html
<ue-expansion title="Details" :model-value="true">
  <p>Content goes here.</p>
</ue-expansion>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | `true` | Whether the panel starts expanded |
| `title` | `String` | `''` | Panel header text |
| `multiple` | `Boolean` | `false` | Allow multiple panels open simultaneously (forwarded to `v-expansion-panels`) |
| `readonly` | `Boolean` | `false` | Prevent the panel from being toggled |
| `hasActions` | `Boolean` | `false` | Show a custom chevron icon in the header actions slot |
| `expandedIcon` | `String` | `'mdi-chevron-down'` | Icon shown when the panel is expanded |
| `collapsedIcon` | `String` | `'mdi-chevron-up'` | Icon shown when the panel is collapsed |

## Slots

| Slot | Description |
|------|-------------|
| `default` | Body content rendered inside the expansion panel |

## Examples

```html
<!-- Collapsed by default -->
<ue-expansion title="Advanced Settings" :model-value="false">
  <!-- settings inputs -->
</ue-expansion>

<!-- Read-only (always expanded, not togglable) -->
<ue-expansion title="System Info" readonly>
  <ue-property-list :data="systemInfo" />
</ue-expansion>

<!-- Custom action icons -->
<ue-expansion title="Notifications" has-actions expanded-icon="mdi-bell" collapsed-icon="mdi-bell-off">
  <!-- notification settings -->
</ue-expansion>
```

::: tip vs Collapsible
`ue-expansion` is backed by Vuetify's `v-expansion-panels` and follows its styling system. `ue-collapsible` is a lighter custom component with more layout control (borders, padding, dense mode). For simple single-section toggles in forms or cards, either works; prefer `ue-collapsible` when you need more styling control.
:::

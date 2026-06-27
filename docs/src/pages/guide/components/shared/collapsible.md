---
sidebarPos: 12
sidebarTitle: Collapsible
---
# Collapsible

The `ue-collapsible` component wraps any content in a togglable panel with a clickable header and Vuetify's expand transition. It supports `v-model` for controlled open/close state.

## Usage

```html
<ue-collapsible title="Advanced Options">
  <p>Hidden content shown when expanded.</p>
</ue-collapsible>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | `false` | Controls open/closed state (use with `v-model`) |
| `title` | `String` | `''` | Header text. Can be replaced by the `title` slot |
| `bordered` | `Boolean` | `false` | Add a border and rounded corners around the component |
| `elevated` | `Boolean` | `false` | Add a subtle box shadow |
| `dense` | `Boolean` | `false` | Apply compact spacing |
| `noHeaderBackground` | `Boolean` | `false` | Remove the default faint header background |
| `noCollapse` | `Boolean` | `false` | Disable toggling — content is always visible |
| `horizontalPadding` | `Number` | `4` | Vuetify spacing scale for horizontal padding |
| `verticalPadding` | `Number` | `3` | Vuetify spacing scale for vertical padding |
| `color` | `String` | `'primary'` | Reserved for future use |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `Boolean` | Emitted when open state changes |
| `open` | — | Emitted when the panel opens |
| `close` | — | Emitted when the panel closes |

## Slots

| Slot | Description |
|------|-------------|
| `default` | The collapsible body content |
| `title` | Replaces the header text with custom markup |

## Examples

```html
<!-- Controlled with v-model -->
<ue-collapsible v-model="showDetails" title="Details" bordered>
  <ue-property-list :data="item" />
</ue-collapsible>

<!-- Always open (no-collapse) -->
<ue-collapsible title="Notes" no-collapse elevated>
  <p>{{ item.notes }}</p>
</ue-collapsible>

<!-- Custom title slot -->
<ue-collapsible bordered>
  <template #title>
    <v-icon class="mr-2">mdi-filter</v-icon> Filters
  </template>
  <!-- filter inputs -->
</ue-collapsible>
```

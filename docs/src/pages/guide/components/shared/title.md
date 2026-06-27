---
sidebarPos: 8
sidebarTitle: Title
---
# Title

The `ue-title` component is a flexible, polymorphic text element for headings and labels. It converts typography props into Vuetify utility classes and renders as any HTML element via the `tag` prop.

## Usage

```html
<ue-title text="Section Heading" type="h4" weight="bold" color="primary" />
```

## Props

| Prop | Type | Default | Accepted values |
|------|------|---------|-----------------|
| `text` | `String` | — | Any string |
| `subTitle` | `String` | — | Any string |
| `tag` | `String` | `'div'` | `div`, `h1`–`h6` |
| `type` | `String` | `'body-1'` | `h1`–`h6`, `subtitle-1`, `subtitle-2`, `body-1`, `body-2`, `button`, `caption`, `overline` |
| `weight` | `String` | `'bold'` | `black`, `bold`, `medium`, `regular`, `light`, `thin` |
| `transform` | `String` | `'uppercase'` | `none`, `capitalize`, `lowercase`, `uppercase` |
| `color` | `String` | — | Vuetify color name or hex |
| `bg` | `String` | — | Vuetify background color name |
| `padding` | `String` | `'a-3'` | Vuetify spacing suffix, e.g. `'a-0'`, `'x-2'` |
| `margin` | `String` | `'a-0'` | Vuetify spacing suffix |
| `align` | `String` | `'start'` | `start`, `center`, `end` |
| `justify` | `String` | `'start'` | `start`, `center`, `end`, `space-between` |
| `textPosition` | `String` | `'left'` | `left`, `center`, `right` |
| `classes` | `String\|Array` | — | Extra CSS classes |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `default` | `{text}` | Replaces the default `<span>` with custom content |
| `right` | — | Content anchored to the right side of the title row |

## Examples

```html
<!-- Page section header -->
<ue-title text="Users" type="h3" weight="medium" transform="none" />

<!-- Caption label -->
<ue-title text="Last updated" type="caption" weight="regular" color="grey-darken-1" padding="a-0" />

<!-- Colored badge-style title -->
<ue-title text="Active" type="overline" color="success" bg="success-lighten-5" padding="x-2" />

<!-- With right slot -->
<ue-title text="Orders">
  <template #right>
    <v-btn size="small" icon="mdi-refresh" />
  </template>
</ue-title>
```

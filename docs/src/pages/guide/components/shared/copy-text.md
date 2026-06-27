---
sidebarPos: 10
sidebarTitle: Copy Text
---
# Copy Text

The `ue-copy-text` component renders a clipboard icon that copies a value to the clipboard on click. A "Copied!" tooltip is shown for 2 seconds after a successful copy.

## Usage

```html
<ue-copy-text :text="item.api_key" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `text` | `String\|Number` | required | The value written to the clipboard |
| `icon` | `String` | `'mdi-content-copy'` | MDI icon name for the trigger |
| `color` | `String` | `'primary'` | Icon color |
| `size` | `String` | `'small'` | Icon size (`x-small`, `small`, `default`, `large`, `x-large`) |

## Examples

```html
<!-- Copy an API key -->
<div class="d-flex align-center ga-2">
  <span class="text-caption font-weight-mono">{{ item.api_key }}</span>
  <ue-copy-text :text="item.api_key" />
</div>

<!-- Custom icon and color -->
<ue-copy-text :text="shareUrl" icon="mdi-share-variant" color="secondary" size="default" />
```

::: tip Clipboard Access
Internally uses `this.$copy()`, which wraps the [Clipboard API](https://developer.mozilla.org/en-US/docs/Web/API/Clipboard_API). Copy only works in secure contexts (HTTPS or localhost).
:::

---
sidebarPos: 17
sidebarTitle: SVG Icon
---
# SVG Icon

`ue-svg-icon` renders an inline SVG symbol using the `v-svg` directive. Use it to display icons from a sprite sheet that has been injected into the page.

## Usage

```html
<ue-svg-icon symbol="icon-logo" width="48" height="48" />
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `symbol` | `String` | yes | The SVG `<symbol>` ID to render |
| `width` | `String` | no | Width in pixels |
| `height` | `String` | no | Height in pixels |

## Notes

- The component uses the `v-svg` directive which resolves `symbol` against the application's SVG sprite sheet.
- Width and height are applied as inline styles on the wrapping `<span>`.
- If no `width`/`height` are provided, the SVG scales to fit its container (`max-width: 100%; max-height: 100%`).
- For standard MDI icons, use Vuetify's `v-icon` instead — `ue-svg-icon` is intended for custom brand or project-specific vector icons.

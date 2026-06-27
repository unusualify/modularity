---
sidebarPos: 11
sidebarTitle: Text Display
---
# Text Display

The `ue-text-display` component renders a bold primary value with an optional smaller secondary value aligned to its baseline. Typical use cases are prices, totals, or any figure that needs a unit suffix.

## Usage

```html
<ue-text-display text="$2,500" sub-text="+ VAT" />
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `text` | `String` | yes | The main value to display (bold) |
| `subText` | `String` | no | A secondary label rendered in `text-body-1` aligned to the baseline |

## Examples

```html
<!-- Price with currency suffix -->
<ue-text-display text="€ 1,200" sub-text="/ month" />

<!-- Simple count -->
<ue-text-display text="42" />

<!-- Inside a stepper summary card -->
<ue-text-display class="text-h5 text-white" text="$2500" sub-text="+ VAT" />
```

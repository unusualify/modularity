---
sidebarPos: 26
sidebarTitle: Success
---
# Success

`ue-success` renders a full-page success state with a check-circle icon, a title, a description, and a call-to-action button.

## Usage

```html
<ue-success
  title="Payment Complete"
  description="Your order has been placed successfully."
  button_text="Go to Dashboard"
  button_url="/dashboard"
/>
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `title` | `String` | yes | Heading text displayed below the icon |
| `description` | `String` | yes | Body text below the heading |
| `button_text` | `String` | yes | Label for the action button |
| `button_url` | `String` | yes | `href` of the action button |

## Behaviour

The component uses a vertically and horizontally centred `v-container fill-height` layout. All four props are required — omitting any of them will cause Vue to emit a prop validation warning.

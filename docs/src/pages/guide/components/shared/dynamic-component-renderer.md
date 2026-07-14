---
sidebarPos: 25
sidebarTitle: Dynamic Component Renderer
---
# Dynamic Component Renderer

`ue-dynamic-component-renderer` parses a component tag string and renders the described component dynamically. If the subject is not a recognisable component string, it renders it as raw HTML.

## Usage

```html
<!-- Renders a ue-chip programmatically -->
<ue-dynamic-component-renderer subject="<ue-chip color='success'>Active</ue-chip>" />

<!-- Renders raw HTML if not a component string -->
<ue-dynamic-component-renderer subject="<strong>Bold text</strong>" />
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `subject` | `String` | `''` | An HTML/component string to parse and render |

## Behaviour

- If `subject` starts with `<v-` or `<ue-` and ends with `>`, it is parsed as a Vue component: the tag name is extracted, attributes are bound via `v-bind`, and the inner text is set as the default slot content.
- Otherwise, the string is injected as raw HTML via `v-html`.

::: warning
`ue-dynamic-component-renderer` uses `v-html` for non-component strings. Never pass unsanitised user input.
:::

::: tip When to use
This component is used internally by `ue-recursive-stuff` and `ue-configurable-card` when a segment value is a primitive string. In most cases you should prefer `ue-recursive-stuff` with a full configuration object for more control.
:::

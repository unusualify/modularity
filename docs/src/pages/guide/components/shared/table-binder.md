---
sidebarPos: 36
sidebarTitle: Table Binder
---
# Table Binder

`ue-table-binder` dynamically resolves a `ue-*` component by name and forwards a set of attributes to it. It is a thin bridge that lets server-driven configuration select which table-like component to render without hardcoding the component tag in the template.

## Usage

```html
<ue-table-binder
  component-name="data-table"
  :table-attributes="tableProps"
/>
```

This is equivalent to:

```html
<ue-data-table v-bind="tableProps" />
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `componentName` | `String` | — | Suffix of the `ue-*` component to render (e.g. `'data-table'` renders `ue-data-table`) |
| `tableAttributes` | `Object` | yes | Props forwarded to the resolved component via `v-bind` |

::: tip
`ue-table-binder` is useful in module config files where the table component may vary per context. Pass `componentName` from PHP and keep the Vue template generic.
:::

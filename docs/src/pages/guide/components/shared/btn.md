---
sidebarPos: 7
sidebarTitle: Btn
---
# Btn

The `ue-btn` component is a thin wrapper around Vuetify's `v-btn`. It forwards all Vuetify button props via `$bindAttributes()` and adds a built-in 5-second debounce on click to prevent double-submission.

## Usage

```html
<ue-btn color="primary" variant="elevated" @click="handleSave">
  Save
</ue-btn>
```

## Props

`ue-btn` accepts all [Vuetify `v-btn` props](https://vuetifyjs.com/en/api/v-btn/#props) plus the following:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `revealed` | `Boolean` | `false` | Reserved for future use (toggling a revealed/hidden state) |

## Click Debounce

After each click, `ue-btn` automatically disables itself for **5 seconds** and re-enables. This prevents accidental double-clicks on form submission buttons without any extra logic in the parent.

```html
<!-- The button is disabled for 5 s after each click automatically -->
<ue-btn color="secondary" @click="submitOrder">
  Place Order
</ue-btn>
```

::: warning Disabled Override
If you need to control `disabled` externally (e.g. while an async request is in flight), bind `:disabled="loading"` — this overrides the internal debounce state.
:::

## Common Vuetify Props

| Prop | Example values | Description |
|------|---------------|-------------|
| `color` | `'primary'`, `'error'`, `'#ff5722'` | Button color |
| `variant` | `'elevated'`, `'flat'`, `'tonal'`, `'outlined'`, `'text'`, `'plain'` | Visual style |
| `size` | `'x-small'`, `'small'`, `'default'`, `'large'`, `'x-large'` | Button size |
| `rounded` | `true`, `'xs'`, `'sm'`, `'lg'`, `'xl'`, `'pill'` | Border radius |
| `density` | `'default'`, `'comfortable'`, `'compact'` | Vertical density |
| `prepend-icon` | `'mdi-plus'` | Icon before label |
| `append-icon` | `'mdi-chevron-right'` | Icon after label |
| `icon` | `'mdi-delete'` | Icon-only mode (square button) |
| `loading` | `true` / `false` | Shows a loading spinner |
| `disabled` | `true` / `false` | Disables the button |
| `href` | `'/path'` | Renders as an anchor |
| `block` | `true` / `false` | Full-width button |

See the full API at [Vuetify v-btn](https://vuetifyjs.com/en/api/v-btn/).

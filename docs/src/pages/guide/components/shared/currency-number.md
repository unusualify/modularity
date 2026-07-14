---
sidebarPos: 48
sidebarTitle: Currency Number
---
# Currency Number

`ue-currency-number` is a numeric text field that formats its value as a currency string while editing. It wraps Vuetify's `v-text-field` and delegates formatting logic to the `useCurrencyNumber` composable.

## Usage

```html
<ue-currency-number
  v-model="price"
  label="Price"
/>
```

## Props

| Prop | Type | Description |
|------|------|-------------|
| `modelValue` | `Number` | The numeric value controlled via `v-model` |
| `errorMessages` | `Array` | Validation error messages forwarded to the underlying `v-text-field` |

## Behaviour

- The displayed value is formatted as a currency string (thousands separators, decimal places) while the bound `modelValue` remains a plain `Number`.
- Standard Vuetify `v-text-field` props such as `label`, `error`, `variant`, `density`, and `placeholder` are forwarded via `$bindAttributes`.
- All `v-text-field` slots are forwarded so you can use `#prepend-inner`, `#append-inner`, etc.

::: tip
`ue-currency-number` is typically used as a form schema input type. Set `type: 'currency'` in your field schema to have `ue-form` render it automatically.
:::

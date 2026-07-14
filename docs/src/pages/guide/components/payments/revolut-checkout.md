---
sidebarPos: 39
sidebarTitle: Revolut Checkout
---
# Revolut Checkout

`ue-revolut-checkout` embeds a Revolut card field and handles the full payment flow: card submission, processing overlay, success redirect, and failure handling via `ue-dynamic-modal`.

`ue-revolut-checkout-modal` is a convenience wrapper that opens the checkout inside a `ue-modal`.

## `ue-revolut-checkout`

### Usage

```html
<ue-revolut-checkout
  :token="revolutToken"
  env="sandbox"
  :payment-id="paymentId"
  :revolut-order-id="revolutOrderId"
  :order-id="orderId"
  complete-url="/api/payments/complete"
/>
```

### Props

| Prop | Type | Description |
|------|------|-------------|
| `token` | `String` | Revolut public token for the order |
| `env` | `String` | `'sandbox'` or `'production'` |
| `paymentId` | `String` | Internal payment record ID |
| `revolutOrderId` | `String` | Revolut order ID |
| `orderId` | `String` | Internal order ID |
| `completeUrl` | `String` | Endpoint called after Revolut reports success or failure |

### Behaviour

1. On mount, the Revolut SDK initialises and mounts a card field inside `#embed-form`.
2. When the user clicks **Pay**, the card field is submitted to Revolut.
3. On success, a processing overlay modal is shown and `completeUrl` is called with `status=success`.
4. If the backend returns `variant: 'success'`, the user is redirected to the URL in the response.
5. On failure, a `ue-error-card` modal is opened with the error details.

## `ue-revolut-checkout-modal`

Wraps `ue-revolut-checkout` inside a `ue-modal`, activating it programmatically.

```html
<ue-revolut-checkout-modal
  :token="token"
  env="sandbox"
  :payment-id="paymentId"
  :revolut-order-id="revolutOrderId"
  :order-id="orderId"
  complete-url="/api/payments/complete"
>
  <template #activator="{ open }">
    <v-btn color="primary" @click="open">Pay Now</v-btn>
  </template>
</ue-revolut-checkout-modal>
```

::: warning
The `@revolut/checkout` SDK is loaded as a package dependency. Ensure your Revolut integration is configured in the Modularous payment settings before using this component.
:::

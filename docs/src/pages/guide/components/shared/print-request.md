---
sidebarPos: 32
sidebarTitle: Print Request
---
# Print Request

`ue-print-request` fetches data from an endpoint on mount and displays the result using `ue-text-display`. It re-fetches automatically whenever `payload` changes.

## Usage

```html
<ue-print-request
  :endpoint="/api/orders/summary"
  :payload="{ order_id: orderId }"
  :print-keys="{ text: 'total', subText: 'currency' }"
/>
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `endpoint` | `String` | yes | POST URL to fetch data from |
| `payload` | `Object` | yes | Request body sent with the POST request; watched deeply for re-fetch |
| `printKeys` | `Array\|Object` | yes | Describes how to extract display values from the response (see below) |
| `loadingText` | `String` | `'Loading...'` | Text shown below the spinner while fetching |

## `printKeys` format

`printKeys` can be:

- **A single object** `{ text: 'fieldName', subText: 'fieldName' }` — used when the response is a single object.
- **An array of strings** `['field1', 'field2']` — each item maps to a display row when the response is an array.
- **An array of objects** `[{ text: 'field', subText: 'field' }, ...]` — explicit per-row mapping for array responses.

## Behaviour

- Renders a spinner with `loadingText` while the request is in-flight.
- For array responses, each element is rendered as a separate `ue-text-display`.
- For single-object responses, one `ue-text-display` is rendered.
- Errors are logged to the console; the component remains in the loading state if the request fails.

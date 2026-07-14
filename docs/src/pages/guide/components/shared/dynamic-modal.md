---
sidebarPos: 22
sidebarTitle: Dynamic Modal
---
# Dynamic Modal

`ue-dynamic-modal` is a singleton dialog that is mounted once at the application root and driven entirely by the `modalService` injection. Use it to open any component inside a modal programmatically — without needing to place `ue-modal` in every view.

## How It Works

The `useDynamicModal()` composable returns a service object with `open()` and `close()` methods. Calling `open()` populates the singleton's internal state, which causes the modal to render the target component in its `body.description` slot.

```js
import { useDynamicModal } from '@/hooks'

const DynamicModal = useDynamicModal()

DynamicModal.open('my-component-name', {
  props:      { /* props forwarded to the component */ },
  modalProps: { title: 'My Title', widthType: 'md' },
  emits:      { confirm: () => { /* handle confirm */ } },
})

DynamicModal.close()
```

## Service API

| Method | Signature | Description |
|--------|-----------|-------------|
| `open` | `(component, options)` | Render `component` inside the modal |
| `close` | `()` | Close the modal |

### `options` object

| Key | Type | Description |
|-----|------|-------------|
| `props` | `Object` | Props forwarded to the dynamic component |
| `modalProps` | `Object` | Props forwarded to the underlying `ue-modal` |
| `emits` | `Object` | Event handlers keyed by event name |
| `slots` | `Object` | Configuration-driven slot content for `ue-recursive-stuff` |
| `data` | any | Arbitrary data available inside the rendered component via `inject('modalRef').data` |

## Inside the Rendered Component

The component rendered inside `ue-dynamic-modal` can access a `modalRef` injection:

```js
import { inject } from 'vue'

const modalRef = inject('modalRef')
modalRef.close()    // close the modal
modalRef.data       // the data passed in options.data
```

## Example — Open a Confirmation Component

```js
const DynamicModal = useDynamicModal()

DynamicModal.open('ue-error-card', {
  props: { statusCode: 403, statusText: 'Not Allowed' },
  modalProps: { title: 'Access Denied', widthType: 'sm', noActions: true },
})
```

::: tip Mounting
`ue-dynamic-modal` is already mounted inside `ue-auth` and the main layout (`ue-main`). You do not need to add it to your views manually.
:::

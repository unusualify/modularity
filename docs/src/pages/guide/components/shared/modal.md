---
sidebarPos: 21
sidebarTitle: Modal
---
# Modal

`ue-modal` is the primary dialog component. It wraps Vuetify's `v-dialog` and provides a standardised card layout with a title, description body, and confirm/cancel action buttons — all customisable through props and slots.

## Usage

```html
<!-- v-model controlled -->
<ue-modal v-model="showDialog" title="Delete Record" description="Are you sure?">
</ue-modal>

<!-- ref controlled (imperative API) -->
<ue-modal ref="confirmModal" title="Confirm">
</ue-modal>
<!-- open programmatically -->
<script>
  this.$refs.confirmModal.open()
</script>
```

## Common Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | `false` | Controls dialog open state with `v-model` |
| `title` | `String` | — | Dialog title |
| `description` | `String` | — | Body text (supports HTML via `v-html`) |
| `widthType` | `String` | `'sm'` | Preset width: `'xs'`, `'sm'`, `'md'`, `'lg'`, `'xl'`, `'full'` |
| `persistent` | `Boolean` | `false` | Prevent closing by clicking outside |
| `confirmText` | `String` | i18n `fields.confirm` | Label for the confirm button |
| `cancelText` | `String` | i18n `fields.cancel` | Label for the cancel button |
| `noCancelButton` | `Boolean` | `false` | Hide the cancel button |
| `noConfirmButton` | `Boolean` | `false` | Hide the confirm button |
| `noActions` | `Boolean` | `false` | Remove the entire actions row |
| `hasCloseButton` | `Boolean` | `false` | Show an × icon in the title bar |
| `hasFullscreenButton` | `Boolean` | `false` | Show a fullscreen toggle in the title bar |
| `hasTitleDivider` | `Boolean` | `false` | Add a divider below the title |
| `confirmCallback` | `Function` | — | Async function called on confirm — return `false` to keep modal open |
| `rejectCallback` | `Function` | — | Async function called on cancel |
| `titleJustify` | `String` | `'start'` | Title alignment (`start`, `center`, `end`) |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `default` | `{close, confirm, open, toggleFullscreen, isFullActive}` | Full custom content — replaces the default card entirely |
| `body` | same scope | Replaces the default card body while keeping the dialog wrapper |
| `body.description` | `{description}` | Replaces the description text area |
| `body.options` | `{description}` | Replaces the action buttons row |
| `systembar` | — | Injected above the card when `hasSystembar` is active |

## Events

| Event | Description |
|-------|-------------|
| `update:modelValue` | Emitted on open/close |
| `opened` | Emitted when the dialog becomes visible |
| `confirm` | Emitted after confirm resolves |
| `cancel` | Emitted after cancel resolves |

## Imperative API

When used with a template `ref`, the modal exposes these methods:

```js
this.$refs.myModal.open()       // open the dialog
this.$refs.myModal.close()      // close the dialog
this.$refs.myModal.toggle()     // toggle open state
this.$refs.myModal.confirm()    // trigger confirm flow programmatically
```

## Example — Confirm Delete

```html
<ue-modal
  ref="deleteModal"
  title="Delete Item"
  description="This action cannot be undone."
  width-type="sm"
  has-close-button
  :confirm-callback="handleDelete"
>
</ue-modal>

<v-btn color="error" @click="$refs.deleteModal.open()">Delete</v-btn>
```

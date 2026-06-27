---
sidebarPos: 34
sidebarTitle: Logout Modal
---
# Logout Modal

`ue-logout-modal` is a confirmation dialog that submits a Laravel logout POST request on confirm. The activator button can be replaced via slot.

## Usage

```html
<!-- Default activator (a red "Logout" button) -->
<ue-logout-modal />

<!-- Custom activator -->
<ue-logout-modal>
  <template #activator="{ props }">
    <v-list-item v-bind="props" title="Sign out" prepend-icon="mdi-logout" />
  </template>
</ue-logout-modal>
```

## Props

| Prop | Type | Description |
|------|------|-------------|
| `csrf` | `String` | CSRF token injected into the hidden `_token` form field. Falls back to `$csrf()` if omitted |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `activator` | `{ props }` | Replace the default logout button. Bind `props` to the element that should open the modal |

## Behaviour

- Dialog title, description, and button labels are resolved through the i18n keys `authentication.logout-title`, `authentication.logout-description`, `authentication.logout-cancel`, and `authentication.logout-confirm`.
- On confirm, a standard HTML form `POST /logout` is submitted with the CSRF token.
- The modal width is `md`.

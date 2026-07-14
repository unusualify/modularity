---
sidebarPos: 6
sidebarTitle: Alert
---
# Alert

The `ue-alert` component is a global snackbar notification tied to the Vuex alert store. It is mounted once at the app root and reacts to any commit to `ALERT.SET_ALERT`. You never place it directly inside page templates — it is included automatically by the Modularous layout.

## How It Works

Any service, form, or component can trigger a notification by committing to the store:

```js
this.$store.commit(ALERT.SET_ALERT, {
  type: 'success',
  message: 'Record saved successfully.',
})
```

The alert component picks up the commit and displays the snackbar at the configured location with the given type and message.

## Alert Types

| Type | Color | Default message key |
|------|-------|---------------------|
| `success` | green | `messages.success` |
| `error` | red | `messages.error` |
| `warning` | orange | `messages.warning` |
| `info` | blue | `messages.info` |

Default messages are resolved through the i18n system (`$t('messages.<type>')`). Pass a `message` string to override.

## Store Payload

| Key | Type | Description |
|-----|------|-------------|
| `type` | `String` | One of `success`, `error`, `warning`, `info` |
| `message` | `String` | Custom message text. Falls back to the i18n default if omitted |
| `location` | `String` | Vuetify snackbar `location` prop (e.g. `'top'`, `'bottom right'`). Defaults to `'bottom'` |

## Programmatic Usage

```js
// From inside a Vue component
import { ALERT } from '@/store/mutations/index'

// Success
this.$store.commit(ALERT.SET_ALERT, { type: 'success' })

// Error with custom message
this.$store.commit(ALERT.SET_ALERT, {
  type: 'error',
  message: 'Something went wrong. Please try again.',
})

// Show at top of viewport
this.$store.commit(ALERT.SET_ALERT, {
  type: 'info',
  message: 'New update available.',
  location: 'top',
})
```

::: info Auto-dismissal
The snackbar auto-dismisses after 3 000 ms by default. The timeout is not currently exposed as a store payload key; use the component's `open()` method if you need a custom timeout from inside a child component.
:::

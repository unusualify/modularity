---
sidebarPos: 31
sidebarTitle: Impersonate Toolbar
---
# Impersonate Toolbar

`ue-impersonate-toolbar` lets administrators impersonate another user from the sidebar. It renders either a user-search input (when not impersonating) or a "Stop Impersonating" button (when actively impersonating).

## Usage

```html
<ue-impersonate-toolbar
  v-model="showToolbar"
  :impersonated="isImpersonating"
  :fetch-endpoint="/api/users/search"
  route="/users/impersonate/:id"
  stop-route="/users/impersonate/stop"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Boolean` | `false` | Controls toolbar visibility via `v-model` |
| `active` | `Boolean` | `false` | Whether the impersonation feature is enabled at all |
| `impersonated` | `Boolean` | `false` | `true` when the current session is already impersonating a user |
| `users` | `Array` | `[]` | Static list of users — used when `fetchEndpoint` is not provided |
| `fetchEndpoint` | `String` | `null` | API endpoint for live user search (powers `v-input-browser`) |
| `route` | `String` | `'/users/impersonate/:id'` | Impersonate URL template; `:id` is replaced with the selected user id |
| `stopRoute` | `String` | `'/users/impersonate/stop'` | URL to navigate to when stopping impersonation |
| `itemTitle` | `String` | `'name'` | Field used as the display label in the user dropdown |
| `itemValue` | `String` | `'id'` | Field used as the option value |
| `density` | `String` | `'comfortable'` | Vuetify density for the input |
| `variant` | `String` | `'outlined'` | Vuetify variant for the input |

## Behaviour

- When `impersonated` is `true`, a red "Stop Impersonating" list item is shown. Clicking it navigates to `stopRoute`.
- When `impersonated` is `false`, a user search input is shown. Selecting a user immediately redirects to the `route` URL with the selected id substituted.
- This component is rendered automatically inside `ue-main` when the `impersonation.active` option is set in the main layout configuration.

---
sidebarPos: 16
sidebarTitle: Error Card
---
# Error Card

`ue-error-card` renders a full-featured HTTP error page inside a `v-card`. It displays a status code, title, description, an alert, and navigation buttons (Go Back / Home). All text is passed through `$t()` for i18n support.

## Usage

```html
<!-- 403 Forbidden (defaults) -->
<ue-error-card />

<!-- 404 Not Found -->
<ue-error-card
  :status-code="404"
  status-text="Page Not Found"
  description="The page you are looking for does not exist."
  alert="info"
  icon="mdi-alert-circle-outline"
  home-url="/dashboard"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `statusCode` | `Number` | `403` | HTTP status code displayed as a large heading |
| `statusText` | `String` | `'Access Forbidden'` | Title below the status code |
| `description` | `String` | `'You don\'t have permission to access this resource.'` | Body copy |
| `alertText` | `String` | `'This action is restricted for modularous authenticated users.'` | Text inside the alert box |
| `alert` | `String` | `'warning'` | Alert type: `'warning'`, `'error'`, `'info'`, `'success'` |
| `icon` | `String` | `'mdi-lock-outline'` | MDI icon shown at the top of the card |
| `iconSize` | `Number` | `120` | Icon size in pixels |
| `elevation` | `Number` | `8` | Card shadow elevation |
| `rounded` | `String` | `'lg'` | Card border radius |
| `homeUrl` | `String` | `'/'` | URL for the "Home" button |
| `homeText` | `String` | `'Home'` | Label for the "Home" button |

## Examples

```html
<!-- 500 Server Error -->
<ue-error-card
  :status-code="500"
  status-text="Server Error"
  description="Something went wrong on our end."
  alert="error"
  icon="mdi-server-off"
  home-url="/dashboard"
  home-text="Back to Dashboard"
/>
```

::: tip Usage in Blade Views
In Modularous, permission and access errors are typically caught in middleware and redirected to a dedicated error Blade view. Place `<ue-error-card />` inside the page's Vue mount to take advantage of the built-in navigation buttons.
:::

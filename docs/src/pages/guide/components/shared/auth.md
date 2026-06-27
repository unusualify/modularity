---
sidebarPos: 20
sidebarTitle: Auth
---
# Auth

`ue-auth` is the full-page authentication layout component. It renders the branded card, logo, optional banner, the form slot, and an optional divider + bottom slot (e.g. for OAuth buttons). The global `ue-alert` and `ue-dynamic-modal` are also mounted here so they're available on auth pages.

## Usage

```html
<ue-auth>
  <!-- ue-form for login/register goes in the default slot -->
  <ue-form :model-value="form" :schema="schema" action-url="/login" has-submit />
</ue-auth>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `logoLightSymbol` | `String` | `'main-logo-light'` | SVG sprite symbol used for the logo inside the card |
| `logoSymbol` | `String` | `'main-logo-dark'` | Dark-variant symbol (reserved) |
| `noDivider` | `Boolean\|Number` | `false` | Hide the divider between form and `bottom` slot |
| `noSecondSection` | `Boolean\|Number` | `false` | Hide the banner (`description` slot) above the form |
| `slots` | `Object` | `{}` | Reserved for server-side slot injection |

## Slots

| Slot | Description |
|------|-------------|
| `default` | The auth form — place `ue-form` here |
| `description` | Banner content shown above the card's form sheet |
| `cardTop` | Injected at the very top of the card, before the banner |
| `bottom` | Content below the divider — typically OAuth / SSO buttons |

## Layout Config

Card width per breakpoint can be customised via the global `window.__MODULAROUS_AUTH_CONFIG__` object:

```js
window.__MODULAROUS_AUTH_CONFIG__ = {
  formWidth: {
    xs: '90vw',
    sm: '420px',
    md: '460px',
    lg: '500px',
  },
  dividerText: 'or continue with',
}
```

## Example — Login Page

```html
<ue-auth>
  <template #description>
    <h2 class="text-h5 font-weight-bold">Welcome back</h2>
    <p class="text-body-2 text-medium-emphasis">Sign in to your account</p>
  </template>

  <ue-form :model-value="credentials" :schema="loginSchema" action-url="/login" has-submit button-text="Sign In" />

  <template #bottom>
    <v-btn block variant="outlined" prepend-icon="mdi-google" href="/auth/google">
      Continue with Google
    </v-btn>
  </template>
</ue-auth>
```

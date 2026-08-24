---
sidebarPos: 7
sidebarTitle: Page Definitions
---

# Page Definitions

Each auth page (login, register, forgot_password, etc.) is defined under `auth_pages.pages.[key]`.

## Page Keys

| Key | Route / Controller | Description |
|-----|-------------------|-------------|
| `login` | Login | Sign in form |
| `register` | Register | Registration form |
| `pre_register` | Pre-register | Email verification before register |
| `complete_register` | CompleteRegister | Finish registration after email verification |
| `forgot_password` | ForgotPassword | Request password reset email |
| `reset_password` | ResetPassword | Set new password with token |
| `oauth_password` | OAuth | Link OAuth provider to account |

## Page Configuration Keys

| Key | Type | Description |
|-----|------|-------------|
| `pageTitle` | string | Page title (translation key or literal) |
| `layoutPreset` | string | `banner`, `minimal`, `minimal_no_divider` |
| `formDraft` | string | Form draft name (e.g. `login_form`) |
| `actionRoute` | string | Route name for form submission |
| `formTitle` | object/string | Form title structure |
| `buttonText` | string | Submit button text (translation key) |
| `formSlotsPreset` | string | Preset for form slots (options, restart, etc.) |
| `slotsPreset` | string | Preset for bottom slots (OAuth, links) |
| `formTitleOverrides` | array | Merge into `ue-form` title (`margin`, `padding`, `class`, …) |
| `formOverrides` | array | Override form attributes |
| `attributes` | array | Per-page attributes for auth component |

## Form Slots Presets

| Preset | Description |
|--------|-------------|
| `login_options` | Forgot password link (beside submit) |
| `login_forgot_below` | Forgot password link as a block under submit |
| `sign_in_below` | Sign in link as a block under submit |
| `register_have_account_below` | "Already have account?" link as a block under submit |
| `complete_register_restart_below` | Restart link as a block under submit |
| `have_account` | "Already have account?" link (beside submit) |
| `restart` | Restart link (beside submit) |
| `restart` | Restart registration button |
| `resend` | Resend verification button |
| `oauth_submit` | OAuth submit button |
| `forgot_password_form` | Sign in + Reset password buttons |

## Slots Presets (Bottom)

| Preset | Description |
|--------|-------------|
| `login_bottom` | OAuth Google + Create account (outlined) |
| `login_bottom_v2` | OAuth Google + Create account (text link) |
| `register_bottom` | OAuth Google |
| `forgot_password_bottom` | OAuth Google + Create account (outlined) |
| `forgot_password_bottom_v2` | OAuth Google + Create account (text link) |

## Example: Full Page Definition

```php
'login' => [
    'pageTitle' => 'authentication.login',
    'layoutPreset' => 'banner',
    'formDraft' => 'login_form',
    'actionRoute' => 'admin.login',
    'formTitle' => 'authentication.login-title',
    'buttonText' => 'authentication.sign-in',
    'formSlotsPreset' => 'login_options',
    'slotsPreset' => 'login_bottom',
    'attributes' => [
        'bannerDescription' => __('authentication.login-banner'),
    ],
],
```

## Overriding in App Config

Override any page in `modularous/auth_pages.php`:

```php
return [
    'pages' => [
        'login' => [
            'layoutPreset' => 'minimal',
            'attributes' => [
                'bannerDescription' => 'Custom login banner',
            ],
        ],
    ],
];
```

Merging is shallow for `pages`; your keys replace package defaults for that page.

---
sidebarPos: 7
sidebarTitle: ImpersonateMiddleware
---

# ImpersonateMiddleware

**File**: `src/Http/Middleware/ImpersonateMiddleware.php`  
**Alias**: `modularous.impersonate`  
**Part of**: `modularous.core` group

Activates user impersonation for the current request and shares the impersonation config with the master Blade layout.

## How Impersonation Works

When an admin starts impersonating another user, the target user's ID is stored in the session under the `'impersonate'` key. On every subsequent request this middleware:

1. Checks for `session('impersonate')`.
2. Stores the real (impersonator) user on `$request->attributes->get('impersonator')` so controllers can authorize against them.
3. Calls `auth()->guard(Modularous::getAuthGuardName())->onceUsingId($targetId)` — this swaps the authenticated user **for the current request only**, without affecting the session's real authenticated user.
4. Composes `modularous::layouts.master` with the impersonation config (`get_modularous_impersonation_config()`), which provides the frontend with the data needed to render the "stop impersonating" banner.

## Impersonation Config

`get_modularous_impersonation_config()` returns an array used by the admin panel to:

- Show or hide the impersonation banner.
- Provide the "stop impersonating" route.
- Display the impersonated user's name.

## Starting / Stopping Impersonation

Impersonation is controlled by two **POST** routes (CSRF-protected; `GET` returns `405`):

| Method | URI | Route | Action |
|--------|-----|-------|--------|
| `POST` | `users/impersonate/{id}` | `admin.impersonate` | Sets `session('impersonate', $userId)` |
| `POST` | `users/impersonate/stop` | `admin.impersonate.stop` | Removes `session('impersonate')` |

`ImpersonateController` authorizes the **impersonator** (falls back to the guard user) with the `impersonate` gate (`$user->is_superadmin`), so a superadmin can switch directly from one impersonated user to another. Requests sending `Accept: application/json` receive JSON (`{"impersonating": true, "user_id": 42}` / `{"impersonating": false}`); browser requests are redirected back.

```bash
curl -X POST https://app.example.test/users/impersonate/42 \
  -H 'Accept: application/json' -H "X-XSRF-TOKEN: $XSRF" -b cookies.txt
```

`ImpersonateToolbar.vue` posts with axios (Laravel's `XSRF-TOKEN` cookie) and reloads the page.

These routes are excluded from the intended-URL store in `AuthenticateMiddleware`.

## Dev auto-login (local only)

`MODULAROUS_DEV_AUTO_LOGIN=true` authenticates every `web.auth` request as a superadmin with `onceUsingId()` — no login form, no login session — so agents and browsers can open panel URLs directly.

| Env | Config | Default |
|-----|--------|---------|
| `MODULAROUS_DEV_AUTO_LOGIN` | `modularous.dev_auth.enabled` | `false` |
| `MODULAROUS_DEV_AUTO_LOGIN_EMAIL` (falls back to `MODULAROUS_SUPERADMIN_EMAIL`) | `modularous.dev_auth.email` | first user with the `superadmin` role when empty |
| — | `modularous.dev_auth.allowed_environments` | `['local', 'testing']` |

- `DevAuthServiceProvider` throws on boot when the flag is on outside the allowed environments.
- `DevAutoLoginMiddleware` (`modularous.dev_auto_login`) is inserted into `web.auth` between `web` and `modularous.auth`, and into the router middleware priority right before `AuthenticatesRequests`.
- Responses authenticated this way carry `X-Dev-Auto-Login: <email>`; guest pages (`/login`, …) redirect to `auth_login_redirect_path`.
- `php artisan modularous:dev-auth:status [--json]` reports the flag, environment, resolved user, config/route caches and the Vite hot file, with fix hints (exit code `0` when active).
- Already authenticated users are kept; `ImpersonateMiddleware` runs afterwards, so impersonation still works on top of the auto-login.
- Run `php artisan config:clear` (and `route:clear` if routes are cached) after toggling the flag.

## Notes

- `onceUsingId()` is request-scoped — the real session user is never changed. If the browser is closed and reopened, the impersonation is gone.

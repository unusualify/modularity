---
sidebarPos: 2
sidebarTitle: Public routing
sidebarGroupTitle: Cms
outline: deep
---

# Public routing

How public HTTP routes are registered so CMS pages, system endpoints, and host routes coexist.

## Registration order

Laravel uses **first match wins**. Modularous registers in this order:

1. **Built-in system routes** (`CmsPublicSystemRoutes`) — boot — `/robots.txt`, `/sitemap.xml`, `/sitemap.xsl`, signed preview, public stylesheets
2. **Host app** — `routes/web.php` (framework `booted`)
3. **CMS catch-all** — `GET {path}` → `CmsPublicFrontController` (later `booted`)

A host route such as `/test` in `routes/web.php` therefore beats the CMS catch-all. No package change required.

## Where to define routes

| What | Where |
|------|--------|
| CMS product endpoints | `modules/Cms/Routing/CmsPublicSystemRoutes.php` |
| App-specific paths | Host `routes/web.php` |
| Third-party paths still eaten by `{path}` | Config exclude (below) |

Enabled system prefixes are removed from the catch-all `{path}` regex via `CmsPublicSystemRoutes::reservedPathPrefixes()` (also used by visitor-redirect / URL-stale skip logic).

## Catch-all exclude config

**Usually not needed** for `routes/web.php` — order already wins.

Use `modularous.cms_routing.public_front_catch_all_exclude_path_prefixes` when:

- Another package registers **after** the catch-all (or on a domain that never matches), so `{path}` still wins
- You want belt-and-suspenders so CMS never treats that path as a page
- Stale/redirect middleware should ignore the path (same reserved list)

```php
'public_front_catch_all_exclude_path_prefixes' => [
    'health',
    'horizon',
],
```

Slash-trimmed; `health` blocks `health` and `health/...`.

## Domain binding

Public CMS routes use `CmsFrontRouteRegistrar::resolvePublicFrontRouteDomain()` (host from `APP_URL` or `public_front_route_domain`). System routes bind the same way when registered through the catalog.

## Related config

`modularous.cms_routing.*` — auto-register, universal front, localization mode, signed preview, visitor redirects. See [Configuration](./configuration).

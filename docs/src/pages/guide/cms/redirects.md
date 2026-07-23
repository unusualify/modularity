---
sidebarPos: 7
sidebarTitle: Redirects
sidebarGroupTitle: Cms
outline: deep
---

# Redirects

Visitor redirects are CMS rules evaluated on the public front stack before (or instead of) resolving a page.

## Entity

**Redirect**: `from_path`, `to_path`, `locale`, `status_code`, `is_active`.

On save/delete, `RedirectRepository` syncs a UrlRoute with `kind = redirect_source` for the from-path (`CmsUrlRouteRegistry`).

## Runtime

1. Front middleware `modules.cms.visitor.redirect` (`VisitorRedirectMiddleware`) when `cms_routing.visitor_redirects_enabled`
2. `CmsVisitorRedirectResolver`:
   - Skips admin/api/system and [reserved system prefixes](./public-routing)
   - If an active **`page_public`** UrlRoute exists for the path → **no redirect** (page wins)
   - Else match `redirect_source` UrlRoute → active Redirect, or fall back to scanning Redirect rows
3. Issues HTTP redirect (clamped 3xx; absolute or site-relative `to_path`)

## Config

- `modularous.cms_routing.visitor_redirects_enabled`
- `modularous.cms_routing.visitor_redirect_exclude_prefixes` (e.g. `api`, `sanctum`, `livewire`) — separate from catch-all excludes

See [Configuration](./configuration).

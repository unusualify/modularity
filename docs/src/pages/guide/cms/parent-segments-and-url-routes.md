---
sidebarPos: 3
sidebarTitle: Parent segments & URL routes
sidebarGroupTitle: Cms
outline: deep
---

# Parent segments & URL routes

Public URLs are data, not one Laravel route per page.

## ParentSegment

One row per **target model class + locale**: the shared path prefix for that type (e.g. `pages`, or empty for locale root).

- Table / entity: `ParentSegment`
- Config: `modularous.cms_parent_segments`
- Models opt in with `HasParentSegment` (bundled on `IsCmr`)

Changing a prefix can resync UrlRoute rows for that model class (`cms_routing.resync_registry_after_parent_segments_change`).

## UrlRoute

Flat registry of published paths:

| Field | Meaning |
|-------|---------|
| `locale` | Locale code |
| `normalized_path` | Path without leading slash |
| `urlable` | Morph to Page (or other CMR) / Redirect |
| `kind` | `page_public` or `redirect_source` |

Synced on entity save via `CmsUrlRouteRegistry` (prefix + slug leaf, or singular prefix-only paths).

## Public resolve

1. Catch-all receives the request ([Public routing](./public-routing)).
2. `CmsVisitorRedirectResolver` derives locale + inner path.
3. `CmsPublicModelResolver` finds a `page_public` UrlRoute (with slugless / implicit-locale rules).
4. ParentSegment registry gate must allow the model class.
5. Controller renders presentation ([Page layouts](./page-layouts)).

Universal mode (`cms_routing.universal_cms_public_front`, default true): one `CmsPublicFrontController` for all ParentSegment targets. Legacy mode registers per-module catch-alls when that flag is false.

## Config

Primary: `modularous.cms_routing`, `modularous.cms_parent_segments`. See [Configuration](./configuration).

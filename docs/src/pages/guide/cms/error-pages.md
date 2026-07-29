---
sidebarPos: 8
sidebarTitle: Error pages
sidebarGroupTitle: Cms
outline: deep
---

# Error pages

The **ErrorPage** module manages HTTP **403 / 404 / 500** responses. CMS records are optional: package **builtin Blade views** under `error_page::error_page.{code}` are full documents that `@extends('error_page::error_page.standalone')` with Vuetify content. When a host theme body override exists and a LayoutBuilder shell is bound, that override is injected into the marketing shell instead.

Unlike CMR content, error pages have **no public URL**, no ParentSegment, and no slug — they are never resolved through the CMS catch-all.

Runtime rendering is handled by `Modules\ErrorPage\Support\ErrorPageRenderer`, registered from `ErrorPageServiceProvider` for `HttpExceptionInterface` status codes `403`, `404`, and `500`.

## Feature flags

| Config / env | Default |
|--------------|---------|
| `modularous.cms_features.error_pages_enabled` / `MODULAROUS_CMS_ERROR_PAGES_ENABLED` | `true` |
| `modularous.cms_features.error_pages_cache_enabled` / `MODULAROUS_CMS_ERROR_PAGES_CACHE_ENABLED` | `true` |

When **error_pages_enabled** is off, the renderer returns `null` and the exception `renderable` skips ErrorPage → **Laravel’s default** error page.

When **error_pages_cache_enabled** is off (or `presentationItem.store=none` / module `presentationItem` type off), published ErrorPage HTML is rendered on every request. Cache is model-scoped (`ErrorPagePresentationCache` / `StaleFileCache`) and cleared on ErrorPage save/delete. Admin purge/warm use that same store — warm delegates via `ErrorPagePresentationCache::warm()` (ErrorPage has no UrlRoute, so the generic CMS path warm is skipped).

## Resolution strategy

1. Feature flag off → Laravel.
2. Lookup `ErrorPage` by `error_code` (skipped if the table is missing → treat as no row).
3. **Found + unpublished** → Laravel (CMS intentionally disabled for that code).
4. **Found + published** → CMS path: host/package body override inside LayoutBuilder when both exist; otherwise package builtin full document.
5. **Not found** → builtin fallback: theme body override (plain document) or Vuetify builtin full page (`@extends` standalone + ModularousVite).

| Condition | Result |
|-----------|--------|
| Flag off | Laravel |
| Unpublished row | Laravel |
| Published row + override + LayoutBuilder | LayoutBuilder shell + body fragment |
| Published row, no usable override | Builtin full document |
| No row / table missing | Builtin (+ optional app override) |

Unpublished ≠ missing: unpublishing opts out; absence still uses builtin fallbacks.

### Traits

| Entity | Repository |
|--------|------------|
| `HasPageLayout` + `Publishable` + `HasScopes` | `PageLayoutTrait` + `PublishableTrait` |

Do **not** use `IsCmr` / `HasParentSegment` — error pages are not front URL routes.

## Why seed at all?

Seeding is **optional** for seeing a non-Laravel error page. Use `modularous:create:error-page-defaults` when you want:

- Admin-managed published/unpublished toggles per code
- A `PageLayout` binding so a **host body override** sits inside a LayoutBuilder shell (head / body / footer)

Without seeding, visitors still get the Vuetify module builtin (or your app body override) as a standalone HTML document.

## Activation & command

1. Enable the module (`modules_statuses.json` → `"ErrorPage": true`).
2. Ensure `cms_features.error_pages_enabled` is true (default).
3. Migrate if you want CMS rows: `php artisan modularous:migrate ErrorPage`.
4. Optionally seed defaults:

```bash
php artisan modularous:create:error-page-defaults
```

Optional flags:

```bash
# Bind PageLayout to a specific LayoutBuilder slug
php artisan modularous:create:error-page-defaults --layout-slug=your-theme

# Re-publish / update existing ErrorPage rows
php artisan modularous:create:error-page-defaults --force
```

LayoutBuilder resolution (first match wins): `--layout-slug` → `cms_layout_builder.default_layout_slug` → `CmsPageLayoutResolver::defaultLayoutBuilder()` (includes `cms_page_layouts.default_layout_builder_id`). The package does **not** default to a host-specific theme slug.

If no LayoutBuilder is resolved, the command still creates/updates `ErrorPage` rows and warns that `PageLayout` will be stored without `layout_builder_id` (standalone shell until a builder is linked).

## Body priority (highest first)

Given status `404` and a resolved LayoutBuilder slug (from PageLayout / config / default builder):

| Priority | View name | Role |
|----------|-----------|------|
| 1 | `cms.layout_builder.{slug}.404` | App theme **body override** — disk: `resources/views/cms/layout_builder/{slug}/404.blade.php`. **Body fragment only**. |
| 2 | `cms::layout_builder.{slug}.404` | Package Cms namespace fallback for the same segment/code |

Package builtin UI (no usable override): `error_page::error_page.404` which `@extends('error_page::error_page.standalone')` with:

```blade
ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
```

(`core-free.js` mounts Vue/Vuetify on `#admin`, same pattern as other free Modularous layouts; not Inertia.)

Gates before body resolution: feature flag off or unpublished row → Laravel (no body render). Candidate list for shell fragments: `ErrorPageRenderer::bodyViewCandidates()` (overrides only).

App overrides apply for **both** published CMS and no-record builtin paths.

### Host app example

Marketing / theme 404 body override (host app, not the package):

```
resources/views/cms/layout_builder/{your-layout-slug}/404.blade.php
```

Set `MODULAROUS_CMS_LAYOUT_DEFAULT_SLUG` (or seed PageLayout with that builder). Without seeding, unknown URLs render that override via the plain document (or the Vuetify builtin via standalone). After seeding + PageLayout + override, the same body sits inside the LayoutBuilder shell.

## View namespaces

| Namespace / pattern | Meaning | Typical path |
|---------------------|---------|--------------|
| `error_page::…` | ErrorPage module views (snake of `ErrorPage`) | Package: `modules/ErrorPage/Resources/views/…` → e.g. `error_page::error_page.404`, `error_page::error_page.standalone`, `error_page::error_page.plain` |
| `cms.layout_builder.{slug}.{segmentOrCode}` | Host app LayoutBuilder theme (no `::`) | `resources/views/cms/layout_builder/{slug}/{segmentOrCode}.blade.php` |
| `cms::layout_builder.{slug}.{segmentOrCode}` | Cms module / package fallback | Cms module views under `layout_builder/{slug}/` |

Convention matches other CMS themes: see [Layout builders](./layout-builders) for shell segments (`head` / `body` / `footer`); error codes reuse the same `{slug}` folder for body overrides (`404`, `403`, `500`).

After editing views: `php artisan view:clear`.

## Panel

Admin route config: `error-pages` with Selectable `error_code` (`403` / `404` / `500`, unique) and a `published` switch.

## Related

- [CMS Overview](./overview) — public presentation stack
- [Page layouts](./page-layouts) — `HasPageLayout` binding used by ErrorPage
- [Layout builders](./layout-builders) — document shell around the error body

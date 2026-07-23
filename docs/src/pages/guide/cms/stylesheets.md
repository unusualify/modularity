---
sidebarPos: 4
sidebarTitle: Stylesheets
sidebarGroupTitle: Cms
outline: deep
---

# Stylesheets

A **StyleSheet** is a compiled CSS bundle (optional SCSS / raw CSS / utilities) plus framework metadata. [Layout builders](./layout-builders) attach sheets; public pages receive `<link>` tags via `StylesheetManager`.

Unlike LayoutBuilder / PageLayout, stylesheets are **not** Blade `blade_source` trees — output is CSS on a filesystem disk + an optional public HTTP route.

## Ordering

For one sheet (`linkHrefsForSheet`):

1. Framework link hrefs (from sheet `definition` + config maps: Bootstrap / Tailwind)
2. Public compiled bundle URL (`cms.public.stylesheet`) when the sheet is **published** and the public route is enabled

For a LayoutBuilder: primary `style_sheet_id`, then each `style_sheet_slugs` entry (published only; missing/unpublished skipped; duplicate ids/hrefs deduped).

## Where compiled CSS lives

| Config | Default | Meaning |
|--------|---------|---------|
| `cms_stylesheets.compiled_disk` | `local` | Laravel filesystem disk |
| `cms_stylesheets.compiled_directory` | `modularous/cms/stylesheets` | Directory on that disk (no leading/trailing slash) |

Typical local path: `storage/app/modularous/cms/stylesheets/…` (depends on the `local` disk root).

Persisted via `StyleSheetRepository`. Optional SCSS: `cms_stylesheets.scssphp.enabled` (default **false**; needs `scssphp/scssphp`).

Other caps:

| Key | Default |
|-----|---------|
| `max_raw_css_bytes` | `512000` |
| `max_inline_vendor_css_bytes` | `2000000` |
| `utilities_class_prefix` | `u-` |
| `allow_http_fetch_for_inline_merge` | `false` |

## Public CSS route

Registered in `CmsPublicSystemRoutes`:

```
GET /{path_prefix}/{slug}.css
```

| Config | Default |
|--------|---------|
| `public_route.enabled` | `true` |
| `public_route.path_prefix` | `cms/stylesheets` |
| `public_route.max_age_seconds` | `3600` |
| `public_route.cache_bust_query` | `true` |
| `public_route.cache_bust_param` | `v` |

Bound to the public front domain; catch-all excludes this prefix automatically ([Public routing](./public-routing)).

Manual rebuild: `POST api/cms/style-sheets/{id}/recompile` when your API middleware allows it.

## Framework href maps

Config nests `bootstrap.*` and `tailwind.*` map versions → vendor/CDN/build CSS (and Bootstrap JS bundle) paths. Demo assets ship under `public/vendor/cms-stylesheet-examples/…` and `public/build/cms-stylesheet-examples/…`. Prefer local `/` paths; HTTP fetch for inlining is off by default.

## Blade snippet

```php
@foreach (\Modules\Cms\Support\StylesheetManager::linkHrefsForLayoutBuilder($layoutBuilder) as $href)
    <link rel="stylesheet" href="{{ $href }}">
@endforeach
```

## Config

Full table: [Configuration](./configuration) → `modularous.cms_stylesheets`.

**Next:** [Layout builders](./layout-builders) (attach sheets to a document shell).

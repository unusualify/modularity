---
sidebarPos: 6
sidebarTitle: Page layouts
sidebarGroupTitle: Cms
outline: deep
---

# Page layouts

A **PageLayout** is the locale-agnostic presentation binding for one routed model class (e.g. all `Blog` posts share one shell). URLs stay on [Parent segments & URL routes](./parent-segments-and-url-routes); HTML chrome comes from an optional [LayoutBuilder](./layout-builders) plus per-model appends.

Models opt in via `HasPageLayout` (on `IsCmr`). Resolve with `CmsPageLayoutResolver`.

## Fields

| Field | Meaning |
|-------|---------|
| `target_model_class` | FQCN of the CMR model (unique) |
| `layout_builder_id` | Optional LayoutBuilder for the document shell |
| `blade_source` | `db` or `filesystem` for **this binding’s appends** |
| `blade_segments` | DB appends: `head` / `body` / `footer` when `blade_source=db` |
| `blade_view_name` | **Optional** explicit view. Often **`NULL`** — then filesystem falls back by module/route (below) |

When `blade_source` is omitted on the row, resolution falls back to `cms_layout_builder.default_blade_source` (**`db`** by default).

## How it wraps a page

`CmsPageLayoutPresentationWrapper` on public render:

1. Inner content — prefer `module::route.custom` when present
2. PageLayout appends (DB segments **or** filesystem `page_layout` blades)
3. Wrap with LayoutBuilder shell (`LayoutBladeResolver`) + [stylesheet](./stylesheets) links

If there is **no** enabled PageLayout row, module filesystem `page_layout` segments can still merge when `cms_page_layouts.filesystem_segments_without_db_binding_enabled` is **true** (default).

## NULL `blade_view_name` → module/route filesystem fallback

This is the usual production pattern: the `page_layouts` row exists (target model + layout builder), but **`blade_view_name` is empty**. Markup lives in the module under `{route}/page_layout/`.

Context comes from the presentation view name (e.g. `blog::blog.custom` → module `blog`, route `blog`):

| Priority | View name | Disk path |
|----------|-----------|-----------|
| 1 | `vendor.modularous.modules.{module}.{route}.page_layout.{segment}` | `resources/views/vendor/modularous/modules/{module}/{route}/page_layout/{segment}.blade.php` |
| 2 | `modularous::modules.{module}.{route}.page_layout.{segment}` | package / published modularous views |
| 3 | **`{module}::{route}.page_layout.{segment}`** | **`modules/{Module}/Resources/views/{route}/page_layout/{segment}.blade.php`** |

`{segment}` = `head` | `body` | `footer`.

### Example: Blog post detail

DB (`page_layouts`):

| target_model_class | blade_view_name | admin_label |
|--------------------|-----------------|-------------|
| `Modules\Blog\Entities\Blog` | **`NULL`** | Blog Post Detail |

Filesystem fallback:

```
blog::blog.page_layout.body
→ modules/Blog/Resources/views/blog/page_layout/body.blade.php
```

Landing rows often set an **explicit** `blade_view_name` instead (e.g. `blog::blog_landing.page_layout.body`) — same convention, just stored on the row.

Convention: **snake module namespace + snake route folder** = submodule view root (`Blog` module / `Blog` route → `blog::blog.*`).

After editing views: `php artisan view:clear`.

## Default LayoutBuilder shell

When a PageLayout has no `layout_builder_id` (or static segments need a shell):

1. `cms_page_layouts.default_layout_builder_id` if set
2. Else LayoutBuilder with slug = `cms_layout_builder.default_layout_slug` (if non-empty)

## Informational fallback

If neither `module::route.custom` nor static page_layout body exists:

| Key | Default |
|-----|---------|
| `public_presentation_informational_fallback_enabled` | `true` |
| `public_presentation_informational_fallback_view` | `cms::page.page_layout.body` |

## Config (`modularous.cms_page_layouts`)

| Key | Default | Purpose |
|-----|---------|---------|
| `enabled` | `true` | Feature on/off |
| `filesystem_segments_without_db_binding_enabled` | `true` | Use `{module}::{route}.page_layout.*` without a DB row |
| `default_layout_builder_id` | `null` | Fallback LayoutBuilder id |
| `layout_appends_modal_preview_enabled` | `true` | Modal draft preview for appends |
| `public_presentation_informational_fallback_enabled` | `true` | Inner body fallback |
| `public_presentation_informational_fallback_view` | `cms::page.page_layout.body` | Fallback Blade |

See [Configuration](./configuration). Stylesheets and LayoutBuilder blades: [Stylesheets](./stylesheets), [Layout builders](./layout-builders).

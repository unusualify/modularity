---
sidebarPos: 9
sidebarTitle: Configuration
sidebarGroupTitle: Cms
outline: deep
---

# CMS configuration

Merged under `modularous.*` from `packages/modularous/config/merges/cms_*.php`. Host apps override via published config / `.env`.

## Feature switches

| Key | Default | Purpose |
|-----|---------|---------|
| `cms_features.enabled` | `true` | Master CMS on/off |
| `cms_features.register_contracts` | `true` | Bind CMS contracts |
| `cms_features.register_middlewares` | `true` | Register CMS middleware aliases |

## Routing & URLs

| Key | Purpose |
|-----|---------|
| `cms_routing.*` | Catch-all, universal front, localization, domain, signed preview, visitor redirects, UrlRoute cache/resync |
| `cms_routing.public_front_catch_all_exclude_path_prefixes` | Extra path prefixes the catch-all must ignore — see [Public routing](./public-routing) |
| `cms_parent_segments.*` | Enable ParentSegment URL prefix bindings |

## Presentation (defaults that matter)

Read in dependency order: [Stylesheets](./stylesheets) → [Layout builders](./layout-builders) → [Page layouts](./page-layouts).

### `cms_stylesheets`

| Key | Default | Notes |
|-----|---------|-------|
| `compiled_disk` | `local` | Disk for compiled CSS |
| `compiled_directory` | `modularous/cms/stylesheets` | Path on that disk |
| `utilities_class_prefix` | `u-` | Utility class namespace |
| `scssphp.enabled` | `false` | SCSS compile |
| `public_route.enabled` | `true` | Serve `/{prefix}/{slug}.css` |
| `public_route.path_prefix` | `cms/stylesheets` | Public URL prefix |
| `public_route.cache_bust_query` | `true` | `?v=<hash>` on bundle links |

Details: [Stylesheets](./stylesheets).

### `cms_layout_builder`

| Key | Default | Notes |
|-----|---------|-------|
| `default_blade_source` | **`db`** | Used when LayoutBuilder **or** PageLayout omits `blade_source` (`MODULAROUS_CMS_LAYOUT_BLADE_SOURCE`) |
| `max_blade_segments_bytes` | `512000` | DB segment size cap |
| `preview_enabled` | `true` | Admin layout preview |
| `middleware_enabled` | `false` | Opt-in `?cms_layout=` middleware |
| `middleware_query_slug_key` | `cms_layout` | Query key |
| `default_layout_slug` | `''` | Fallback LayoutBuilder slug for middleware / PageLayout default shell |

Filesystem blades: [Layout builders](./layout-builders) — slug theme pack `resources/views/cms/layout_builder/{slug}/` (`cms.layout_builder.{slug}.*`; `blade_view_name` often NULL). Optional master via `cms:layout-builder:publish-blades`.

### `cms_page_layouts`

| Key | Default | Notes |
|-----|---------|-------|
| `enabled` | `true` | Feature on/off |
| `filesystem_segments_without_db_binding_enabled` | **`true`** | Merge module `page_layout/*` blades without a DB PageLayout row |
| `default_layout_builder_id` | `null` | Else use `cms_layout_builder.default_layout_slug` |
| `layout_appends_modal_preview_enabled` | `true` | Appends modal preview |
| `public_presentation_informational_fallback_enabled` | `true` | Inner body fallback |
| `public_presentation_informational_fallback_view` | `cms::page.page_layout.body` | Fallback view |

Filesystem appends: [Page layouts](./page-layouts) — `{module}::{route}.page_layout.{segment}` → `modules/{Module}/Resources/views/{route}/page_layout/` (`blade_view_name` often NULL).

## SEO & sitemap

| Key | Purpose |
|-----|---------|
| `cms_sitemap.*` | `/sitemap.xml` + `/sitemap.xsl`, cache key, defaults, build-on-miss, panel step-up |
| `cms_seo.*` | Canonical helpers, robots.txt, staging noindex, admin soft warnings |

## Other

| Key | Purpose |
|-----|---------|
| `cms_settings.*` | CMS settings cache TTL / sensitive keys |
| `cms_schedule.*` | Publish-window boundary scan |
| `cms_promotion.*` | Staging→prod promotion workflow |

Toggle individual public endpoints via flags used by `CmsPublicSystemRoutes` (e.g. `cms_sitemap.route_enabled`, `cms_seo.robots.route_enabled`, `cms_routing.signed_preview.enabled`, `cms_stylesheets.public_route.enabled`).

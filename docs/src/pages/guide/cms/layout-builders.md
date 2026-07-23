---
sidebarPos: 5
sidebarTitle: Layout builders
sidebarGroupTitle: Cms
outline: deep
---

# Layout builders

A **LayoutBuilder** is a reusable HTML document shell (head / body / footer) — the selected “theme”. [Page layouts](./page-layouts) point at a builder; [stylesheets](./stylesheets) hang off the builder.

Rendered by `LayoutBladeResolver`.

## Blade source (exclusive)

| Mode | What is authoritative |
|------|------------------------|
| **`db`** (default) | `blade_segments` (`head`, `body`, `footer`) — `Blade::render()`, wrapped in `cms::layout_builder.shell` |
| **`filesystem`** | Theme blades on disk keyed by **`slug`** (and optional `blade_view_name`) |

Do **not** keep markup in both places. Saving clears the inactive side.

**Default when a row omits `blade_source`:** `cms_layout_builder.default_blade_source` = **`db`** (`MODULAROUS_CMS_LAYOUT_BLADE_SOURCE`).

## Filesystem mode — resolution order

When `blade_source=filesystem`:

1. **Slug theme pack** — if `slug` is set and any slug segment view exists (usual case; `blade_view_name` may be **`NULL`**)
2. Else explicit **`blade_view_name`** if that view exists
3. Else PageLayout/module `page_layout` segments inside the package shell

### NULL `blade_view_name` → slug theme fallback (recommended)

Production themes often look like this in `layout_builders`:

| name | slug | blade_source | blade_view_name |
|------|------|--------------|-----------------|
| b2press | `b2press` | `filesystem` | **`NULL`** |
| Bootstrap | `bootstrap-5-3-8` | `db` | `NULL` (uses `blade_segments`) |

For filesystem + slug, `LayoutBladeResolver` loads:

```
cms.layout_builder.{slug}.{segment}
cms::layout_builder.{slug}.{segment}
vendor.modularous.layout_builder.{slug}.{segment}
```

First existing view wins. Host apps typically use the **`cms.layout_builder.*`** namespace (no `::`):

```
resources/views/cms/layout_builder/{slug}/head.blade.php
resources/views/cms/layout_builder/{slug}/body.blade.php
resources/views/cms/layout_builder/{slug}/footer.blade.php
resources/views/cms/layout_builder/{slug}/shell.blade.php   # optional
```

**Example — theme `b2press`:**

```
resources/views/cms/layout_builder/b2press/head.blade.php
resources/views/cms/layout_builder/b2press/body.blade.php
resources/views/cms/layout_builder/b2press/footer.blade.php
```

↔ views `cms.layout_builder.b2press.head` / `.body` / `.footer`.

Same pattern with the Cms module namespace: `cms::layout_builder.b2press.head` → `modules/Cms/Resources/views/layout_builder/b2press/head.blade.php` (if present).

Vendor publish path alternative:

```
resources/views/vendor/modularous/layout_builder/{slug}/{segment}.blade.php
```

If no slug `shell` exists, segments are composed with package `cms::layout_builder.shell`.

After edits: `php artisan view:clear`.

### Optional: single master via `blade_view_name`

When you do set an explicit view (instead of a slug pack):

```bash
php artisan cms:layout-builder:publish-blades
```

| Item | Value |
|------|--------|
| File | `resources/views/vendor/modularous/cms/layout-builder/master.blade.php` |
| Suggested `blade_view_name` | `vendor.modularous.cms.layout-builder.master` |

Slug packs take precedence over `blade_view_name` when any slug override file exists.

## Stylesheets on a builder

- `style_sheet_id` — primary published [StyleSheet](./stylesheets)
- `style_sheet_slugs` — extra published slugs after the FK (duplicates skipped)

`StylesheetManager::linkHrefsForLayoutBuilder()` emits framework + compiled bundle `<link>` hrefs in that order.

## Nested body compose

Optional: `definition.cms_page_layout_body_compose = wrap` — PageLayout body is compiled first and passed into the LayoutBuilder body as `$cmsPageLayoutBodyHtml`. Default is append.

## Admin preview & middleware

- Preview when `cms_layout_builder.preview_enabled` (default **true**)
- Front middleware `modules.cms.layout_builder` is **off** by default; query key default `cms_layout`
- `cms_layout_builder.default_layout_slug` — optional default theme slug

## Config (`modularous.cms_layout_builder`)

| Key | Default | Purpose |
|-----|---------|---------|
| `default_blade_source` | `db` | Used when row omits `blade_source` |
| `max_blade_segments_bytes` | `512000` | Cap for DB segments (UTF-8) |
| `preview_enabled` | `true` | Admin HTML preview |
| `middleware_enabled` | `false` | Opt-in query-param layout middleware |
| `middleware_query_slug_key` | `cms_layout` | Query key |
| `default_layout_slug` | `''` | Fallback slug; also used by PageLayout default shell |

See also [Configuration](./configuration).

**Next:** [Page layouts](./page-layouts) — module/route `page_layout` blades with `NULL` `blade_view_name`.

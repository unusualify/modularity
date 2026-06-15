# CMS Stylesheet subsystem and LayoutBuilder contract

This document describes how **style sheets** relate to **layout builders** rendered as Blade/HTML.

## Data model (`layout_builders`)

| Column | Meaning |
|--------|---------|
| `style_sheet_id` | Nullable FK — **primary** sheet; links are emitted first via `StylesheetManager`. |
| `style_sheet_slugs` | Optional JSON array of additional **published** `style_sheets.slug` values merged **after** the FK in order. Duplicate sheets (same id as FK) are skipped; duplicate URL hrefs dedupe while keeping the first occurrence. |
| `slug` | Public identifier for resolving a layout row (middleware + preview). Unique. |
| `blade_source` | `db` or `filesystem` — **exclusive** storage; do not keep authoritative markup in both places for the same row. |
| `blade_segments` | JSON/object with `head`, `body`, `footer` — used only when `blade_source=db`, compiled with `Blade::render()` into the package shell. |
| `blade_view_name` | Laravel view name for `blade_source=filesystem` (e.g. `vendor.modularous.cms.layout-builder.master`). |
| `definition` | Optional manifest (slot names, version, tooling hints). |

### Why DB vs filesystem is exclusive

Keeping the same layout as both editable DB segments **and** a physical Blade file drifts immediately. Saving enforces mutually exclusive payloads: filesystem rows clear persisted segments in the repository; DB rows clear `blade_view_name` when `blade_source` is toggled.

Global default when a row omits storage mode: **`modularous.cms_layout_builder.default_blade_source`** (see `config/merges/cms_layout_builder.php`).

### Published stylesheets

Only **published** `StyleSheet` models contribute a compiled bundle URL. References in `style_sheet_slugs` that are missing or unpublished are skipped with no merged href.

## Stylesheet ordering

For one sheet see `Modules\Cms\Support\StylesheetManager::linkHrefsForSheet()`:

1. Ordered `frameworkLinkHrefs`
2. Public compiled bundle URL (`cms.public.stylesheet`), when routing is enabled and the sheet is published

For a layout builder, `Modules\Cms\Support\StylesheetManager::linkHrefsForLayoutBuilder()` merges `linkHrefsForSheet()` in order for:

1. The FK (`style_sheet_id`), if present
2. Each extra slug in `style_sheet_slugs` (published sheets only)

## Rendering

### Middleware (front routing)

Class: `Modules\Cms\Http\Middleware\LayoutBuilderMiddleware` — Laravel alias **`modules.cms.layout_builder`**.

- **Disabled internally by default.** Set **`modularous.cms_layout_builder.middleware_enabled`** to `true`, then attach the middleware to the route groups where you emit HTML.
- **Query key** (default **`cms_layout`**): resolves a **published** `layout_builders.slug` and shares Blade variables **`$cmsLayoutBuilder`** and **`$cmsLayoutStylesheetHrefs`**.

### Resolver

Class: `Modules\Cms\Support\LayoutBladeResolver` — emits full HTML documents.

| Mode | Behaviour |
|------|-----------|
| `db` | Renders segments with Laravel `Blade::render()`, wraps them in **`cms::layout_builder.shell`** (stylesheet loop + placeholders). |
| `filesystem` | Returns `view(blade_view_name, $data)`; **`previewBodyHtml`** is passed for panel preview markup. |

### Filesystem slug overrides

Filesystem-mode `LayoutBuilder` rows that define a `slug` may host vendor overrides at:

```
resources/views/vendor/modularous/layout_builder/{slug}/shell.blade.php
resources/views/vendor/modularous/layout_builder/{slug}/layout.blade.php
resources/views/vendor/modularous/layout_builder/{slug}/head.blade.php
resources/views/vendor/modularous/layout_builder/{slug}/body.blade.php
resources/views/vendor/modularous/layout_builder/{slug}/footer.blade.php
```

Each file is referenced as `vendor.modularous.layout_builder.{slug}.{segment}` and receives the same resolver data (`cmsLayout`, `stylesheetHrefs`, `stylesheetScriptSrcs`, `previewBodyHtml`, etc.). Slug overrides render `head`, `body`, and `footer` segments first, optionally compose them through the `layout` view, and finally wrap the result in the `shell` view before any head/body/footer appends are injected. If no slug-specific views exist the resolver falls back to the configured `blade_view_name` (e.g. `vendor.modularous.cms.layout-builder.master`) and the packaged inline-document helper.

After editing Blade files under **`resources/views`**, run **`php artisan view:clear`** so overrides are picked up.

### Admin preview route

Authenticated panel (**web** stack): **`GET …/layout-builders/{layout_builder}/preview-html`**. With the stock Modularous admin prefix this is commonly registered as **`admin.system.cms.layout_builder.preview_html`** (inspect `php artisan route:list | grep preview-html`).

Toggle with **`modularous.cms_layout_builder.preview_enabled`**. Open in a new tab or iframe; stylesheet link order matches `StylesheetManager`.

API payloads may include **`preview_html_url`** when preview is enabled and the route exists.

### Publishing filesystem stubs

```bash
php artisan cms:layout-builder:publish-blades
```

Copies **`master.blade.php`** to **`resources/views/vendor/modularous/cms/layout-builder/`**. Typical view name: **`vendor.modularous.cms.layout-builder.master`**  
Use **`--force`** for non-interactive overwrites.

### Admin form inputs

- **`layout-blades`** hydrates as **`input-layout-blades`** (`VInputLayoutBlades`): monospace tabs for **`head` / `body` / `footer`**.
- **`json-field`** is used for **`definition`** and **`style_sheet_slugs`**.

Filesystem mode editors may remain visible depending on tooling; authoritative markup still lives only in Blade — the repository clears DB segments once `blade_source` is saved as `filesystem`.

## Blade usage snippets

Single sheet:

```php
@foreach (\Modules\Cms\Support\StylesheetManager::linkHrefsForSheet($styleSheetModel) as $href)
    <link rel="stylesheet" href="{{ $href }}">
@endforeach
```

Layout builder row:

```php
@foreach (\Modules\Cms\Support\StylesheetManager::linkHrefsForLayoutBuilder($layoutBuilderModel) as $href)
    <link rel="stylesheet" href="{{ $href }}">
@endforeach
```

## Public route registry (Parent segment bindings)

Table **`cms_parent_segment_bindings`** (entity `ParentSegment`) maps each **IsCmr / module-route model + locale** to:

- **`normalized_prefix`** — public URL segment (see `CmsParentSegmentResolver`).

URLs stay here; **presentation shells** are intentionally separate (locale-agnostic, one row per model class).

## Presentation shells (`PageLayout`)

Table **`cms_page_layouts`** (`PageLayout`) stores **one shell per routed model FQCN** (no locale column):

- **`layout_builder_id`** (optional)
- **`blade_segments`** (head/body/footer fragments persisted when `blade_source=db`, used when the binding executes its own DB shell)

Models opt in via **`HasPageLayout`** (bundled on **`IsCmr`** / **`CmrTrait`**). Resolve in PHP:

```php
$resolver = app(\Modules\Cms\Services\CmsPageLayoutResolver::class);
$shell = $resolver->layoutBuilderShellForModelClass(\Modules\Cms\Entities\Page::class);
$html = \Modules\Cms\Support\LayoutBladeResolver::renderHtml($shell, $mergeViewData);
```

Panel CRUD headline: **Presentation shells**; full-screen composer routes live under `modules/Cms/Routes/web.php`.

## Config merges

| Key | Purpose |
|-----|---------|
| `modularous.cms_layout_builder` | Blade source default, preview toggles, middleware toggles/query key, segment byte caps. |
| `modularous.cms_page_layouts` | Enable table + composer (`layout_composer_enabled`). |
| `modularous.cms_stylesheets` | Compiled bundle prefixes, compilers, placeholders. |

## Admin API / compilers

Saving via `Modules\Cms\Repositories\StyleSheetRepository` persists compiled CSS. Manual rebuild: **`POST api/cms/style-sheets/{id}/recompile`** when your API middleware allows it (`modules/Cms/Routes/api.php`).

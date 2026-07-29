# ErrorPage module

HTTP error presentation (403 / 404 / 500) — CMS records optional; **builtin Blade views are fallbacks**.

## Activation

1. Enable the module (`modules_statuses.json` → `"ErrorPage": true`).
2. Feature flag (default on): `MODULAROUS_CMS_ERROR_PAGES_ENABLED` / `modularous.cms_features.error_pages_enabled`.
3. Migrate (optional for builtin-only): `php artisan modularous:migrate ErrorPage`.
4. Seed CMS rows + PageLayout (optional, for LayoutBuilder shell):

```bash
php artisan modularous:create:error-page-defaults
# optional: --layout-slug=your-theme --force
```

Creates published `ErrorPage` rows for `404`, `403`, `500` and a `PageLayout` binding (`target_model_class = ErrorPage`) pointed at a LayoutBuilder.

LayoutBuilder resolution for the seed command (first match wins):

1. `--layout-slug=` option
2. `modularous.cms_layout_builder.default_layout_slug` (`MODULAROUS_CMS_LAYOUT_DEFAULT_SLUG`)
3. `CmsPageLayoutResolver::defaultLayoutBuilder()` (also honors `cms_page_layouts.default_layout_builder_id`)

If none resolve, PageLayout is still created/updated without `layout_builder_id` (standalone until a builder is linked). The package does **not** hardcode a host theme slug.

## Feature flags

| Config / env | Default | Effect when off |
|--------------|---------|-----------------|
| `cms_features.error_pages_enabled` / `MODULAROUS_CMS_ERROR_PAGES_ENABLED` | `true` | Renderer returns `null` → Laravel default |
| `cms_features.error_pages_cache_enabled` / `MODULAROUS_CMS_ERROR_PAGES_CACHE_ENABLED` | `true` | Skip model-scoped presentation HTML cache |

Also requires `modularous.cache.enabled`, `presentationItem.store !== none`, and `modularous.cache.modules.ErrorPage.routes.ErrorPage.types.presentationItem` (package default `true`).

## Presentation cache (model-scoped)

Published CMS rows cache full HTML via `ErrorPagePresentationCache` → `StaleFileCache` keyed by **model id + locale + error_code** (not visitor URL). Builtin (no-row) responses are not cached.

Invalidate on `ErrorPage` **saved** / **deleted** (entity `booted` callback). Generic `CacheObserver` warm is skipped (`shouldCacheInvalidate(): false`) because ErrorPage has no UrlRoute.

Admin **purge** clears model-scoped StaleFileCache (store-agnostic). Admin **warm** (`/error-pages/cache/warm/{id}`) rebuilds via `ErrorPagePresentationCache::warm()` — `WarmupCache::warmupPresentationItem` detects `ErrorPage` and delegates instead of the UrlRoute / front-view warm path.

## Visibility / strategy

| Condition | Result |
|-----------|--------|
| Feature flag off | Laravel default |
| Row exists, **unpublished** | Laravel default (CMS intentionally off for that code) |
| Row exists, **published** | CMS path: host override in LayoutBuilder when both exist; else package builtin full document |
| **No row** (or table missing) | Builtin full document (or app body override in plain document) |

Unpublished ≠ missing: unpublished opts out of ErrorPage handling for that code; missing still gets the package builtin (and app body override if present).

## Traits

| Entity | Repository |
|--------|------------|
| `HasPageLayout` + `Publishable` + `HasScopes` | `PageLayoutTrait` + `PublishableTrait` |

Do **not** use `IsCmr` / `HasParentSegment` — error pages are not front URL routes.

## Body resolution (highest wins)

Given status `404` and a resolved LayoutBuilder slug (from PageLayout / `default_layout_slug` / default builder — empty if unset):

1. **App theme body override** — `resources/views/cms/layout_builder/{slug}/404.blade.php`  
   View name: `cms.layout_builder.{slug}.404`  
   **Body fragment only** (host theme CSS; no ModularousVite).
2. **Package Cms fallback** — `cms::layout_builder.{slug}.404`

Package builtins (`error_page::error_page.{code}`) are **full documents** (`@extends` standalone) — they are never injected as LayoutBuilder body fragments.

App overrides apply for both published CMS and no-record builtin paths (so hosts can brand 404 without seeding).

## Shell & Vite / Vuetify

| Path | Document | Assets |
|------|----------|--------|
| **Published + LayoutBuilder + host/package override** | LayoutBuilder shell wraps body fragment | Host / layout assets only — **no** ModularousVite in the body |
| **Published, no usable override** | `error_page::error_page.{code}` (`@extends` standalone) | ModularousVite (`core-free.js`) + Vuetify UI |
| **No CMS row + theme override** | `error_page::error_page.plain` | Simple HTML document (no Vite) |
| **No CMS row, no override** | `error_page::error_page.{code}` (`@extends` standalone) | ModularousVite (`core-free.js`) + Vuetify UI |

Layout: `error_page::error_page.standalone` (`@yield('content')` + ModularousVite). Content pages: `404` / `403` / `500` use `@extends` + `@section('content')` with Vuetify markup and `error_page::messages.*` translations.

## Runtime

`ErrorPageServiceProvider` registers a `renderable` callback on the exception handler for `HttpExceptionInterface` status codes `403`, `404`, and `500`. Implementation: `Modules\ErrorPage\Support\ErrorPageRenderer`.

## Panel

Route config: `error-pages` with Selectable `error_code` (`403` / `404` / `500`, unique) and `published` switch.

## Host app overrides

Place a body fragment at:

`resources/views/cms/layout_builder/{your-layout-slug}/404.blade.php`

Configure `MODULAROUS_CMS_LAYOUT_DEFAULT_SLUG` (or PageLayout `layout_builder_id`) so the renderer finds that slug. Without seeded rows, unknown URLs still render that override via the plain document (or the Vuetify builtin via standalone). After seeding + PageLayout + override, the same body sits inside the LayoutBuilder shell.

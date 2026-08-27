---
sidebarPos: 9
sidebarTitle: Sitemap & SEO
sidebarGroupTitle: Cms
outline: deep
---

# Sitemap & SEO

## Sitemap

**Build** (`CmsSitemapBuildService`): all `UrlRoute` rows with `kind = page_public`, gated + published/visible models, absolute locs via public site URL helpers. Optional `CmsSitemapableItem` morph overrides `changefreq` / `priority`. Hreflang alternates group by urlable.

**Cache** (`CmsSitemapCacheService`): committed XML in `Cache::forever` (`cms_sitemap.cache_key`). Public requests only **read** the cache.

**Public route**: `GET /sitemap.xml` → `PublicSitemapController` (via [Public routing](./public-routing) catalog). Rebuild with `php artisan cms:sitemap:rebuild` or the panel commit tool / `RebuildCmsSitemapJob`.

Browser view: committed XML includes `<?xml-stylesheet href="/sitemap.xsl"?>`; `GET /sitemap.xsl` → `PublicSitemapXslController` (human-readable table). Crawlers ignore the stylesheet.

Panel dry-run returns XSLT HTML when PHP `ext-xsl` is available (`CmsSitemapXsltService`), plus the raw stylesheet for a browser-side fallback when the extension is missing.

`build_on_cache_miss` (default false): optional sync build on first miss; otherwise empty valid `<urlset>` until commit.

## Robots.txt

`GET /robots.txt` → `RobotsTxtController` when `cms_seo.robots.route_enabled`. Body from System Settings (when enabled) or `cms_seo.robots.global_robots_txt`. Staging noindex can force disallow-all (`cms_seo`).

## llms.txt

`GET /llms.txt` → `LlmsTxtController` when `cms_seo.llms.route_enabled` (package default **false**; host apps opt in). Markdown body from System Settings `seo.llms_txt` (when enabled) or `cms_seo.llms.global_llms_txt`. Staging noindex serves a stub instead of production content. Spec: [llmstxt.org](https://llmstxt.org/). The admin field is a [`source-text`](/guide/form-inputs/input-source-text) input (`format: md`).

## Config

`modularous.cms_sitemap`, `modularous.cms_seo`. See [Configuration](./configuration).

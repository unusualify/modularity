# Cache Services — Agent Rules

Package-internal cache layer. Do not explain module scaffolding to end users here.

## Two layers

| Layer | Storage | Types / concern |
|-------|---------|-----------------|
| Admin module-route cache | Redis (default) | `counts`, `index`, `record`, `formItem`, `formattedItem` |
| Public presentation | `url` / `model` / `none` store | `presentationItem` HTML |

Never assume public HTML lives in Redis.

## Hard rules

1. Middleware and controllers resolve the URL store via `ModularousCache::getUrlPresentationCacheStore()` — do **not** bind callers to `UrlKeyedStaleCache` directly.
2. Warm jobs must use public site URL (`CmsPublicSiteUrl`) and run each locale inside `CmsPublicPresentationWarmupContext` — never warm from admin hostname/session locale.
3. On purge, unpublish, or path change, clear **all query variants** for affected locale + path (`forgetByRelation`, `forgetByModuleRoute`, `forgetPathVariants`).
4. New URL driver: implement `Contracts/Cache/UrlPresentationCacheStoreInterface`, register in `ModularousCacheService::resolveUrlPresentationCacheStore()`, preserve key helpers and variant purge semantics.
5. Config/env details live in docs — do not duplicate long env tables in AGENTS.

## Key classes

| Piece | Path |
|-------|------|
| Facade / service | `ModularousCacheService` |
| URL store interface | `Contracts/Cache/UrlPresentationCacheStoreInterface` |
| Default driver | `FileUrlPresentationCacheDriver` |
| Stale file (model store) | `StaleFileCache` |
| Jobs | `src/Jobs/Cache/*` |

## Read before edit

- `docs/src/pages/guide/module-route-cache/index.md`
- `docs/src/pages/guide/module-route-cache/url-stale-resilience.md`
- `docs/src/pages/guide/module-route-cache/cms-public-pages.md`
- `docs/src/pages/guide/module-route-cache/swr.md`
- `docs/src/pages/guide/console/cache/` (warm/purge-presentation)
- CMS side: `modules/Cms/AGENTS.md`

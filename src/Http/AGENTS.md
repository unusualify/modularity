# Http Instructions

Controllers, middleware, requests, responses under `src/Http/`.

Format for prompts: Action | Path | Constraints | Tests

## Controller hierarchy

```
Controller
└── CoreController
    ├── PanelController → BaseController   # admin CRUD
    ├── ApiController                      # REST
    └── FrontController                    # public-facing base
```

Keep controllers thin: DI + form requests; domain work in repositories/services. Traits under `Controllers/Traits/` for form/table/cache/scopes.

## Hard rules

1. No business logic in controllers; use repositories/services.
2. Admin cache purge/warm UI: `ManageResourceCache` + repo `ResourceCacheActionsTrait` + `CACHING` permission.
3. Public presentation (`serve_first`): `ServeUrlKeyedStaleMiddleware` may short-circuit before route match; controller path still uses `ModularousCache::getUrlPresentationCacheStore()`.
4. Register middleware aliases via Module / provider conventions — do not hard-code paths.
5. New middleware: PSR-12, typed, tested under `tests/Http/`.

## Examples

- Add controller | `src/Http/Controllers/ReportController.php` | DI, form requests, Inertia or JSON | `tests/Http/ReportControllerTest.php`
- Add middleware | `src/Http/Middleware/EnsureModuleEnabled.php` | register alias appropriately | `tests/Http/Middleware/...`
- Create request | `src/Http/Requests/StoreReportRequest.php` | `validated()`, typed rules | `tests/Http/Requests/...`

## Read before edit

- `docs/src/pages/system-reference/backend/http/controllers/overview.md`
- `docs/src/pages/guide/module-route-cache/admin-cache-actions.md`
- Presentation: `src/Services/Cache/AGENTS.md`, `modules/Cms/AGENTS.md`

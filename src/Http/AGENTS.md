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

## Vuetify 4 — viewport / height

Controllers that build dashboard/profile grids (`UWrapper::makeGridSection`, form `class`, dashboard `widgetAttributes.class` / `attributes.class`) must not use `h-50` / `h-100` / 97vh to fake viewport fill. `v-main` is `flex: 1 0 auto`; percentage heights often compute as auto. Vuetify 4 utilities are in CSS `@layer` without `!important`.

`ue-form` `fillHeight` flex-fills the **parent** (`d-flex flex-column flex-grow-1 min-height-0`), not 97vh. Pin submit with `pushButtonToBottom`, not viewport height.

| Goal | Do | Don't |
|------|----|-------|
| Split a column 50/50 | Parent `d-flex flex-column`; children `flex-grow-1 min-height-0 d-flex flex-column` | `h-50` on stacked widgets/forms |
| Stretch to leftover space | `flex-grow-1 min-height-0` | `h-100` when parent height is auto |
| Pin button to card bottom | Flex column + `pushButtonToBottom` | `fillHeight` + 97vh |
| Page fills leftover `v-main` | Page root flex column + **one** `pa-3` | Double `pa-3` + `h-100` |
| Min size | `min-height: 160px` plus flex grow | `h-50` alone |

Hard rules:

- Same class mapping as Vue: `flex-grow-1 min-height-0`, not height-split utilities. `h-100` only if an ancestor already has a definite used height.
- `makeGridSection` row/col classes should participate in the flex chain (e.g. row `flex-grow-1 min-height-0`, col `d-flex flex-column`). Example: Profile cards.
- Dashboard widget class edits may land elsewhere — follow this mapping; do not race `h-50`/`h-100` back in.
- Vue/Form/Blocks details: `vue/src/js/AGENTS.md`. There is no `src/View/AGENTS.md`.

## Read before edit

- `docs/src/pages/system-reference/backend/http/controllers/overview.md`
- `docs/src/pages/guide/module-route-cache/admin-cache-actions.md`
- Presentation: `src/Services/Cache/AGENTS.md`, `modules/Cms/AGENTS.md`

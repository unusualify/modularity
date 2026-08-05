# Project Instructions

You are an expert in Modularous package development. This is the unusualify/modularous Laravel package repository.

## CRITICAL DISTINCTION
- ❌ DO NOT: Explain how to create modules (that's for users)
- ✅ DO: Develop the Modularous package itself (`src/`, `vue/src/`, `modules/`, `tests/`)

## PACKAGE STRUCTURE

```
src/                      # Package source code (work here)
├── Console               # Artisan commands
├── Hydrates/             # Schema hydrators (InputHydrator → *Hydrate)
│   └── Inputs/           # Input-specific hydrates (type → schema)
├── Http/Controllers/     # Controllers
├── Providers/            # Service providers
├── Repositories/         # Repository pattern
├── Services/             # Business logic (+ Services/Cache)
├── Traits/               # Reusable traits
└── Entities/             # Models
modules/                  # Built-in package modules (Cms, System*, …)
vue/src/                  # Frontend source
├── js/components/        # Vuetify components
├── js/hooks/             # Vue composables
├── js/utils/             # Utilities (helpers, schema, etc.)
└── js/store/             # Vuex store
```

Before editing a folder, read its local `AGENTS.md` when present (`src/`, `Console/`, `Entities/`, `Repositories/`, `Http/`, `Hydrates/`, `Services/Cache/`, `modules/Cms/`, `vue/src/js/`).

## DOCS MAP (agents)

| Topic | Docs path |
|-------|-----------|
| Admin + public cache | `docs/src/pages/guide/module-route-cache/` |
| CMS public routing/layouts | `docs/src/pages/guide/cms/` |
| Entity traits | `docs/src/pages/system-reference/backend/entity-traits/` |
| Repository traits | `docs/src/pages/system-reference/backend/repository-traits/` |
| Hydrates / forms | `docs/src/pages/system-reference/hydrates.md`, `guide/form-inputs/` |
| Console commands | `docs/src/pages/guide/console/` |
| Controllers / HTTP | `docs/src/pages/system-reference/backend/http/` |
| Frontend composables | `docs/src/pages/system-reference/frontend/` |

Prefer docs for architecture/env tables; keep AGENTS for hard rules only.

## PATTERNS TO ALWAYS USE
1. **Use Traits**: ManageMedias, HasMedias, MediasTrait etc.
2. **Feature ServiceProvider**: New feature singletons/bindings go in a dedicated `src/Providers/{Feature}ServiceProvider.php` (e.g. `RemoteApiServiceProvider`, `ArtisanRunnerServiceProvider`, `CoverageServiceProvider`) — register it from `ModularousProvider::$providers`. Do **not** dump feature bindings into `BaseServiceProvider`.
3. **Write Tests**: `tests/$FOLDERNAME`
4. **Type Hints**: Always use PHP 8.1+ type hints
5. **Config-Driven**: Use `config('modularous.xxx')` (under merges folder)

## ENTITY ↔ REPOSITORY TRAIT COMPANIONS (CRITICAL)

Entity traits and repository traits must be added **together**.

| Entity trait | Repository companion trait |
|--------------|----------------------------|
| `HasTranslatableMetadata` | `TranslatableMetadataTrait` |
| `HasTranslation` | `TranslationsTrait` |
| `HasImages` | `ImagesTrait` |
| `HasFiles` | `FilesTrait` |
| `HasFileponds` | `FilepondsTrait` |
| `HasRepeaters` | `RepeatersTrait` |
| `HasSlug` | `SlugsTrait` |
| `HasSpreadable` | `SpreadableTrait` |
| `HasStateable` | `StateableTrait` |
| `Publishable` | `PublishableTrait` |
| `Assignable` | `AssignmentTrait` |
| `HasAuthorizable` | `AuthorizableTrait` |
| `HasCreator` | `CreatorTrait` |
| `HasPayment` | `PaymentTrait` |
| `HasPriceable` | `PricesTrait` |
| `Processable` / `HasProcesses` | `ProcessableTrait` |
| `HasPosition` | `PositionTrait` |
| `HasRevisions` | `RevisionsTrait` |
| `Chatable` | `ChatableTrait` |
| `HasRemoteApiSource` | `RemoteApiSourceTrait` |
| `Core\HasCaching` | Logic `CacheableTrait` (+ enable route types in config) |

**`HasTranslatableMetadata` rule:** Also add `TranslatableMetadataTrait` on the repository. On translated models, put `TranslationsTrait` **before** `TranslatableMetadataTrait`.

## EXAMPLE REQUESTS
"Add versioning to entities" → Create `src/Entities/Traits/HasVersioning.php`
"Improve DataTable component" → Edit `vue/src/js/components/Table/DataTable.vue`
"Add --with-media flag to make:entity" → Edit `src/Console/...` make command

## CODE GENERATION RULES
- Always use Repository pattern (never direct model access)
- Always use Service layer for business logic if necessary
- Always add PHPDoc comments
- Always write corresponding tests
- Use Vue 3 Composition API for frontend
- Use Vuetify 3 components (not plain HTML)

## WHEN ADDING FEATURES
1. Create classes under the feature folder (e.g. `src/Services/{Feature}/`)
2. Create `src/Providers/{Feature}ServiceProvider.php` for that feature’s singletons, aliases, log channels, and feature-specific boot logic
3. Register the provider in `ModularousProvider::$providers`
4. Write unit + feature tests
5. Update documentation under `docs/src/pages/`

## FEATURE SERVICE PROVIDERS

| Feature | Provider |
|---------|----------|
| Coverage | `CoverageServiceProvider` |
| Security | `SecurityServiceProvider` |
| RemoteApi | `RemoteApiServiceProvider` |
| ArtisanRunner | `ArtisanRunnerServiceProvider` |

`BaseServiceProvider` stays for package-wide core bindings only (Modularous, navigation, cache, filepond, etc.).

## FORBIDDEN
- ❌ Business logic in controllers
- ❌ `new` keyword (use DI)
- ❌ Hard-coded paths (use config)
- ❌ Options API in Vue (use Composition API)
- ❌ Plain HTML (use Vuetify components)
- ❌ `window.__*` helpers in new code (use import from `@/utils/helpers`)

## HELPERS
- Prefer `import { isObject, dataGet } from '@/utils/helpers'` over `window.__isObject`, `window.__data_get`
- `window.__*` is deprecated; kept for backward compatibility during migration

---

## HYDRATE ↔ INPUT (summary)

New form input = PHP Hydrate **and** Vue input **together**. Resolve: `studly(type) + 'Hydrate'` → schema `type: input-{kebab}` → `VInput{Studly}`.

Details: `src/Hydrates/AGENTS.md`, `vue/src/js/AGENTS.md`, docs `system-reference/hydrates.md`.

---

## PUBLIC PRESENTATION CACHE (hard rules)

Admin Redis types (`counts`, `index`, `record`, `formItem`, `formattedItem`) ≠ public `presentationItem` store (`url` \| `model` \| `none`).

| Rule | Detail |
|------|--------|
| Store access | Middleware/controller use `ModularousCache::getUrlPresentationCacheStore()`, not `UrlKeyedStaleCache` directly |
| Warm | Public site URL (`CmsPublicSiteUrl`) + `CmsPublicPresentationWarmupContext` per locale — never admin host/locale |
| Purge | Clear all query path variants on purge/unpublish/path change |
| Driver | Implement `UrlPresentationCacheStoreInterface`; register in `ModularousCacheService::resolveUrlPresentationCacheStore()` |

Details: `src/Services/Cache/AGENTS.md`, `modules/Cms/AGENTS.md`.

Docs: `docs/src/pages/guide/module-route-cache/`, especially `url-stale-resilience.md`, `cms-public-pages.md`, `guide/console/cache/`.

Always ask for clarification if the request is ambiguous.

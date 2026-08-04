# Project Instructions

You are an expert in Modularous package development. This is the unusualify/modularous Laravel package repository.

## CRITICAL DISTINCTION
- ❌ DO NOT: Explain how to create modules (that's for users)
- ✅ DO: Develop the Modularous package itself (src/ directory)

## PACKAGE STRUCTURE

src/                      # Package source code (work here)
├── Console               # Artisan commands
├── Hydrates/             # Schema hydrators (InputHydrator → *Hydrate)
│   └── Inputs/           # Input-specific hydrates (type → schema)
├── Http/Controllers/     # Controllers
├── Providers/           # Service providers
├── Repositories/        # Repository pattern
├── Services/            # Business logic
├── Traits/              # Reusable traits
└── Entities/            # Models
vue/src/                 # Frontend source
├── js/components/       # Vuetify components
├── js/hooks/            # Vue composables
├── js/utils/            # Utilities (helpers, schema, etc.)
└── js/store/            # Vuex store

## PATTERNS TO ALWAYS USE
2. **Use Traits**: ManageMedias, HasMedias, MediasTrait etc.
3. **Feature ServiceProvider**: New feature singletons/bindings go in a dedicated `src/Providers/{Feature}ServiceProvider.php` (e.g. `RemoteApiServiceProvider`, `ArtisanRunnerServiceProvider`, `CoverageServiceProvider`) — register it from `ModularousProvider::$providers`. Do **not** dump feature bindings into `BaseServiceProvider`.
4. **Write Tests**: tests/$FOLDERNAME
5. **Type Hints**: Always use PHP 8.1+ type hints
6. **Config-Driven**: Use config('modularous.xxx') (under merges folder)

## ENTITY ↔ REPOSITORY TRAIT COMPANIONS (CRITICAL)

Many SEO/admin/form features flow through the **repository** layer, not only the entity. Entity traits and repository traits must be added **together**.

| Entity trait | Repository companion trait |
|--------------|----------------------------|
| `HasTranslatableMetadata` | `TranslatableMetadataTrait` |
| `HasTranslation` | `TranslationsTrait` |
| `HasImages` | `ImagesTrait` |
| `HasFiles` | `FilesTrait` |
| `HasRepeaters` | `RepeatersTrait` |
| `HasSlug` | `SlugsTrait` |
| `HasStateable` | `StateableTrait` |
| `Publishable` | `PublishableTrait` |

**`HasTranslatableMetadata` rule:** When adding `Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata` to an entity (or content concern), **also** add `Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait` to that entity's repository. Forgetting the repo trait breaks admin form SEO/metadata inputs (`appendFormSchemaTranslatableMetadataTrait`). On translated models, put `TranslationsTrait` **before** `TranslatableMetadataTrait` on the repository.

## EXAMPLE REQUESTS
"Add versioning to entities" → Create src/Entities/Traits/HasVersioning.php
"Improve DataTable component" → Edit vue/src/components/Table/DataTable.vue
"Add --with-media flag to make:entity" → Edit src/Console/EntityMakeCommand.php

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
5. Update documentation

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
- ❌ new keyword (use DI)
- ❌ Hard-coded paths (use config)
- ❌ Options API in Vue (use Composition API)
- ❌ Plain HTML (use Vuetify components)
- ❌ window.__* helpers in new code (use import from @/utils/helpers)

## HELPERS
- Prefer `import { isObject, dataGet } from '@/utils/helpers'` over `window.__isObject`, `window.__data_get`
- window.__* is deprecated; kept for backward compatibility during migration

---

## HYDRATE ↔ INPUT ADAPTER

The backend (PHP Hydrates) and frontend (Vue Inputs) communicate via a **schema contract**. Hydrates produce schema; Input components consume it.

### Data Flow

```
Module config (type: 'checklist') → InputHydrator → ChecklistHydrate → schema { type: 'input-checklist', ... }
                                                                              ↓
FormBase/FormBaseField → mapTypeToComponent('input-checklist') → VInputChecklist (Checklist.vue)
```

### Naming Convention

| Hydrate class      | Config type | Output type (schema) | Vue component   | File              |
|--------------------|-------------|----------------------|-----------------|-------------------|
| ChecklistHydrate  | checklist   | input-checklist      | VInputChecklist | Checklist.vue     |
| TaggerHydrate      | tagger      | input-tagger         | VInputTagger    | Tagger.vue        |
| SelectHydrate      | select      | select (or input-select-scroll) | v-select | (Vuetify) |
| FileHydrate        | file        | input-file           | VInputFile      | File.vue          |
| ImageHydrate       | image       | input-image          | VInputImage     | Image.vue         |
| ...                | ...         | input-{kebab}        | VInput{Studly}  | {Studly}.vue      |

- **Hydrate**: `studlyName($input['type']) . 'Hydrate'` → e.g. `checklist` → `ChecklistHydrate`
- **Output type**: Hydrate sets `$input['type'] = 'input-{kebab}'` (e.g. `input-checklist`)
- **Vue component**: `registerComponents(..., 'inputs', 'VInput')` → `Checklist.vue` → `VInputChecklist`
- **Resolution**: `mapTypeToComponent('input-checklist')` → `v-input-checklist` (kebab of VInputChecklist)

### When Adding a New Input

1. **PHP**: Create `src/Hydrates/Inputs/{Studly}Hydrate.php` extending `InputHydrate`
   - Set `$input['type'] = 'input-{kebab}'` in `hydrate()`
   - Define `$requirements` for default schema keys
2. **Vue**: Create `vue/src/js/components/inputs/{Studly}.vue`
   - Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
   - Component registers as `VInput{Studly}` via `includeFormInputs` glob
3. **Registry** (optional): Add to `hydrateTypeMap` in `registry.js` for explicit mapping

### Schema Contract

Vue inputs expect schema props via `obj.schema` or `boundProps`:

- **Common**: `name`, `label`, `default`, `rules`, `items`, `itemValue`, `itemTitle`
- **Selectable**: `cascadeKey`, `cascades`, `repository`, `endpoint`
- **Files**: `accept`, `maxFileSize`, `translated`, `max`
- **Hydrate-only** (stripped before frontend): `route`, `model`, `repository`, `cascades`, `connector`

### Hydrate Types → Vue Components

See `vue/src/js/components/inputs/registry.js` → `hydrateTypeMap` for the full mapping.

Always ask for clarification if the request is ambiguous.

---

## Public presentation cache (agents reference)

When working on CMS public pages, URL stale resilience, or `presentationItem` cache:

### Store types

| `MODULAROUS_PRESENTATION_CACHE_STORE` | Behavior |
|---------------------------------------|----------|
| `url` (default) | File-primary HTML at `modularous-stale-by-url` via `UrlPresentationCacheStoreInterface` |
| `model` | Id-based `StaleFileCache` at `modularous-stale` |
| `none` | Always render; no presentation cache |

Admin types (`record`, `index`, `formItem`, `formattedItem`, `counts`) stay on Redis — independent of public store.

### Key env vars

| Env | Config key | Default | Notes |
|-----|------------|---------|-------|
| `MODULAROUS_PRESENTATION_CACHE_STORE` | `presentationItem.store` | `url` | `url` \| `model` \| `none` |
| `MODULAROUS_PRESENTATION_CACHE_SWR` | `presentationItem.swr` | `false` | Stale window + warm in controller |
| `MODULAROUS_PRESENTATION_CACHE_SERVE_FIRST` | `presentationItem.serve_first` | `true` | Middleware before controller (`url` only) |
| `MODULAROUS_PRESENTATION_CACHE_STALE_TTL` | `presentationItem.stale_ttl` | `604800` | `stale_expires_at` in `.meta` |
| `MODULAROUS_PRESENTATION_CACHE_URL_DRIVER` | `presentationItem.url.driver` | `file` | `shared_file` = EFS/NFS at `base_path` |
| `MODULAROUS_CACHE_URL_STALE_PATH` | `presentationItem.url.base_path` | `…/modularous-stale-by-url` | URL store directory |
| `MODULAROUS_RESOURCE_CACHE_SWR_STALE_PATH` | `presentationItem.model.stale_path` | `…/modularous-stale` | Model store (`store=model`) |
| `MODULAROUS_RESOURCE_CACHE_SWR_WARM_COOLDOWN` | `presentationItem.warm_dispatch_cooldown` | `600` | Warm job dedup lock TTL |
| `MODULAROUS_RESOURCE_CACHE_TTL_PRESENTATION_ITEM` | `ttl.presentationItem` | `900` | Fresh TTL → `expires_at` |

**Legacy (when new vars unset):** `MODULAROUS_CACHE_URL_STALE_ENABLED` → store; `MODULAROUS_RESOURCE_CACHE_SWR_ENABLED` → swr; `MODULAROUS_CACHE_URL_STALE_SERVE_FIRST` → serve_first; `MODULAROUS_CACHE_URL_STALE_TTL` / `MODULAROUS_RESOURCE_CACHE_SWR_STALE_TTL` → stale_ttl.

**Layer order:** global `enabled` → `store` → route `types.presentationItem` → store driver → `serve_first` → `swr` → query strategy → fresh/stale TTL.

**Toggle notes:**
- `store=url`: path files; no Redis for reads; middleware serves stale regardless of SWR
- `store=model`: id files; writes only when `swr=true`; requires Redis for `isEnabled()`
- `store=none`: always render
- `swr=false` + `serve_first=true`: middleware may still return `URL_STALE` past fresh TTL

### Middleware order

1. `ServeUrlKeyedStaleMiddleware` (global HTTP when `serve_first=true`) — before route match
2. CMS front route stack → `CmsController` → `CmsPublicPresentationItemCache`

Middleware and controller both use `ModularousCache::getUrlPresentationCacheStore()`, not `UrlKeyedStaleCache` directly.

### Query param caching

Per route: `presentation_cache_key` + `presentation_cache_query` in module cache config.

| Strategy | Behavior |
|----------|----------|
| `path_only` | Ignore query params |
| `path_and_query_allowlist` | Only allowlisted params in key; unknown params bypass cache |
| `path_and_query` | All query params sorted into key |

Middleware-only paths: `presentationItem.url.path_query` map.

### Purge / invalidation

On model purge, unpublish, or path change, `CacheInvalidation` clears **all query variants** for affected locale + path:

- `forgetByRelation(modelClass, id)`
- `forgetByModuleRoute(module, route)`
- `forgetPathVariants(locale, normalizedPath)` — scans meta files

Also forgets via `UrlRoute` rows when CMS module is present.

### DB-down resilience

URL store serves HTML from disk using `.meta` publication snapshot (`StalePublicationGate`). No Redis or DB required on HIT when `store=url` and file exists + visible.

### Admin warm must use public URL

Warmup jobs must render via public site URL (`CmsPublicSiteUrl`) so cached HTML matches visitor-facing host/path. Do not warm from admin hostname.

Each locale iteration must also run inside `CmsPublicPresentationWarmupContext` so `app()->getLocale()`, `trans()`, and locale-dependent Blade/helpers match the visitor locale (`tr`, `nl`, etc.) — not the admin session default.

### Deployment warm command

```bash
# All presentationItem routes (queued by default; Horizon on modularous-cache)
php artisan modularous:cache:warm-presentation

# Force synchronous warm
php artisan modularous:cache:warm-presentation --sync

# Filtered
php artisan modularous:cache:warm-presentation --module=Blog --route=BlogLanding --locale=en

# Preview without dispatching (dry run)
php artisan modularous:cache:warm-presentation --dry-run
```

Docs: `docs/src/pages/guide/console/cache/cache-warm-presentation.md`

### Deployment purge command

```bash
# All presentationItem filesystem caches (queued by default)
php artisan modularous:cache:purge-presentation

# Force synchronous purge
php artisan modularous:cache:purge-presentation --sync

# Filtered
php artisan modularous:cache:purge-presentation --module=Blog --route=BlogLanding --locale=en

# Preview without purging (dry run)
php artisan modularous:cache:purge-presentation --dry-run
```

Docs: `docs/src/pages/guide/console/cache/cache-purge-presentation.md`

### Multi-node extension point

| Piece | Path |
|-------|------|
| Interface | `src/Contracts/Cache/UrlPresentationCacheStoreInterface.php` |
| Default driver | `src/Services/Cache/FileUrlPresentationCacheDriver.php` |
| Resolution | `ModularousCacheService::resolveUrlPresentationCacheStore()` |
| Config | `modularous.cache.presentationItem.url.driver` |
| Container | `UrlPresentationCacheStoreInterface::class` singleton |

To add a driver: implement the interface, register in `resolveUrlPresentationCacheStore()`, preserve key helpers and variant purge semantics.

Docs: `docs/src/pages/guide/module-route-cache/url-stale-resilience.md`

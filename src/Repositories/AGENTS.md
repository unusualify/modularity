# Repository Instructions

Encapsulate data access and persistence for entities. Keep business logic in Services.

## Layout

| Path | Role |
|------|------|
| `src/Repositories/*Repository.php` | Repository classes (`SomethingRepository`) |
| `src/Repositories/Traits/` | Feature traits (`ImagesTrait`, `TranslationsTrait`, …) |
| `src/Repositories/Logic/` | Cross-cutting logic (`CacheableTrait`, `MethodTransformers`, `QueryBuilder`, …) |

Naming: PascalCase repositories; feature traits end with `Trait` (e.g. `MediasTrait`).

## MethodTransformers lifecycle (CRITICAL)

`MethodTransformers` discovers and invokes `{hook}{TraitBasename}` on every loaded trait:

| Convention | When |
|------------|------|
| `setColumns{T}` | Boot — which form input names this trait owns |
| `prepareFieldsBeforeCreate{T}` | Before insert |
| `prepareFieldsBeforeSave{T}` | Before any save |
| `beforeSave{T}` | Just before Eloquent `save()` |
| `afterSave{T}` | After save (pivots, file moves, …) |
| `hydrate{T}` | In-memory relations before save |
| `getFormFields{T}` | Populate edit form fields |
| `afterDelete{T}` / `afterRestore{T}` | Soft-delete / restore cleanup |
| `filter{T}` / `order{T}` | Index scopes / ordering |
| `getTableFilters{T}` / `getFormActions{T}` | Table tabs / form actions |

New feature trait methods **must** follow this naming or they will never run.

## Entity ↔ repository companions

When adding an entity trait, add the repository companion too (see root `AGENTS.md` table).

- Translated + metadata: `TranslationsTrait` **before** `TranslatableMetadataTrait`
- Cache invalidation wiring on model: `Core\HasCaching` → enable types in config; repo uses Logic `CacheableTrait`
- Admin purge/warm UI actions: `ResourceCacheActionsTrait` (pairs with controller `ManageResourceCache`)

## Cache (admin)

- `CacheableTrait` wraps index/record/counts when `shouldUseCache(type)` is true
- Do not put public `presentationItem` HTML caching in repositories — that is CMS/Http + `Services/Cache`

## Tests & docs

- Tests: `tests/Repositories`
- Docs: `docs/src/pages/system-reference/backend/repository-traits/overview.md`
- Cacheable: `.../repository-traits/logic/CacheableTrait.md`

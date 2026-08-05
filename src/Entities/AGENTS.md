# Entities — Agent Rules

Eloquent models and model traits for the Modularous package.

## Layout

| Path | Role |
|------|------|
| `src/Entities/` | Model classes (PascalCase) |
| `src/Entities/Traits/` | Top-level domain traits |
| `src/Entities/Traits/Core/` | Plumbing: caching, scopes, change tracking |
| `src/Entities/Traits/Auth/` | OAuth / registration helpers |
| `src/Entities/Traits/Secondary/` | Optional: nesting, blocks, revisions, related |

## Hard rules

1. Prefer Repository + Service for writes; do not encourage direct model access from controllers.
2. Factor reusable behavior into traits; keep models thin.
3. When adding a domain trait, add the **repository companion** (root `AGENTS.md` companion table).
4. Cache: use `Core\HasCaching` so `CacheObserver` invalidates admin module-route cache. Cross-model deps → also `HasCacheDependents` + `$cacheDependents`.
5. Prefer `Core\HasScopes` / `Core\ModelHelpers` over deprecated top-level aliases.
6. PHP 8.1+ types, PSR-12, PHPDoc on public methods.

## Tests & docs

- Tests: `tests/Entities`
- Docs: `docs/src/pages/system-reference/backend/entity-traits/overview.md`
- Caching trait: `.../entity-traits/core/has-caching.md`
- Per-module cache setup: `docs/src/pages/guide/module-route-cache/per-module-setup.md`

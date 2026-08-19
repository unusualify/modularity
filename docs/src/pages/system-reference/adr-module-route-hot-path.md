---
sidebarPos: 16
sidebarTitle: ADR — ModuleRoute Hot Path
---

# ADR: ModuleRoute Hot-Path Contract

**Status:** Accepted  
**Date:** 2026-08-11

## Context

`ModuleRoute` is the intended first-class API for module routes (sidebar, controllers, inspect, Blueprint). Early wiring that forced **every** hot-path call through `ModuleRouteRegistry` (and eager construct / presentation) roughly **doubled** admin document request time (~3s → ~7s) in production-like local measurements.

Root causes observed:

1. Sidebar / route boot calling `$module->isSingleton()` which delegated into **registry + materialization** for every route.
2. `hasRoute` / `getRouteNames` going through registry `names()` (union + sort) instead of activator status keys.
3. `CoreController::__construct` eagerly resolving `ModuleRoute` — runs during `RouteServiceProvider` `additionalRoutes` `app()->make($controller)` for every controller when `route:cache` is absent.
4. `getConfigFieldsByRoute` always going through Blueprint/presentation resolution on large configs (e.g. PressRelease).

Hardcoding sidebar `isSingleton` to `false` did **not** change latency — proving the cost was broader than navigation alone.

## Decision

Adopt a **hot-path contract**: consumers may use the `ModuleRoute` **object API**, but implementations must keep request-critical work **lazy, memoized, and raw-first**. Registry and Blueprint are not free.

### Principle

| Layer | Role |
|-------|------|
| **API surface** | Prefer `ModuleRoute` (`$module->moduleRoute('Post')->…`) for domain readability |
| **Hot-path cost** | Must match (or beat) pre-ModuleRoute activator / class-string / `data_get` costs |
| **Registry** | Lazy, request-scoped instance cache — for inspect, explicit domain use, opt-in presentation |
| **Module facades** | May share helpers with `ModuleRoute` but **must not** require registry on spam paths |

### Cost table (normative)

| Operation | Allowed hot-path cost | Forbidden on hot path |
|-----------|----------------------|------------------------|
| `isSingleton` | Class-string `class_exists` + `classHasTrait` + **memo** | `App::make(repository)`, model instantiate, new registry per call |
| `isEnabled` / `isEnabledRoute` | Activator `hasStatus` (filesystem default) | `Modularous::findOrFail` + store indirection per call |
| `hasRoute` / `getRouteNames` | Activator status **keys** + Module memo | `ModuleRouteRegistry::names()` union/sort as default |
| Config field read (edit form) | Preloaded raw / nested-first `data_get` / flat | Presentation resolver unless Blueprint meta / non-config driver |
| Controller construct | Module + `routeName` only | Eager `$this->getModuleRoute()` |
| Sidebar listing | **`getRawRouteConfigs` + `Module::isSingleton`** only | `sidebarRoutes()` / `moduleRoute()` / `moduleRoutes()` for menu build |
| `hasTable` / `features()` | Lazy, inspect / doctor / rare | Sidebar, route boot, every `getConfigFieldsByRoute` |

### ModuleRoute instance rules

1. **Construct is cheap** — no config load, no trait scan, no Schema.
2. **`rawConfig()` / `config()` lazy** — load once per instance; prefer raw for hot reads.
3. **`isSingleton()` memo** — class-string only (same helper as `Module::isSingleton` when possible).
4. **`presentation()` / Blueprint** — only when:
   - global `module_route_presentation.driver` ≠ `config`, or
   - route has `blueprint` / `presentation` meta, or
   - nested `index`/`form` leaf is a class string / driver meta array.
5. **Instance cache** — `ModuleRouteRegistry::find` / `make` caches per Module per request.
6. **`moduleRoute($name)` is O(1) after warm** — `moduleRoutes()` may remain expensive (inspect OK).

### Controller rules

```text
__construct          → module, routeName/moduleRouteName, repository
preload              → MUST NOT resolve ModuleRoute (no-op)
getRouteConfig / inputs / headers → raw Module getters; use ModuleRoute only if already set
getConfigFieldsByRoute → raw/data_get first; ModuleRoute presentation only if shouldUse…
URL / prefix         → may use ModuleRoute only when already resolved; never force registry
getModuleRoute()     → lazy resolve on explicit call only
```

Never resolve `ModuleRoute` solely because `additionalRoutes` instantiates the controller.
Never call `ensureModuleRouteResolved()` from preload, config getters, or default URL helpers.

### Sidebar rules

```text
OK:  getRawRouteConfigs(valid) + Module::isSingleton($name)   ← ModularousNavigation MUST use this
NO:  sidebarRoutes() / moduleRoute() for every menu render     ← still builds registry + FeatureDetector per module (~0.5–1s)
NO:  moduleRoutes() / enabledModuleRoutes() for menu render
NO:  headline() that forces features()/hasTable()
```

`Module::sidebarRoutes()` remains available for inspect / explicit ModuleRoute consumers — **not** for admin sidebar hot path until registry construct is free.

`headline()` must prefer configured string before singleton detection.

### Shared helpers (recommended)

Avoid dual implementations:

```php
// Conceptual — Module owns memo; ModuleRoute delegates
Module::isSingleton($name)           // hot path entry
ModuleRoute::isSingleton()           // calls same class-string + memo helper
```

Same pattern for parent detection when both need it.

### Migration order (same perf)

1. **Keep sidebar on raw config** — do not migrate navigation to ModuleRoute until measured parity.
2. Controllers may expose `getModuleRoute()` lazily; config/URL hot paths stay raw-first.
3. Nested `index`/`form` + Blueprint class extract — opt-in per route.
4. Drop unused flat config leaves after class source of truth.

Do **not** set global presentation driver to `class` for all routes as a first step.

### Verification

When changing ModuleRoute hot paths:

1. Measure admin **document** request (e.g. edit) with changes on vs stashed — target parity with post-fix baseline (~raw config path).
2. Isolating one call site (e.g. sidebar singleton → `false`) must not be the only proof; also check construct + `getConfigFieldsByRoute` + boot without `route:cache`.
3. Keep package tests for enable/disable (same activator instance on filesystem driver) and presentation nested/legacy fallback.

## Consequences

- `Module::hasRoute` / `getRouteNames` / `isSingleton` / `isEnabled*` stay **registry-free** on filesystem default.
- `CoreController` does not eager-load `ModuleRoute` in `__construct` or preload.
- Admin sidebar stays on raw config arrays (never `sidebarRoutes()`).
- `getConfigFieldsByRoute` uses `shouldUseModuleRoutePresentation()` (or equivalent) before Blueprint path.
- `ModuleRouteRegistry::find` resolves config-listed names without forcing status∪config union.
- Docs and agents treat this ADR as binding when wiring sidebar/controllers to ModuleRoute.

## Non-goals

- Making `moduleRoutes()` as cheap as `getRouteNames()` (inspect may pay full materialization).
- Removing `config.php` in the same change set as API migrate.
- Database status driver matching filesystem hot-path cost (DB is explicitly opt-in).

## Related

- [ModuleRoute](/guide/console/module/module-route)
- [ModuleRoute Blueprint](/guide/console/module/module-route-blueprint)
- [ADR — ModuleRoute Blueprint](./adr-module-route-blueprint)
- [Route Inspect](/guide/console/module/route-inspect)

---
sidebarPos: 1
sidebarTitle: System Reference
---

# System Reference

Modularous (Modularous) is a Laravel package that provides a modular admin panel powered by Vue.js, Vuetify, and Inertia. It uses the Repository pattern for data access, config-driven forms and tables, and a Hydrate system to transform module config into frontend schema.

## Documentation Index

| Page | Description |
|------|-------------|
| [Architecture](./architecture) | System overview, request flow, schema flow, core classes |
| [ADR — ModuleRoute Blueprint](./adr-module-route-blueprint) | Index/form UI class tree, field map, drivers |
| [ADR — ModuleRoute Hot Path](./adr-module-route-hot-path) | Perf contract for ModuleRoute vs registry / presentation |
| [Hydrates](./hydrates) | Backend → frontend schema transformation (input types) |
| [Repositories](./repositories) | Data access layer, lifecycle, Logic traits |
| [Backend](./backend/overview) | Controllers, Console commands, Entities, Services |
| [Frontend](./frontend/overview) | Vue structure, form/table flow, hooks, store |
| [Config](./config) | Configuration layers (merges, defers, publishes) |
| [Modules](./modules) | Module vs route activation, structure |
| [API](./api) | Common patterns and use cases |
| [Pinia Migration](./pinia-migration) | Vuex → Pinia migration path |
| [Console Conventions](./console-conventions) | Command naming and signature rules |
| [Entities](./entities) | Models, entity traits, enums |
| [Features Pattern](./features) | Entity + Repository + Hydrate triple pattern |

**Guides**

| Page | Description |
|------|-------------|
| [Remote API](/guide/remote-api/overview) | Sync entities from external JSON APIs (connector, cache, admin UI) |

## Quick Reference

**Key config keys**
- `modularous.services.*` — services (currency_exchange, etc.)
- `modularous.roles` — role definitions
- `modularous.traits` — entity traits
- `modularous.paths` — base paths
- `modularous.currency_provider` — currency provider FQCN

**Key commands**
- `modularous:build` — rebuild Vue assets
- `modularous:route:enable` / `modularous:route:disable` — toggle routes
- `modularous:route:status` — list route status per module

**Paths**
- Package source: `packages/modularous/src/`
- Vue source: `packages/modularous/vue/src/js/`
- Modules: `config('modules.paths.modules')` (default: `modules/`)

## For Contributors

See [AGENTS.md](https://github.com/unusualify/modularous/blob/main/AGENTS.md) for package development rules, patterns, and conventions.

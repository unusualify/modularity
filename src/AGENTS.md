# Backend Core Instructions

Concise prompts for agents working in `src/` (backend). Keep requests repository-scoped, state expected file/path, constraints, and tests.

Format:
- Action | Path | Constraints | Tests

Examples:
- Add trait | src/Entities/Traits/HasVersioning.php | PHP 8.1 types, PSR-12, register via feature ServiceProvider if needed | tests/Entities/HasVersioningTest.php
- Implement service | src/Services/CoverageService.php | Use DI, return typed DTOs, no facades | tests/Services/CoverageServiceTest.php
- Add feature bindings | src/Providers/{Feature}ServiceProvider.php | Singletons/aliases/log channels for that feature only; register in ModularousProvider::$providers — do not add to BaseServiceProvider | tests/Services/{Feature}/*
- Update provider | src/Providers/ModularousProvider.php | Register feature providers, publish config | tests/Providers/ModularousProviderTest.php

Keep prompts short and concrete — avoid high-level product requests. Always include a target path and a test expectation.

**Feature providers:** New Modularous features that need container bindings must use a dedicated `{Feature}ServiceProvider` (see `RemoteApiServiceProvider`, `ArtisanRunnerServiceProvider`, `CoverageServiceProvider`) registered from `ModularousProvider`. Keep `BaseServiceProvider` for package-wide core only.

## Folder AGENTS (read before edit)

| Area | AGENTS |
|------|--------|
| Entities / model traits | `src/Entities/AGENTS.md` |
| Repositories / MethodTransformers | `src/Repositories/AGENTS.md` |
| Http controllers / middleware | `src/Http/AGENTS.md` |
| Console commands | `src/Console/AGENTS.md` |
| Hydrates (form schema) | `src/Hydrates/AGENTS.md` |
| Cache services | `src/Services/Cache/AGENTS.md` |
| CMS module | `modules/Cms/AGENTS.md` |
| Vue / inputs | `vue/src/js/AGENTS.md` |
| Package root / docs map | `AGENTS.md` (package root) |

Docs map: see package root `AGENTS.md` → **DOCS MAP**.

Core classes (short):
- `Modularous.php` | Module manager extending Nwidart FileRepository. Handles scanning, caching, vendor/app paths, auth names, and helpers (e.g. `scan()`, `allEnabled()`, `getVendorPath()`).
- `Module.php` | Represents a single module. Loads config/providers/commands, exposes route names, middleware aliases, paths and helpers (e.g. `getConfig()`, `getRouteNames()`, `getDirectoryPath()`).

When asking agents to change module behavior, reference these classes and a test file (e.g. `tests/Services/ModularousBehaviorTest.php`).

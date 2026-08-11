---
sidebarPos: 15
sidebarTitle: ADR — ModuleRoute Blueprint
---

# ADR: ModuleRoute Blueprint

**Status:** Accepted  
**Date:** 2026-08-10

## Context

Module route UI definitions (`inputs`, `headers`, `table_options`, filters, actions, …) often live as large inline arrays in `Config/config.php`. Class-backed providers were introduced under a “presentation” driver, but:

1. The default class path used HTTP **`Routes/`** (`web.php` / `api.php`), which collides conceptually.
2. “Presentation” is too narrow once toolbar/row/form actions and filters are included.
3. Flat `table_*` / `form_*` / `index_*` prefixes repeat surface context and hurt readability.
4. Short class names (`Columns`) hurt IDE search and self-describing `::class` usage in config.

## Decision

Adopt **ModuleRoute Blueprint**: driver-based resolution of invokable providers that describe index/form UI for a route.

### Folder / namespace

```text
Modules/{Module}/Blueprint/{RouteStudly}/Index/{RouteStudly}Index{Field}.php
Modules/{Module}/Blueprint/{RouteStudly}/Form/{RouteStudly}Form{Field}.php
```

Example:

```text
Blueprint/PressRelease/Index/PressReleaseIndexColumns.php
→ Modules\PressRelease\Blueprint\PressRelease\Index\PressReleaseIndexColumns
```

### Class naming (conscious duplication)

Pattern: **`{RouteStudly}{Surface}{Field}`**

Route name appears in **both** the path and the class name on purpose:

- File / symbol search by route name (`PressReleaseIndex…`)
- Self-describing config: `PressReleaseIndexColumns::class`
- Safer route removal: delete `Blueprint/{Route}/` and `rg {Route}Index|{Route}Form`

### Config surfaces

Nest under **`index`** | **`form`**. Drop `table_*` / `form_*` / `index_*` prefixes on child keys.

`scopes` stay at **route root** (shared query scopes for index and form; not a Blueprint class).

### Canonical field map

| Legacy flat key | Canonical nested | Blueprint class |
|-----------------|------------------|-----------------|
| `table_options` | `index.options` | `{Route}IndexOptions` |
| `headers` | `index.columns` | `{Route}IndexColumns` |
| `index_with` | `index.with` | `{Route}IndexWith` |
| `index_appends` | `index.appends` | `{Route}IndexAppends` |
| `table_filters` | `index.filters` | `{Route}IndexFilters` (chip / count filters) |
| `filters` | `index.advanced_filters` | `{Route}IndexAdvancedFilters` (Vuetify input panel) |
| `table_actions` | `index.actions` | `{Route}IndexActions` |
| `table_row_actions` | `index.row_actions` | `{Route}IndexRowActions` |
| `form_options` | `form.options` | `{Route}FormOptions` |
| `form_with` | `form.with` | `{Route}FormWith` |
| `form_appends` | `form.appends` | `{Route}FormAppends` |
| `inputs` | `form.inputs` | `{Route}FormInputs` |
| `form_actions` | `form.actions` | `{Route}FormActions` |

### Drivers

Same family as status store:

| Driver | Source |
|--------|--------|
| `config` (default) | Inline arrays — nested `index`/`form` preferred, legacy flat keys as fallback |
| `class` | Invokable Blueprint provider |
| `database` | Reserved (falls back to config until implemented) |

**Config payload precedence:** nested leaf (`index.columns`, `form.inputs`, …) if present → else legacy flat key (`headers`, `inputs`, …). An explicit empty nested array wins over legacy.

**Class / driver meta precedence:** nested leaf class string or `{ driver, class }` → else `blueprint` / `presentation` keyed by legacy field name.

Driver meta key: **`blueprint`** (alias: `presentation` until the Presentation\* types are renamed).

```php
use Modules\PressRelease\Blueprint\PressRelease\Index\PressReleaseIndexColumns;
use Modules\PressRelease\Blueprint\PressRelease\Form\PressReleaseFormInputs;

'press_release' => [
    // Preferred: nested surfaces (inline array or class)
    'index' => [
        'columns' => PressReleaseIndexColumns::class,
    ],
    'form' => [
        'inputs' => PressReleaseFormInputs::class,
    ],

    // Still supported: blueprint meta keyed by legacy field names
    // 'blueprint' => [
    //     'headers' => ['driver' => 'class', 'class' => PressReleaseIndexColumns::class],
    //     'inputs' => PressReleaseFormInputs::class,
    // ],
],
```

### Provider contract

```php
public function __invoke(ModuleRoute $route): array;
```

One field per class. Hydrates (`InputHydrator` / `HeaderHydrator`, …) stay unchanged: they transform items **after** Blueprint resolution.

### Convention FQCN (shipped for existing fields)

With `module_route_presentation.path = Blueprint`:

| Legacy field | Convention class |
|--------------|------------------|
| `inputs` | `{ModuleNs}\Blueprint\{Route}\Form\{Route}FormInputs` |
| `headers` | `{ModuleNs}\Blueprint\{Route}\Index\{Route}IndexColumns` |
| `table_options` | `{ModuleNs}\Blueprint\{Route}\Index\{Route}IndexOptions` |

## Consequences

- Make/scaffold commands live under `src/Console/Blueprint/` (`modularous:make:blueprint`, `modularous:make:blueprint:field`).
- Stubs: `blueprint-form-inputs`, `blueprint-index-columns`, `blueprint-index-options`, `blueprint-provider`.
- HTTP `Routes/web.php` (etc.) remain HTTP-only.
- Nested `index`/`form` config reading is shipped; flat keys remain readable as fallback.
- `presentation` config key remains an alias of `blueprint`.

## Non-goals (this ADR)

- Database Blueprint store CRUD UI
- Moving `scopes` into Blueprint
- Splitting `config.php` array files into Blueprint leafs
- Migrating PressRelease (or other apps) to nested keys / extracted classes in the same change set
- Renaming `ModuleRoutePresentation*` PHP types to `Blueprint*` (follow-up)

## Related

- [ModuleRoute Blueprint guide](/guide/console/module/module-route-blueprint)
- [ModuleRoute](/guide/console/module/module-route)
- [ADR — ModuleRoute Hot Path](./adr-module-route-hot-path)
- [Hydrates](./hydrates)

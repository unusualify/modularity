---
sidebarPos: 8
sidebarTitle: ModuleRoute Blueprint
---

# ModuleRoute Blueprint

Resolves route UI fields through drivers (`config` | `class` | `database`). Full contract: [ADR — ModuleRoute Blueprint](/system-reference/adr-module-route-blueprint).

## Shipped today

`ModuleRoute` resolves Blueprint fields with **nested-first** config reading:

| Prefer | Fallback |
|--------|----------|
| `index.columns` / `form.inputs` / `index.filters` / `index.advanced_filters` / `index.actions` / `index.row_actions` / `form.actions` / … | legacy `headers` / `inputs` / `table_filters` / `filters` / `table_actions` / `table_row_actions` / `form_actions` / … |

```php
$route->inputs();
$route->headers();
$route->tableOptions();
$route->tableFilters();
$route->advancedFilters();
$route->tableActions();
$route->tableRowActions();
$route->formActions();
$route->presentationDriver('inputs'); // config|class|database
```

Runtime wiring (hot-path ADR):

- `CoreController::getConfigFieldsByRoute` — ModuleRoute when `shouldUse…`; otherwise nested-first then flat on preloaded config (no registry).
- `TableFilters::getTableAdvancedFilters` — via `getConfigFieldsByRoute('filters')`; strips `fixed` (Panel Raw owns `filters.fixed`).
- `Module::getNavigationActions` — nested `index.row_actions` / Blueprint via `resolveRouteBlueprintField`, then belongs-to links.

Driver / class meta: nested leaf (`index.columns => Class::class`) **or** `blueprint` / `presentation` keyed by legacy field name.

## Class convention (aligned with ADR)

```text
Blueprint/{Route}/Form/{Route}FormInputs.php
Blueprint/{Route}/Index/{Route}IndexColumns.php
Blueprint/{Route}/Index/{Route}IndexOptions.php
```

| Legacy field | Nested | Class |
|--------------|--------|-------|
| `inputs` | `form.inputs` | `{Route}FormInputs` |
| `headers` | `index.columns` | `{Route}IndexColumns` |
| `table_options` | `index.options` | `{Route}IndexOptions` |

```php
'style_sheet' => [
    'name' => 'StyleSheet',
    'index' => [
        'columns' => \Modules\Cms\Blueprint\StyleSheet\Index\StyleSheetIndexColumns::class,
    ],
    'form' => [
        'inputs' => \Modules\Cms\Blueprint\StyleSheet\Form\StyleSheetFormInputs::class,
    ],
    // legacy also works:
    // 'blueprint' => [
    //     'inputs' => ['driver' => 'class', 'class' => StyleSheetFormInputs::class],
    //     'headers' => StyleSheetIndexColumns::class,
    // ],
],
```

### PressRelease (app extract)

`modules/PressRelease/Blueprint/{Route}/{Index|Form}/*` holds extracted providers (`__()` preserved). Config wires nested class leaves **and** keeps legacy flat arrays until the host app runs Modularous with ModuleRoute presentation (then flat keys can be deleted). Covers inputs/columns/options/filters/actions/row_actions/with/form with/appends/actions for PressRelease (+ package/addon/payment subsets).

```php
'press_release' => [
    'index' => [
        'columns' => \Modules\PressRelease\Blueprint\PressRelease\Index\PressReleaseIndexColumns::class,
        // …
    ],
    'form' => [
        'inputs' => \Modules\PressRelease\Blueprint\PressRelease\Form\PressReleaseFormInputs::class,
        // …
    ],
    // legacy flat `headers` / `inputs` / … still present during dual-run
],
```

## Scaffold

```bash
# Preview planned writes (no files created)
php artisan modularous:make:blueprint SystemNotification MyNotification \
  --only=options,columns,filters,actions,row_actions,inputs \
  --from-config --write-config --dry-run

php artisan modularous:make:blueprint:field SystemNotification MyNotification row_actions \
  --from-config --dry-run

# Apply
php artisan modularous:make:blueprint Cms StyleSheet --from-config --write-config
php artisan modularous:make:blueprint Cms StyleSheet --options --force
php artisan modularous:make:blueprint PressRelease PressRelease --all --from-config
php artisan modularous:make:blueprint:field Cms Redirect bulk_sheet --from-config --write-config
php artisan modularous:make:route Blog Post --presentation=class
```

`--dry-run` prints an action table (`create` / `overwrite` / `skip`), target paths, FQCNs, and seed summary (`from-config: source (N bytes)`, `from-config: N items`, or `empty stub`). With `--write-config`, it **persists** nested `index` / `form` (and route-root `bulk_sheet`) `::class` leaves into `Config/config.php` (and comments legacy flat keys unless `--keep-flats`). Dry-run previews the config patch without writing.

`--from-config` prefers the **raw** `Config/config.php` array literal (keeps `__()` / `Component::` / short `[]` style). If source extraction fails, it falls back to an evaluated payload formatted via `array_export` (still short-array, but translations are already resolved).

Stubs: `src/Console/stubs/blueprint-form-inputs.stub`, `blueprint-index-columns.stub`, `blueprint-index-options.stub`, `blueprint-provider.stub`.

Commands live under `src/Console/Blueprint/` (`MakeBlueprintCommand`, `MakeBlueprintFieldCommand`).

## Env

```env
MODULAROUS_MODULE_ROUTE_PRESENTATION_DRIVER=config
```

Path default: `Blueprint` (`modularous.module_route_presentation.path`).

## Nested config shape (runtime-supported)

```php
'press_release' => [
    'scopes' => [ /* route root */ ],
    'index' => [
        'columns' => PressReleaseIndexColumns::class, // or inline array
        'options' => PressReleaseIndexOptions::class,
        'filters' => PressReleaseIndexFilters::class,
        'advanced_filters' => PressReleaseIndexAdvancedFilters::class,
        'actions' => PressReleaseIndexActions::class,
        'row_actions' => PressReleaseIndexRowActions::class,
    ],
    'form' => [
        'inputs' => PressReleaseFormInputs::class,
        'actions' => PressReleaseFormActions::class,
    ],
],
```

App migration (e.g. PressRelease extract) is optional; nested reader works when you adopt this shape. See the ADR field map for the full legacy → canonical table.

## Related

- [ADR — ModuleRoute Blueprint](/system-reference/adr-module-route-blueprint)
- [ADR — ModuleRoute Hot Path](/system-reference/adr-module-route-hot-path)
- [ModuleRoute](./module-route)
- Hydrates: `docs/src/pages/system-reference/hydrates.md`

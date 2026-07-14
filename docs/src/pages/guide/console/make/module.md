---
sidebarPos: 2
sidebarTitle: make:module
---

# make:module

> Bootstrap a complete module

**Signature**: `modularous:make:module`

**Aliases**: `m:m:m`, `unusual:make:module`

**Category**: Make

---

## Description

`make:module` is the primary entry point for creating a new Modularous module. It calls nWidart's `module:make` to create the folder skeleton, then immediately calls [`make:route`](./route) with the same module name to generate the full first-route file set (model, controller, repository, migration, form request, and Vue stubs).

---

## Usage

```
modularous:make:module [options] <module>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | PascalCase module name (e.g. `Blog`, `UserProfile`) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--schema=` | | Schema fields for the initial migration (`title:string,body:text`) |
| `--rules=` | | Validation rules for the Form Request |
| `--relationships=` | | Relationship definitions forwarded to the model |
| `--custom-model=` | | Reuse an existing model class instead of generating a new one |
| `--table-name=` | | Override the auto-derived table name |
| `--force` | `-f` | Overwrite existing files |
| `--system` | | Create inside the Modularous system modules path |
| `--no-migrate` | | Skip running migrations after generation |
| `--no-migration` | | Skip creating the migration file entirely |
| `--no-defaults` | | Skip Modularous default fields (e.g. `is_published`) |
| `--notAsk` | | Skip all interactive trait questions |
| `--all` | | Accept all trait questions with `yes` |
| `--just-stubs` | | Only regenerate stubs for existing module routes |
| `--stubs-only=` | | Comma-separated list of stub types to include (used with `--just-stubs`) |
| `--stubs-except=` | | Comma-separated list of stub types to exclude (used with `--just-stubs`) |
| `--test` | | Dry-run / preview mode |

#### Trait flags

| Option | Short | Description |
|--------|-------|-------------|
| `--addTranslation` | `-T` | Add translatable content support |
| `--addMedia` | `-M` | Add media/image attachment support |
| `--addFile` | `-F` | Add file attachment support |
| `--addPosition` | `-P` | Add sortable position support |
| `--addSlug` | `-S` | Add slug generation support |
| `--addAuthorized` | `-A` | Add scoped authorization |
| `--addFilepond` | `-FP` | Add FilePond file upload support |
| `--addUuid` | | Add UUID primary key support |
| `--addSnapshot` | `-SS` | Add snapshot/versioning support |
| `--addPrice` | | Add pricing trait |

---

## Examples

### Minimal module

```bash
php artisan modularous:make:module Blog
```

### Module with schema and translation

```bash
php artisan modularous:make:module Blog --schema="title:string,body:text" --addTranslation
```

### Module with all options set non-interactively

```bash
php artisan modularous:make:module Shop \
    --schema="name:string,price:decimal:8,2" \
    --rules="name:required|string,price:required|numeric" \
    --addTranslation \
    --addMedia \
    --addPrice \
    --notAsk
```

### Re-generate stubs only for an existing module

```bash
php artisan modularous:make:module Blog --just-stubs --stubs-except=migration
```

### Preview without writing files

```bash
php artisan modularous:make:module Blog --test
```

---

## Notes

- `make:module` wraps `module:make` (nWidart) + `make:route` — it does not generate anything itself.
- Pass `--system` only when adding to the Modularous core; requires a non-production environment.
- Use `--just-stubs` to fix or refresh stub files after changing the config without re-running migrations.

## See also

- [make:route](./route) — add a subsequent route to the same module
- [make:stubs](./stubs) — regenerate specific stub files
- [System Reference](/guide/console/make/overview) — class internals

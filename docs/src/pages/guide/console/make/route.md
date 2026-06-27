---
sidebarPos: 3
sidebarTitle: make:route
---

# make:route

> Add a new route to an existing module

**Signature**: `modularous:make:route`

**Aliases**: `m:m:r`, `u:m:r`, `unusual:make:route`

**Category**: Make

---

## Description

`make:route` scaffolds the complete file set needed for one CRUD route inside an existing module: model, migration, controller(s), repository, form request, Vue stubs, and permission seeds. It delegates to [`RouteGenerator`](/system-reference/backend/generators/route-generator) which orchestrates all file creation in the correct order.

---

## Usage

```
modularous:make:route [options] <module> <route>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | The module that owns this route |
| `route` | yes | Route name (e.g. `Post`, `ProductCategory`) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--schema=` | | Schema fields for the migration (`title:string,body:text`) |
| `--rules=` | | Validation rules for the Form Request |
| `--relationships=` | | Relationship definitions (forwarded to model + migration) |
| `--custom-model=` | | Reuse an existing model class |
| `--table-name=` | | Override the auto-derived table name |
| `--force` | `-f` | Overwrite existing files |
| `--plain` | `-p` | Skip route file creation (model + migration only) |
| `--no-migrate` | | Skip running migrations after generation |
| `--no-migration` | | Skip creating the migration file |
| `--no-defaults` | | Skip Modularous default fields |
| `--notAsk` | | Skip all interactive trait questions |
| `--all` | | Accept all trait questions with `yes` |
| `--fix` | | Fix model config errors on an existing route |
| `--test` | | Dry-run / preview mode |

#### Trait flags (same as make:module)

`-T` / `--addTranslation`, `-M` / `--addMedia`, `-F` / `--addFile`, `-P` / `--addPosition`, `-S` / `--addSlug`, `-A` / `--addAuthorized`, `-FP` / `--addFilepond`, `--addUuid`, `-SS` / `--addSnapshot`, `--addPrice`

---

## Examples

### Minimal route

```bash
php artisan modularous:make:route Blog Post
```

### Route with schema and rules

```bash
php artisan modularous:make:route Blog Post \
    --schema="title:string,body:text,published_at:timestamp:nullable" \
    --rules="title:required|string|max:255,body:required|string"
```

### Route with translation and media

```bash
php artisan modularous:make:route Blog Post --addTranslation --addMedia
```

### Route using an existing model (no new model/migration)

```bash
php artisan modularous:make:route Shop Order \
    --custom-model="App\Models\Order" \
    --no-migration \
    --no-defaults
```

### Preview output without writing

```bash
php artisan modularous:make:route Blog Post --test
```

---

## See also

- [make:module](./module) — create the parent module first
- [make:model](./model) — generate only the model
- [make:migration](./migration) — generate only the migration
- [System Reference](/guide/console/make/overview) — class internals

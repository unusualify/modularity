---
sidebarPos: 4
sidebarTitle: make:model
---

# make:model

> Create an Eloquent model for a module

**Signature**: `modularous:make:model`

**Alias**: `mod:m:model`

**Category**: Make

---

## Description

Generates an Eloquent model class with optional trait composition, relationship methods, and fillable fields. When traits like `addTranslation` or `addSlug` are enabled, companion models (`{Name}Translation`, `{Name}Slug`) are automatically created alongside the main model. When `--relationships` is provided, many-to-many pivot models are also created.

---

## Usage

```
modularous:make:model [options] <model> [<module>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `model` | yes | Model class name (e.g. `Post`, `ProductVariant`) |
| `module` | no | Target module; omit to create in `app/Models/` |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--fillable=` | | Comma-separated fillable field names |
| `--relationships=` | | Relationship definition string |
| `--override-model=` | | Fully-qualified base class to extend instead of the default |
| `--self` | | Write to the Modularous vendor path |
| `--force` | `-f` | Overwrite existing files |
| `--soft-delete` | `-s` | Add `SoftDeletes` trait |
| `--has-factory` | | Add `HasFactory` trait and `newFactory()` method |
| `--no-defaults` | | Skip Modularous default fillable fields |
| `--notAsk` | | Skip interactive trait questions |
| `--all` | | Accept all trait questions |
| `--test` | | Dry-run / preview mode |

#### Trait flags

| Option | Short | Description |
|--------|-------|-------------|
| `--addTranslation` | `-T` | Creates a `{Name}Translation` companion model |
| `--addMedia` | `-M` | Attach `HasMedia` trait |
| `--addFile` | `-F` | Attach `HasFile` trait |
| `--addPosition` | `-P` | Attach `HasPosition` trait |
| `--addSlug` | `-S` | Creates a `{Name}Slug` companion model |
| `--addAuthorized` | `-A` | Attach authorization scope trait |
| `--addFilepond` | `-FP` | Attach FilePond trait |
| `--addUuid` | | Use UUID primary key |
| `--addSnapshot` | `-SS` | Attach snapshot/versioning trait |
| `--addPrice` | | Attach pricing trait |

---

## Examples

### Basic model for a module

```bash
php artisan modularous:make:model Post Blog
```

### Model with fillable and soft-delete

```bash
php artisan modularous:make:model Post Blog \
    --fillable="title,body,published_at" \
    --soft-delete
```

### Translatable model with media

```bash
php artisan modularous:make:model Post Blog --addTranslation --addMedia
```

### Standalone model in app/Models (no module)

```bash
php artisan modularous:make:model Article
```

### Preview without writing

```bash
php artisan modularous:make:model Post Blog --test
```

---

## Output files

| Condition | File created |
|-----------|--------------|
| Always | `{Module}/Entities/Post.php` |
| `--addTranslation` | `{Module}/Entities/Translations/PostTranslation.php` |
| `--addSlug` | `{Module}/Entities/Slugs/PostSlug.php` |
| `--relationships` (BelongsToMany) | Pivot model(s) in `{Module}/Entities/` |

---

## See also

- [make:migration](./migration) — create the matching migration
- [make:repository](./repository) — create the matching repository
- [make:model:trait](./model-trait) — create a reusable entity trait
- [System Reference](/guide/console/make/overview)

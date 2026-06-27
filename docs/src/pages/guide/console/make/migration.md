---
sidebarPos: 5
sidebarTitle: make:migration
---

# make:migration

> Create a database migration file

**Signature**: `modularous:make:migration`

**Alias**: `mod:m:migration`

**Category**: Make

---

## Description

Generates a timestamped migration file for a module or the host app. Supports all standard Laravel migration patterns (`create`, `add`, `delete`, `drop`, `plain`) plus two Modularous-specific pivot patterns. When `--addTranslation` or `--addSlug` traits are active, companion translation/slug migration blocks are appended automatically.

---

## Usage

```
modularous:make:migration [options] <name> [<module>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Migration name (e.g. `create_posts_table`, `add_status_to_posts_table`) |
| `module` | no | Target module; omit for `database/migrations/` |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--fields=` | | Schema field string (`title:string,body:text`) |
| `--route=` | | Route name used for pivot table naming |
| `--relational=` | | Pivot type: `BelongsToMany` or `MorphedByMany` |
| `--table-name=` | | Override auto-derived table name |
| `--plain` | | Create an empty migration body |
| `--self` | | Write to Modularous vendor migrations |
| `--force` | `-f` | Overwrite existing files |
| `--no-defaults` | | Skip Modularous default fields |
| `--test` | | Dry-run / preview mode |

#### Trait flags (affect extra schema blocks)

`-T` / `--addTranslation`, `-S` / `--addSlug`

---

## Examples

### Create table migration

```bash
php artisan modularous:make:migration create_posts_table Blog \
    --fields="title:string,body:text,published_at:timestamp:nullable"
```

### Add column to existing table

```bash
php artisan modularous:make:migration add_status_to_posts_table Blog \
    --fields="status:string"
```

### Pivot table (BelongsToMany)

```bash
php artisan modularous:make:migration create_blog_post_tag_table Blog \
    --relational=BelongsToMany \
    --route=post \
    --fields="tag_id:unsignedBigInteger"
```

### Morph pivot table

```bash
php artisan modularous:make:migration create_taggables_table Blog \
    --relational=MorphedByMany
```

### Migration with translation table

```bash
php artisan modularous:make:migration create_posts_table Blog \
    --fields="title:string,body:text" \
    --addTranslation
```

### Plain migration (empty up/down)

```bash
php artisan modularous:make:migration add_indexes_to_posts_table Blog --plain
```

---

## Migration naming conventions

| Name pattern | Migration type |
|-------------|---------------|
| `create_*_table` | `Schema::create()` |
| `add_*_to_*_table` | `$table->addColumn()` |
| `delete_*_from_*_table` | `$table->dropColumn()` |
| `drop_*_table` | `Schema::drop()` |
| anything else | plain |

---

## See also

- [make:model](./model) — generate the matching model
- [make:route](./route) — generates model + migration together
- [System Reference](/guide/console/make/overview)

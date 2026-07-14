---
sidebarPos: 17
sidebarTitle: make:repository:trait
---

# make:repository:trait

> Create a reusable repository trait

**Signature**: `modularous:make:repository:trait`

**Aliases**: `modularous:create:repository:trait`, `mod:c:repo:trait`

**Category**: Make

---

## Description

Creates a skeleton PHP trait in the Modularous vendor `src/Repositories/Traits/` directory. The file is named `{Name}Trait.php`.

---

## Usage

```
modularous:make:repository:trait <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Trait name (studly-cased; `Trait` suffix added automatically in filename) |

---

## Examples

```bash
php artisan modularous:make:repository:trait HasTagging
# → src/Repositories/Traits/HasTaggingTrait.php
```

```bash
php artisan modularous:make:repository:trait Filterable
# → src/Repositories/Traits/FilterableTrait.php
```

---

## Output

`src/Repositories/Traits/{Name}Trait.php`

**Stub**: `classes/repository-trait.stub`

---

## Notes

- Writes to the Modularous **vendor** path. Register the trait in `config/modularous.php` to make it available in `make:repository`.

---

## See also

- [make:model:trait](./model-trait) — create an entity trait
- [make:feature](./feature) — wizard that can call this command
- [System Reference](/guide/console/make/overview)

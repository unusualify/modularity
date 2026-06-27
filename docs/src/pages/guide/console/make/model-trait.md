---
sidebarPos: 16
sidebarTitle: make:model:trait
---

# make:model:trait

> Create a reusable entity trait

**Signature**: `modularous:make:model:trait`

**Aliases**: `modularous:create:model:trait`, `mod:c:model:trait`

**Category**: Make

---

## Description

Creates a skeleton PHP trait in the Modularous vendor `src/Entities/Traits/` directory. The file is automatically named `Has{Name}.php`, following the Modularous entity trait naming convention.

---

## Usage

```
modularous:make:model:trait <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Trait name (the `Has` prefix is added automatically) |

---

## Examples

```bash
php artisan modularous:make:model:trait Taggable
# → src/Entities/Traits/HasTaggable.php
```

```bash
php artisan modularous:make:model:trait PriceRange
# → src/Entities/Traits/HasPriceRange.php
```

---

## Output

`src/Entities/Traits/Has{Name}.php`

**Stub**: `classes/model-trait.stub`

---

## Notes

- This command writes to the Modularous **vendor** path, not a module. Use it when creating reusable traits that span multiple modules.
- The trait is not registered anywhere automatically — add it to `config/modularous.php` traits list to make it available in `make:model`.

---

## See also

- [make:repository:trait](./repository-trait) — create a repository trait
- [make:feature](./feature) — wizard that can call this command as part of a bundle
- [System Reference](/guide/console/make/overview)

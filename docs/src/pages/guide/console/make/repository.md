---
sidebarPos: 6
sidebarTitle: make:repository
---

# make:repository

> Create a repository class for a module

**Signature**: `modularous:make:repository`

**Category**: Make

---

## Description

Generates a repository class for a module, pre-wired to the matching Eloquent model. Interactively asks which Modularous repository traits to compose (e.g. `HasTranslation`, `HasMedia`) unless `--notAsk` or `--all` is passed. Use `--custom-model` to bind the repository to any existing model class.

---

## Usage

```
modularous:make:repository [options] <repository> <module>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `repository` | yes | Repository class name (e.g. `Post`, `ProductVariant`) |
| `module` | yes | Target module |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--custom-model=` | | Fully-qualified model class to bind instead of auto-resolved |
| `--force` | `-f` | Overwrite existing files |
| `--notAsk` | | Skip interactive trait questions |
| `--all` | | Accept all trait questions |

---

## Examples

### Basic repository

```bash
php artisan modularous:make:repository Post Blog
```

### Repository bound to a custom model

```bash
php artisan modularous:make:repository Order Shop \
    --custom-model="App\Models\Order"
```

### Non-interactive with all traits

```bash
php artisan modularous:make:repository Post Blog --all --notAsk
```

---

## Output

`{Module}/Repositories/PostRepository.php`

---

## See also

- [make:model](./model) — create the matching model
- [make:repository:trait](./repository-trait) — create a standalone repository trait
- [System Reference](/guide/console/make/overview)

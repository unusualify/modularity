---
sidebarPos: 20
sidebarTitle: make:theme:folder
---

# make:theme:folder

> Scaffold a custom theme working folder

**Signature**: `modularous:make:theme:folder`

**Alias**: `modularous:create:theme`

**Category**: Make

---

## Description

Creates a new theme working directory at `resources/vendor/modularous/themes/{name}/` by copying the Sass and JS files from an existing built-in theme. The copied files serve as a starting point; edit them freely before promoting the theme with [`make:theme`](./theme).

---

## Usage

```
modularous:make:theme:folder [options] <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | New theme name (used as folder name) |

### Options

| Option | Description |
|--------|-------------|
| `--extend=` | Built-in theme to copy as base. Prompts with a select list if omitted or invalid. |
| `--force` | Overwrite existing files |

---

## Examples

### Interactive (prompts for base theme)

```bash
php artisan modularous:make:theme:folder mytheme
```

### With explicit base theme

```bash
php artisan modularous:make:theme:folder mytheme --extend=unusualify
```

---

## Output

```
resources/vendor/modularous/themes/mytheme/
├── sass/       (copied from built-in theme)
└── mytheme.js  (copied from built-in theme)
```

---

## Workflow

1. Run `make:theme:folder` to scaffold the working directory
2. Edit `resources/vendor/modularous/themes/{name}/` to customize
3. Run [`make:theme`](./theme) to promote the custom theme to the built-in set

---

## See also

- [make:theme](./theme) — promote a custom theme to built-in
- [System Reference](/guide/console/make/overview)

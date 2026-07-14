---
sidebarPos: 15
sidebarTitle: make:command
---

# make:command

> Scaffold a new Artisan command class

**Signature**: `modularous:make:command`

**Aliases**: `modularous:create:command`, `mod:c:cmd`

**Category**: Make

---

## Description

Creates a new Artisan command class inside `src/Console/` of the Modularous vendor path. The signature is automatically prefixed with `modularous:`, tab and newline escape sequences (`\t`, `\n`) in the signature string are converted to real whitespace.

---

## Usage

```
modularous:make:command [options] <name> <signature>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Class name (studly-cased, `Command` suffix added automatically) |
| `signature` | yes | Artisan signature string (without `modularous:` prefix) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--description=` | `-d` | Command description string |

---

## Examples

### Minimal command

```bash
php artisan modularous:make:command SyncThemes sync:themes
# → src/Console/SyncThemesCommand.php with signature: modularous:sync:themes
```

### Command with arguments and description

```bash
php artisan modularous:make:command ImportData \
    "import:data {source : The data source path}" \
    --description="Import data from a source file"
```

---

## Output

`src/Console/{Name}Command.php`

**Stub**: `scaffold/command.stub`

---

## See also

- [System Reference](/guide/console/make/overview)

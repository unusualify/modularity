# `Create Theme`

> Create custom theme folder.

## Command Information

- **Signature:** `modularity:create:theme [--extend [EXTEND]] [-f|--force] [--] <name>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:theme NAME
```

### With Options

```bash
php artisan modularity:create:theme --extend=EXTEND
```

```bash
# Using shortcut
php artisan modularity:create:theme -f

# Using full option name
php artisan modularity:create:theme --force
```

### Common Combinations

```bash
php artisan modularity:create:theme NAME
```

`modularity:create:theme`
-------------------------

Create custom theme folder.

### Usage

* `modularity:create:theme [--extend [EXTEND]] [-f|--force] [--] <name>`

Create custom theme folder.

### Arguments

#### `name`

The name of theme to be created.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--extend`

The custom extendable theme name.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--force|-f`

Force the operation to run when the route files already exist.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--env`

The environment the command should run under

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`
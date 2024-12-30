# `Make Theme`

> Generalize a theme.

## Command Information

- **Signature:** `modularity:make:theme [-f|--force] [--] <name>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:theme NAME
```

### With Options

```bash
# Using shortcut
php artisan modularity:make:theme -f

# Using full option name
php artisan modularity:make:theme --force
```

### Common Combinations

```bash
php artisan modularity:make:theme NAME
```

`modularity:make:theme`
-----------------------

Generalize a theme.

### Usage

* `modularity:make:theme [-f|--force] [--] <name>`

Generalize a theme.

### Arguments

#### `name`

The name of custom theme to be generalized.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

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
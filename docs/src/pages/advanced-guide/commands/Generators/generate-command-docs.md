# `Generate Command Docs`

> Extract Laravel Console Documentation

## Command Information

- **Signature:** `modularity:generate:command:docs [--output [OUTPUT]] [-f|--force]`
- **Category:** Generators


## Examples

### Basic Usage

```bash
php artisan modularity:generate:command:docs
```

### With Options

```bash
php artisan modularity:generate:command:docs --output=OUTPUT
```

```bash
# Using shortcut
php artisan modularity:generate:command:docs -f

# Using full option name
php artisan modularity:generate:command:docs --force
```


`modularity:generate:command:docs`
----------------------------------

Extract Laravel Console Documentation

### Usage

* `modularity:generate:command:docs [--output [OUTPUT]] [-f|--force]`

Extract Laravel Console Documentation

### Options

#### `--output`

Output directory for markdown files

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--force|-f`

Force overwrite existing files

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
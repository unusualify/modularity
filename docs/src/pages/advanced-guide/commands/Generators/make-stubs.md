# `Make Stubs`

> Create stub files for route.

## Command Information

- **Signature:** `modularity:make:stubs [--only [ONLY]] [--except [EXCEPT]] [-f|--force] [--fix] [--] <module> <route>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:stubs MODULE ROUTE
```

### With Options

```bash
php artisan modularity:make:stubs --only=ONLY
```

```bash
php artisan modularity:make:stubs --except=EXCEPT
```

```bash
# Using shortcut
php artisan modularity:make:stubs -f

# Using full option name
php artisan modularity:make:stubs --force
```

```bash
php artisan modularity:make:stubs --fix
```

### Common Combinations

```bash
php artisan modularity:make:stubs MODULE
```

`modularity:make:stubs`
-----------------------

Create stub files for route.

### Usage

* `modularity:make:stubs [--only [ONLY]] [--except [EXCEPT]] [-f|--force] [--fix] [--] <module> <route>`

Create stub files for route.

### Arguments

#### `module`

The name of module will be used.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `route`

The name of the route.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--only`

get only stubs

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--except`

get except stubs

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

#### `--fix`

Fixes the model config errors

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
# `Create Vue Test`

> Create a test file for vue features or components

## Command Information

- **Signature:** `modularity:create:vue:test [--importDir] [-F|--force] [--] [<name> [<type>]]`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:vue:test NAME TYPE
```

### With Options

```bash
php artisan modularity:create:vue:test --importDir
```

```bash
# Using shortcut
php artisan modularity:create:vue:test -F

# Using full option name
php artisan modularity:create:vue:test --force
```

### Common Combinations

```bash
php artisan modularity:create:vue:test NAME
```

`modularity:create:vue:test`
----------------------------

Create a test file for vue features or components

### Usage

* `modularity:create:vue:test [--importDir] [-F|--force] [--] [<name> [<type>]]`
* `mod:c:vue:test`

Create a test file for vue features or components

### Arguments

#### `name`

The name of test will be used.

* Is required: no
* Is array: no
* Default: `NULL`

#### `type`

The type of test.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--importDir`

The subfolder for importing.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--force|-F`

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
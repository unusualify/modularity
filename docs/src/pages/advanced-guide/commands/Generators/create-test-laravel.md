# `Create Test Laravel`

> Create a test file for laravel features or components

## Command Information

- **Signature:** `modularity:create:test:laravel [--unit] [--] <module> <test>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:test:laravel MODULE TEST
```

### With Options

```bash
php artisan modularity:create:test:laravel --unit
```

### Common Combinations

```bash
php artisan modularity:create:test:laravel MODULE
```

`modularity:create:test:laravel`
--------------------------------

Create a test file for laravel features or components

### Usage

* `modularity:create:test:laravel [--unit] [--] <module> <test>`

Create a test file for laravel features or components

### Arguments

#### `module`

* Is required: yes
* Is array: no
* Default: `NULL`

#### `test`

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--unit`

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
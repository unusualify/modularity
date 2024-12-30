# `Make Controller Front`

> Create Front Controller with repository for specified module.

## Command Information

- **Signature:** `modularity:make:controller:front [--example [EXAMPLE]] [--] <module> <name>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:controller:front MODULE NAME
```

### With Options

```bash
php artisan modularity:make:controller:front --example=EXAMPLE
```

### Common Combinations

```bash
php artisan modularity:make:controller:front MODULE
```

`modularity:make:controller:front`
----------------------------------

Create Front Controller with repository for specified module.

### Usage

* `modularity:make:controller:front [--example [EXAMPLE]] [--] <module> <name>`

Create Front Controller with repository for specified module.

### Arguments

#### `module`

The name of module will be used.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `name`

The name of the controller class.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--example`

An example option.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

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
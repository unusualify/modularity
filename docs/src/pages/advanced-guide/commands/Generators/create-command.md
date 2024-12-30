# `Create Command`

> Create a new console command

## Command Information

- **Signature:** `modularity:create:command [-d|--description [DESCRIPTION]] [--] <name> <signature>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:command NAME SIGNATURE
```

### With Options

```bash
# Using shortcut
php artisan modularity:create:command -d DESCRIPTION

# Using full option name
php artisan modularity:create:command --description=DESCRIPTION
```

### Common Combinations

```bash
php artisan modularity:create:command NAME
```

`modularity:create:command`
---------------------------

Create a new console command

### Usage

* `modularity:create:command [-d|--description [DESCRIPTION]] [--] <name> <signature>`
* `mod:c:cmd`

Create a new console command

### Arguments

#### `name`

* Is required: yes
* Is array: no
* Default: `NULL`

#### `signature`

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--description|-d`

The description of the command

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
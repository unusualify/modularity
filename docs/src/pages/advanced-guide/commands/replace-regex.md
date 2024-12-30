# `Replace Regex`

> Replace matches

## Command Information

- **Signature:** `modularity:replace:regex [-d|--directory [DIRECTORY]] [-p|--pretend] [--] <path> <pattern> <data>`
- **Category:** Other


## Examples

### With Arguments

```bash
php artisan modularity:replace:regex PATH PATTERN DATA
```

### With Options

```bash
# Using shortcut
php artisan modularity:replace:regex -d DIRECTORY

# Using full option name
php artisan modularity:replace:regex --directory=DIRECTORY
```

```bash
# Using shortcut
php artisan modularity:replace:regex -p

# Using full option name
php artisan modularity:replace:regex --pretend
```

### Common Combinations

```bash
php artisan modularity:replace:regex PATH
```

`modularity:replace:regex`
--------------------------

Replace matches

### Usage

* `modularity:replace:regex [-d|--directory [DIRECTORY]] [-p|--pretend] [--] <path> <pattern> <data>`
* `mod:replace:regex`

Replace matches

### Arguments

#### `path`

The path to the files

* Is required: yes
* Is array: no
* Default: `NULL`

#### `pattern`

The pattern to replace

* Is required: yes
* Is array: no
* Default: `NULL`

#### `data`

The data to replace

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--directory|-d`

The directory pattern

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--pretend|-p`

Dump files that would be modified

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
# `Get Version`

> Get Version of a Package

## Command Information

- **Signature:** `modularity:get:version [-p|--package [PACKAGE]]`
- **Category:** Other


## Examples

### Basic Usage

```bash
php artisan modularity:get:version
```

### With Options

```bash
# Using shortcut
php artisan modularity:get:version -p PACKAGE

# Using full option name
php artisan modularity:get:version --package=PACKAGE
```


`modularity:get:version`
------------------------

Get Version of a Package

### Usage

* `modularity:get:version [-p|--package [PACKAGE]]`
* `mod:g:ver`

Get Version of a Package

### Options

#### `--package|-p`

The package

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
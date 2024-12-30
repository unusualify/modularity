# `Composer Merge`

> Add merge-plugin require pattern for composer-merge-plugin package

## Command Information

- **Signature:** `modularity:composer:merge [-p|--production]`
- **Category:** Composer


## Examples

### Basic Usage

```bash
php artisan modularity:composer:merge
```

### With Options

```bash
# Using shortcut
php artisan modularity:composer:merge -p

# Using full option name
php artisan modularity:composer:merge --production
```


`modularity:composer:merge`
---------------------------

Add merge-plugin require pattern for composer-merge-plugin package

### Usage

* `modularity:composer:merge [-p|--production]`

Add merge-plugin require pattern for composer-merge-plugin package

### Options

#### `--production|-p`

Update Production composer.json file

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
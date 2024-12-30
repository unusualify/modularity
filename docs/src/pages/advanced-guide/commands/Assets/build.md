# `Build`

> Build the Modularity assets with custom Vue components

## Command Information

- **Signature:** `modularity:build [--noInstall] [--hot] [-w|--watch] [-c|--copyOnly] [-cc|--copyComponents] [-ct|--copyTheme] [-cts|--copyThemeScript] [--theme [THEME]]`
- **Category:** Assets


## Examples

### Basic Usage

```bash
php artisan modularity:build
```

### With Options

```bash
php artisan modularity:build --noInstall
```

```bash
php artisan modularity:build --hot
```

```bash
# Using shortcut
php artisan modularity:build -w

# Using full option name
php artisan modularity:build --watch
```

```bash
# Using shortcut
php artisan modularity:build -c

# Using full option name
php artisan modularity:build --copyOnly
```

```bash
# Using shortcut
php artisan modularity:build -cc

# Using full option name
php artisan modularity:build --copyComponents
```

```bash
# Using shortcut
php artisan modularity:build -ct

# Using full option name
php artisan modularity:build --copyTheme
```

```bash
# Using shortcut
php artisan modularity:build -cts

# Using full option name
php artisan modularity:build --copyThemeScript
```

```bash
php artisan modularity:build --theme=THEME
```


`modularity:build`
------------------

Build the Modularity assets with custom Vue components

### Usage

* `modularity:build [--noInstall] [--hot] [-w|--watch] [-c|--copyOnly] [-cc|--copyComponents] [-ct|--copyTheme] [-cts|--copyThemeScript] [--theme [THEME]]`
* `unusual:build`

Build the Modularity assets with custom Vue components

### Options

#### `--noInstall`

No install npm packages

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--hot`

Hot Reload

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--watch|-w`

Watcher for dev

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--copyOnly|-c`

Only copy assets

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--copyComponents|-cc`

Only copy custom components

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--copyTheme|-ct`

Only copy custom theme

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--copyThemeScript|-cts`

Only copy custom theme script

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--theme`

Custom theme name if was worked on

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
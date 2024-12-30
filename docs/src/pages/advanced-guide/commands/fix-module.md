# `Fix Module`

> Fixes the un-desired changes on module's config file

## Command Information

- **Signature:** `modularity:fix:module [--migration] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> [<route>]`
- **Category:** Other


## Examples

### With Arguments

```bash
php artisan modularity:fix:module MODULE ROUTE
```

### With Options

```bash
php artisan modularity:fix:module --migration
```

```bash
# Using shortcut
php artisan modularity:fix:module -T

# Using full option name
php artisan modularity:fix:module --addTranslation
```

```bash
# Using shortcut
php artisan modularity:fix:module -M

# Using full option name
php artisan modularity:fix:module --addMedia
```

```bash
# Using shortcut
php artisan modularity:fix:module -F

# Using full option name
php artisan modularity:fix:module --addFile
```

```bash
# Using shortcut
php artisan modularity:fix:module -P

# Using full option name
php artisan modularity:fix:module --addPosition
```

```bash
# Using shortcut
php artisan modularity:fix:module -S

# Using full option name
php artisan modularity:fix:module --addSlug
```

```bash
php artisan modularity:fix:module --addPrice
```

```bash
# Using shortcut
php artisan modularity:fix:module -A

# Using full option name
php artisan modularity:fix:module --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:fix:module -FP

# Using full option name
php artisan modularity:fix:module --addFilepond
```

```bash
php artisan modularity:fix:module --addUuid
```

```bash
# Using shortcut
php artisan modularity:fix:module -SS

# Using full option name
php artisan modularity:fix:module --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:fix:module MODULE
```

`modularity:fix:module`
-----------------------

Fixes the un-desired changes on module's config file

### Usage

* `modularity:fix:module [--migration] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> [<route>]`

Fixes the un-desired changes on module's config file

### Arguments

#### `module`

The name of module will be used.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `route`

The name of the route.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--migration`

Fix will create migrations

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addTranslation|-T`

Whether model has translation trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addMedia|-M`

Do you need to attach images on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addFile|-F`

Do you need to attach files on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addPosition|-P`

Do you need to manage the position of records on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addSlug|-S`

Whether model has sluggable trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addPrice`

Whether model has pricing trait or not

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addAuthorized|-A`

Authorized models to indicate scopes

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addFilepond|-FP`

Do you need to attach fileponds on this module?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addUuid`

Do you need to attach uuid on this module route?

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--addSnapshot|-SS`

Do you need to attach snapshot feature on this module route?

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
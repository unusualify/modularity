# `Install`

> Install unusual-modularity into your Laravel application

## Command Information

- **Signature:** `modularity:install [-d|--default] [-db|--db-process] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot]`
- **Category:** Setup


## Examples

### Basic Usage

```bash
php artisan modularity:install
```

### With Options

```bash
# Using shortcut
php artisan modularity:install -d

# Using full option name
php artisan modularity:install --default
```

```bash
# Using shortcut
php artisan modularity:install -db

# Using full option name
php artisan modularity:install --db-process
```

```bash
# Using shortcut
php artisan modularity:install -T

# Using full option name
php artisan modularity:install --addTranslation
```

```bash
# Using shortcut
php artisan modularity:install -M

# Using full option name
php artisan modularity:install --addMedia
```

```bash
# Using shortcut
php artisan modularity:install -F

# Using full option name
php artisan modularity:install --addFile
```

```bash
# Using shortcut
php artisan modularity:install -P

# Using full option name
php artisan modularity:install --addPosition
```

```bash
# Using shortcut
php artisan modularity:install -S

# Using full option name
php artisan modularity:install --addSlug
```

```bash
php artisan modularity:install --addPrice
```

```bash
# Using shortcut
php artisan modularity:install -A

# Using full option name
php artisan modularity:install --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:install -FP

# Using full option name
php artisan modularity:install --addFilepond
```

```bash
php artisan modularity:install --addUuid
```

```bash
# Using shortcut
php artisan modularity:install -SS

# Using full option name
php artisan modularity:install --addSnapshot
```


`modularity:install`
--------------------

Install unusual-modularity into your Laravel application

### Usage

* `modularity:install [-d|--default] [-db|--db-process] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot]`

Install unusual-modularity into your Laravel application

### Options

#### `--default|-d`

Use default options for super-admin authentication configuration

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--db-process|-db`

Only handle database configuration processes

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
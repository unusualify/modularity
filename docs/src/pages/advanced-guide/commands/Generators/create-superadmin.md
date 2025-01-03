# `Create Superadmin`

> Creates the superadmin account

## Command Information

- **Signature:** `modularity:create:superadmin [-d|--default] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] [<email> [<password>]]`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:superadmin EMAIL PASSWORD
```

### With Options

```bash
# Using shortcut
php artisan modularity:create:superadmin -d

# Using full option name
php artisan modularity:create:superadmin --default
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -T

# Using full option name
php artisan modularity:create:superadmin --addTranslation
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -M

# Using full option name
php artisan modularity:create:superadmin --addMedia
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -F

# Using full option name
php artisan modularity:create:superadmin --addFile
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -P

# Using full option name
php artisan modularity:create:superadmin --addPosition
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -S

# Using full option name
php artisan modularity:create:superadmin --addSlug
```

```bash
php artisan modularity:create:superadmin --addPrice
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -A

# Using full option name
php artisan modularity:create:superadmin --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -FP

# Using full option name
php artisan modularity:create:superadmin --addFilepond
```

```bash
php artisan modularity:create:superadmin --addUuid
```

```bash
# Using shortcut
php artisan modularity:create:superadmin -SS

# Using full option name
php artisan modularity:create:superadmin --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:create:superadmin EMAIL
```

`modularity:create:superadmin`
------------------------------

Creates the superadmin account

### Usage

* `modularity:create:superadmin [-d|--default] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] [<email> [<password>]]`

Creates the superadmin account

### Arguments

#### `email`

A valid e-mail for super-admin

* Is required: no
* Is array: no
* Default: `NULL`

#### `password`

A valid password for super-admin

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--default|-d`

Use default options for super-admin auth. information

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
# `Make Repository`

> Create a new repository class for the specified module.

## Command Information

- **Signature:** `modularity:make:repository [-f|--force] [--custom-model [CUSTOM-MODEL]] [--notAsk] [--all] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> <repository>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:repository MODULE REPOSITORY
```

### With Options

```bash
# Using shortcut
php artisan modularity:make:repository -f

# Using full option name
php artisan modularity:make:repository --force
```

```bash
php artisan modularity:make:repository --custom-model=CUSTOM-MODEL
```

```bash
php artisan modularity:make:repository --notAsk
```

```bash
php artisan modularity:make:repository --all
```

```bash
# Using shortcut
php artisan modularity:make:repository -T

# Using full option name
php artisan modularity:make:repository --addTranslation
```

```bash
# Using shortcut
php artisan modularity:make:repository -M

# Using full option name
php artisan modularity:make:repository --addMedia
```

```bash
# Using shortcut
php artisan modularity:make:repository -F

# Using full option name
php artisan modularity:make:repository --addFile
```

```bash
# Using shortcut
php artisan modularity:make:repository -P

# Using full option name
php artisan modularity:make:repository --addPosition
```

```bash
# Using shortcut
php artisan modularity:make:repository -S

# Using full option name
php artisan modularity:make:repository --addSlug
```

```bash
php artisan modularity:make:repository --addPrice
```

```bash
# Using shortcut
php artisan modularity:make:repository -A

# Using full option name
php artisan modularity:make:repository --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:make:repository -FP

# Using full option name
php artisan modularity:make:repository --addFilepond
```

```bash
php artisan modularity:make:repository --addUuid
```

```bash
# Using shortcut
php artisan modularity:make:repository -SS

# Using full option name
php artisan modularity:make:repository --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:make:repository MODULE
```

`modularity:make:repository`
----------------------------

Create a new repository class for the specified module.

### Usage

* `modularity:make:repository [-f|--force] [--custom-model [CUSTOM-MODEL]] [--notAsk] [--all] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> <repository>`

Create a new repository class for the specified module.

### Arguments

#### `module`

The name of module will be used.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `repository`

The name of the repository class.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--force|-f`

Force the operation to run when the route files already exist.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--custom-model`

The model class for usage of a available model.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--notAsk`

don't ask for trait questions.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--all`

add all traits.

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
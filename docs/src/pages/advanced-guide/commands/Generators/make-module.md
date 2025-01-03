# `Make Module`

> Create a module

## Command Information

- **Signature:** `modularity:make:module [--schema [SCHEMA]] [--rules [RULES]] [--relationships [RELATIONSHIPS]] [-f|--force] [--no-migrate] [--no-defaults] [--no-migration] [--custom-model [CUSTOM-MODEL]] [--table-name [TABLE-NAME]] [--notAsk] [--all] [--just-stubs] [--stubs-only [STUBS-ONLY]] [--stubs-except [STUBS-EXCEPT]] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:module MODULE
```

### With Options

```bash
php artisan modularity:make:module --schema=SCHEMA
```

```bash
php artisan modularity:make:module --rules=RULES
```

```bash
php artisan modularity:make:module --relationships=RELATIONSHIPS
```

```bash
# Using shortcut
php artisan modularity:make:module -f

# Using full option name
php artisan modularity:make:module --force
```

```bash
php artisan modularity:make:module --no-migrate
```

```bash
php artisan modularity:make:module --no-defaults
```

```bash
php artisan modularity:make:module --no-migration
```

```bash
php artisan modularity:make:module --custom-model=CUSTOM-MODEL
```

```bash
php artisan modularity:make:module --table-name=TABLE-NAME
```

```bash
php artisan modularity:make:module --notAsk
```

```bash
php artisan modularity:make:module --all
```

```bash
php artisan modularity:make:module --just-stubs
```

```bash
php artisan modularity:make:module --stubs-only=STUBS-ONLY
```

```bash
php artisan modularity:make:module --stubs-except=STUBS-EXCEPT
```

```bash
# Using shortcut
php artisan modularity:make:module -T

# Using full option name
php artisan modularity:make:module --addTranslation
```

```bash
# Using shortcut
php artisan modularity:make:module -M

# Using full option name
php artisan modularity:make:module --addMedia
```

```bash
# Using shortcut
php artisan modularity:make:module -F

# Using full option name
php artisan modularity:make:module --addFile
```

```bash
# Using shortcut
php artisan modularity:make:module -P

# Using full option name
php artisan modularity:make:module --addPosition
```

```bash
# Using shortcut
php artisan modularity:make:module -S

# Using full option name
php artisan modularity:make:module --addSlug
```

```bash
php artisan modularity:make:module --addPrice
```

```bash
# Using shortcut
php artisan modularity:make:module -A

# Using full option name
php artisan modularity:make:module --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:make:module -FP

# Using full option name
php artisan modularity:make:module --addFilepond
```

```bash
php artisan modularity:make:module --addUuid
```

```bash
# Using shortcut
php artisan modularity:make:module -SS

# Using full option name
php artisan modularity:make:module --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:make:module MODULE
```

`modularity:make:module`
------------------------

Create a module

### Usage

* `modularity:make:module [--schema [SCHEMA]] [--rules [RULES]] [--relationships [RELATIONSHIPS]] [-f|--force] [--no-migrate] [--no-defaults] [--no-migration] [--custom-model [CUSTOM-MODEL]] [--table-name [TABLE-NAME]] [--notAsk] [--all] [--just-stubs] [--stubs-only [STUBS-ONLY]] [--stubs-except [STUBS-EXCEPT]] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module>`
* `m:m:m`
* `unusual:make:module`

Create a module

### Arguments

#### `module`

The name of the module.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--schema`

The specified migration schema table.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--rules`

The specified validation rules for FormRequest.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--relationships`

The many to many relationships.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--force|-f`

Force the operation to run when the route files already exist.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--no-migrate`

don't migrate.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--no-defaults`

unuse default input and headers.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--no-migration`

don't create migration file.

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

#### `--table-name`

Sets table  name for custom model

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

#### `--just-stubs`

only stubs fix

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--stubs-only`

Get only stubs

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--stubs-except`

Get except stubs

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

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
# `Make Route`

> Create files for routes.

## Command Information

- **Signature:** `modularity:make:route [--schema [SCHEMA]] [--rules [RULES]] [--custom-model [CUSTOM-MODEL]] [--relationships [RELATIONSHIPS]] [-f|--force] [-p|--plain] [--notAsk] [--all] [--no-migrate] [--no-defaults] [--fix] [--table-name [TABLE-NAME]] [--no-migration] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> <route>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:route MODULE ROUTE
```

### With Options

```bash
php artisan modularity:make:route --schema=SCHEMA
```

```bash
php artisan modularity:make:route --rules=RULES
```

```bash
php artisan modularity:make:route --custom-model=CUSTOM-MODEL
```

```bash
php artisan modularity:make:route --relationships=RELATIONSHIPS
```

```bash
# Using shortcut
php artisan modularity:make:route -f

# Using full option name
php artisan modularity:make:route --force
```

```bash
# Using shortcut
php artisan modularity:make:route -p

# Using full option name
php artisan modularity:make:route --plain
```

```bash
php artisan modularity:make:route --notAsk
```

```bash
php artisan modularity:make:route --all
```

```bash
php artisan modularity:make:route --no-migrate
```

```bash
php artisan modularity:make:route --no-defaults
```

```bash
php artisan modularity:make:route --fix
```

```bash
php artisan modularity:make:route --table-name=TABLE-NAME
```

```bash
php artisan modularity:make:route --no-migration
```

```bash
php artisan modularity:make:route --test
```

```bash
# Using shortcut
php artisan modularity:make:route -T

# Using full option name
php artisan modularity:make:route --addTranslation
```

```bash
# Using shortcut
php artisan modularity:make:route -M

# Using full option name
php artisan modularity:make:route --addMedia
```

```bash
# Using shortcut
php artisan modularity:make:route -F

# Using full option name
php artisan modularity:make:route --addFile
```

```bash
# Using shortcut
php artisan modularity:make:route -P

# Using full option name
php artisan modularity:make:route --addPosition
```

```bash
# Using shortcut
php artisan modularity:make:route -S

# Using full option name
php artisan modularity:make:route --addSlug
```

```bash
php artisan modularity:make:route --addPrice
```

```bash
# Using shortcut
php artisan modularity:make:route -A

# Using full option name
php artisan modularity:make:route --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:make:route -FP

# Using full option name
php artisan modularity:make:route --addFilepond
```

```bash
php artisan modularity:make:route --addUuid
```

```bash
# Using shortcut
php artisan modularity:make:route -SS

# Using full option name
php artisan modularity:make:route --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:make:route MODULE
```

`modularity:make:route`
-----------------------

Create files for routes.

### Usage

* `modularity:make:route [--schema [SCHEMA]] [--rules [RULES]] [--custom-model [CUSTOM-MODEL]] [--relationships [RELATIONSHIPS]] [-f|--force] [-p|--plain] [--notAsk] [--all] [--no-migrate] [--no-defaults] [--fix] [--table-name [TABLE-NAME]] [--no-migration] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <module> <route>`
* `m:m:r`
* `u:m:r`
* `unusual:make:route`

Create files for routes.

### Arguments

#### `module`

The name of module will be used.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `route`

The name of the route.

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

#### `--custom-model`

The model class for usage of a available model.

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

#### `--plain|-p`

Don't create route.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

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

#### `--fix`

Fixes the model config errors

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--table-name`

Sets table  name for custom model

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--no-migration`

don't create migration file.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--test`

Test the Route Generator

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
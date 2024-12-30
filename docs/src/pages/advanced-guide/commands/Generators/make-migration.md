# `Make Migration`

> Create a new migration for the specified module.

## Command Information

- **Signature:** `modularity:make:migration [--fields [FIELDS]] [--route [ROUTE]] [--plain] [-f|--force] [--relational] [--notAsk] [--no-defaults] [--all] [--table-name [TABLE-NAME]] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <name> [<module>]`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:migration NAME MODULE
```

### With Options

```bash
php artisan modularity:make:migration --fields=FIELDS
```

```bash
php artisan modularity:make:migration --route=ROUTE
```

```bash
php artisan modularity:make:migration --plain
```

```bash
# Using shortcut
php artisan modularity:make:migration -f

# Using full option name
php artisan modularity:make:migration --force
```

```bash
php artisan modularity:make:migration --relational
```

```bash
php artisan modularity:make:migration --notAsk
```

```bash
php artisan modularity:make:migration --no-defaults
```

```bash
php artisan modularity:make:migration --all
```

```bash
php artisan modularity:make:migration --table-name=TABLE-NAME
```

```bash
php artisan modularity:make:migration --test
```

```bash
# Using shortcut
php artisan modularity:make:migration -T

# Using full option name
php artisan modularity:make:migration --addTranslation
```

```bash
# Using shortcut
php artisan modularity:make:migration -M

# Using full option name
php artisan modularity:make:migration --addMedia
```

```bash
# Using shortcut
php artisan modularity:make:migration -F

# Using full option name
php artisan modularity:make:migration --addFile
```

```bash
# Using shortcut
php artisan modularity:make:migration -P

# Using full option name
php artisan modularity:make:migration --addPosition
```

```bash
# Using shortcut
php artisan modularity:make:migration -S

# Using full option name
php artisan modularity:make:migration --addSlug
```

```bash
php artisan modularity:make:migration --addPrice
```

```bash
# Using shortcut
php artisan modularity:make:migration -A

# Using full option name
php artisan modularity:make:migration --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:make:migration -FP

# Using full option name
php artisan modularity:make:migration --addFilepond
```

```bash
php artisan modularity:make:migration --addUuid
```

```bash
# Using shortcut
php artisan modularity:make:migration -SS

# Using full option name
php artisan modularity:make:migration --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:make:migration NAME
```

`modularity:make:migration`
---------------------------

Create a new migration for the specified module.

### Usage

* `modularity:make:migration [--fields [FIELDS]] [--route [ROUTE]] [--plain] [-f|--force] [--relational] [--notAsk] [--no-defaults] [--all] [--table-name [TABLE-NAME]] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <name> [<module>]`
* `mod:m:migration`

Create a new migration for the specified module.

### Arguments

#### `name`

The migration name will be created.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `module`

The name of module that the migration will be created in.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--fields`

The specified fields table.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--route`

The route name for pivot table.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--plain`

Create plain migration.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--force|-f`

Force the operation to run when the route files already exist.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--relational`

Create relational table for many-to-many and polymorphic relationships.

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

#### `--no-defaults`

unuse default input and headers.

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

#### `--table-name`

set table name

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

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
# `Make Model`

> Create a new model for the specified module.

## Command Information

- **Signature:** `modularity:make:model [--fillable [FILLABLE]] [--relationships [RELATIONSHIPS]] [--override-model [OVERRIDE-MODEL]] [-f|--force] [--notAsk] [--no-defaults] [-s|--soft-delete] [--has-factory] [--all] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <model> [<module>]`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:make:model MODEL MODULE
```

### With Options

```bash
php artisan modularity:make:model --fillable=FILLABLE
```

```bash
php artisan modularity:make:model --relationships=RELATIONSHIPS
```

```bash
php artisan modularity:make:model --override-model=OVERRIDE-MODEL
```

```bash
# Using shortcut
php artisan modularity:make:model -f

# Using full option name
php artisan modularity:make:model --force
```

```bash
php artisan modularity:make:model --notAsk
```

```bash
php artisan modularity:make:model --no-defaults
```

```bash
# Using shortcut
php artisan modularity:make:model -s

# Using full option name
php artisan modularity:make:model --soft-delete
```

```bash
php artisan modularity:make:model --has-factory
```

```bash
php artisan modularity:make:model --all
```

```bash
php artisan modularity:make:model --test
```

```bash
# Using shortcut
php artisan modularity:make:model -T

# Using full option name
php artisan modularity:make:model --addTranslation
```

```bash
# Using shortcut
php artisan modularity:make:model -M

# Using full option name
php artisan modularity:make:model --addMedia
```

```bash
# Using shortcut
php artisan modularity:make:model -F

# Using full option name
php artisan modularity:make:model --addFile
```

```bash
# Using shortcut
php artisan modularity:make:model -P

# Using full option name
php artisan modularity:make:model --addPosition
```

```bash
# Using shortcut
php artisan modularity:make:model -S

# Using full option name
php artisan modularity:make:model --addSlug
```

```bash
php artisan modularity:make:model --addPrice
```

```bash
# Using shortcut
php artisan modularity:make:model -A

# Using full option name
php artisan modularity:make:model --addAuthorized
```

```bash
# Using shortcut
php artisan modularity:make:model -FP

# Using full option name
php artisan modularity:make:model --addFilepond
```

```bash
php artisan modularity:make:model --addUuid
```

```bash
# Using shortcut
php artisan modularity:make:model -SS

# Using full option name
php artisan modularity:make:model --addSnapshot
```

### Common Combinations

```bash
php artisan modularity:make:model MODEL
```

`modularity:make:model`
-----------------------

Create a new model for the specified module.

### Usage

* `modularity:make:model [--fillable [FILLABLE]] [--relationships [RELATIONSHIPS]] [--override-model [OVERRIDE-MODEL]] [-f|--force] [--notAsk] [--no-defaults] [-s|--soft-delete] [--has-factory] [--all] [--test] [-T|--addTranslation] [-M|--addMedia] [-F|--addFile] [-P|--addPosition] [-S|--addSlug] [--addPrice] [-A|--addAuthorized] [-FP|--addFilepond] [--addUuid] [-SS|--addSnapshot] [--] <model> [<module>]`
* `mod:m:model`

Create a new model for the specified module.

### Arguments

#### `model`

The name of model will be created.

* Is required: yes
* Is array: no
* Default: `NULL`

#### `module`

The name of module will be used.

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--fillable`

The fillable attributes.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--relationships`

The relationship attributes.

* Accept value: yes
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--override-model`

The override model for extension.

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

#### `--soft-delete|-s`

Flag to add softDeletes trait to model.

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--has-factory`

Flag to add hasFactory to model.

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
# `Create Route Permissions`

> Create permissions for routes

## Command Information

- **Signature:** `modularity:create:route:permissions [--route [ROUTE]] [--] <route>`
- **Category:** Generators


## Examples

### With Arguments

```bash
php artisan modularity:create:route:permissions ROUTE
```

### With Options

```bash
php artisan modularity:create:route:permissions --route=ROUTE
```

### Common Combinations

```bash
php artisan modularity:create:route:permissions ROUTE
```

`modularity:create:route:permissions`
-------------------------------------

Create permissions for routes

### Usage

* `modularity:create:route:permissions [--route [ROUTE]] [--] <route>`

Create permissions for routes

### Arguments

#### `route`

The name of the route.

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--route`

The validation rules.

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
---
# https://vitepress.dev/reference/default-theme-home-page
layout: doc

prev:
  text: 'Introduction'
  link: '../creating-modules/introduction'



---
# Creating a Module

Creating a plain module is simple and straightforward.

```sh
$ php artisan unusual:make:module YourModuleName
```
Running this command will create the module with empty module structure with a config.php file where you can configure and customize your module's user interface, CRUD form schema and etc.

::: tip
Creating module and a module options are similar while default option of the creating a module is generating a plain folder structure for the given module name.
:::
::: info
Creating module and route options are similar if default option is not used and parent domain entity is created. Options will be explain under creating route header
:::

**Config File**

Module's config file under Modules/YourModuleName/Config directory is containing the main configuration of your module and routes where you can configure CRUD form inputs, user interface options, icons, urls and etc. Initial config file will be constructed as follows for a plain module generation:

Assume a module named Authentication is created with default plain option
```php
<?php

return [
    'name' => 'Authentication',
    'system_prefix' => false,
    'base_prefix' => false,
    'headline' => 'Authentication',
    'routes' => [
    ],
];

```
where you can configure your modules `headline` presenting on sidebar, whether the module route will be generated with system_prefix or base_prefix. ``Routes`` key will contain all your route configrations generated. Them can be customized in future.

::: tip
Config file can be customized in many ways, see [Module Config](/)
:::
<br/>

**File module.json**


## Creating Routes
Creating a route is highly customizable using command options, simplest way to create a route with default schema and relationship options is:
```sh
$ php artisan unusual:make:route YourModuleName YourRouteName --options*
```
This will automatically create route with its `Controllers` `Entity` `Migration File` `Repository` `Request` `Resource` and also its route files like `web.php` and default ``index`` and ``form`` blade components.
::: tip Customization and Config File
As mentioned, config.php file underneath the module folder can and should be used to customize forms, user interfaces and etc. (See [Module Config]()). You do not need to customize generated files to reach your goals mostly.
:::


::: tip IMPORTANT
This documentation will include brief explanation of the technical information about create route command. For further presentation about Modularity Know-how please see [Examples]()
:::

## Artisan Command Options
<br/>

#### `--schema`
Use this option to define your model's database schema. It will automatically configure your migration files. 
#### `--relationships`
Relationships option should not be confict with migration relationships. Database migrations should be set on the `--schema` option. On the other hand, `--relationship` options will be used to define model relationship methods like `Polymorphic Relationships` where you need a pivot or any other external database table to define relationships. See [Example Page]()
#### `--rules`
Rules options will be used to define CRUD form validations for both backend and front-end validation scripts. 
#### `--no-migrate`
Default route generation automatically runs migrations. You can skip migration with this option.
#### `--force`
Force the operation to run when the route files already exist. Use this option to override the route files with new options.

## Defining Model Schema


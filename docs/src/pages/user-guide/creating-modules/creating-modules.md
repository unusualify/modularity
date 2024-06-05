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
Model schema is where you define your enties' attributes (columns) and these attributes' types and modifiers. Modularity schema builder contains all availiable column types and column modifiers in Laravel Framework

( See [Availiable Laravel Column Types](https://laravel.com/docs/11.x/migrations#available-column-types){target="_self"} -  [Available Laravel Column Modifiers](https://laravel.com/docs/11.x/migrations#column-modifiers){target="_self"} )

::: danger Relationships

Defining relation type attributes are different in Unusualify/Modularity.

:::

### Usage

**Defining a series of attributes**

When defining a series of entity attributes, desired schema should be typed between double quotes `"`, columnTypes should be seperated by colons `:` and lastly attributes should be seperated by commas `,` if exist.

```sh
$ php artisan unusual:make:route ModuleName RouteName --schema="attributeName:columnType#1:columnType#2,attributeName#2:...columnType#:..columnModifiers#"
```
Running this command will generate your model's 
 - `controller`, with source methods
 - `migration` files with defined columns
 - `routes`,
 - `entity` with fillable array,
 - `request` with default methods
 - `repository`
 - `index` and `form` blade components with default configuration
 - also module config file will be overriden with route properties 
  
::: tip Module Config.php
Module config file is where user interface, CRUD form schema and etc. can be customized. Please see [Module Config]()
:::

For an example, assume building a user entity with string name and string, unique email address underneath the Authentication module:
```sh
$ php artisan unusual:make:route Authentication User --schema="name:string,email:string:unique"
```


<br/>

## **Defining relations between routes**

In Laravel migrations, only `foreignId` and `morphs` column types can be used to define relationsips between models. In Modularity, `reverse relationship method names` can be used as an attribute while creating route. 

::: warning Reverse Relations
Since creating route command will automatically create all of the required files and running migrations, it is suggested to follow `reverse relationship` path to define relation between models
:::

**Presentation**

Assume database schema as follows, for a Module `Citizens`, with recorded citizens and their cars. A citizen can have many cars,

```sh
#Module Name : Citizens

citizen
    id - integer
    name - string
    citizen_id - integer (unique)

cars
    id - integer
    model - string
    user_id - integer
```

Following the given example, creating user route:
```sh
$ php artisan unusual:make:route Aparment Citizen --schema="name:string,citizen_id:integer:unique"
```
`Citizen` route is now generated with all required files. Next, we can create `Car` route with `belongsTo` relationship related column(s) and model method(s) with the following artisan command:
```sh
$ php artisan unusual:make:route Aparment Car --schema="model:string,plate:string:unique,citizen:belongsTo"
```
Runnings these couple of commands, will also create relationship related model methods as:
```php

// Citizen.php
public function cars() : \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(\Modules\Testify\Entities\Car::class);
	}

// Car.php
public function citizen(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsto(\Modules\Testify\Entities\Citizen::class, 'citizen_id', 'id')
    }
```

Also migration of the Car route will be generated with the line:
```php
$table->foreignId('testify_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
```


::: tip Relationship Summary
While defining direct relationships that will affect migration and database tables, `--schema` option should be used. On the other hand, with un-direct relations like `many-to-many` and `through` relations you need to use `--relationships` option
:::

### Available Relationship Methods


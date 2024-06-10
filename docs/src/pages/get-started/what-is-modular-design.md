---
sidebarPos: 2

---


# What is Modular Design Architecture?
In summary, `Modular Design` can be defined as an approach to dividing code files into smaller sub-parts and layers by separating and `isolating` them sub-parts from each other. 

## Problem Statement
As the project grows, business logic of `multiple` features tends to affect other code spaces in the project. That might be blocking  co-developers to undergo their tasks, can produce dependency injection problems, code bugs and code-conflicts with making `multiple` different tasks or `features affecting each other`. Lastly, it would increase testing processes, the app built time due to codebase growth. In conclusion, all things considered it would reduce developer’s productivity and production efficiency, `increase development complexity`.

## Modular Design Solution
Dealing with the mentioned problems is possible with making code-space and features `seperated` into layers that will work `independently` from each other as much as possible. In this way, `feature based` development become available and its enables us to making features independent from each other. Consequently, a feature can be built as an project or `re-usable generic package`, code becomes more `SOLID`.

## Benefits of Modular System Design
#### Increasing Code Reusability

When the application is in modular form, a module can be easily imported and transferred to another project. It makes it easier to share common components used in different projects, and to create different applications through a codebase by building certain modules.

#### Feature Based Development

It is the approach of separating the existing features in the application module by module and making the features independent from each other. A change in one feature does not affect another feature. In this way, it is sufficient to run only the tests related to the relevant module. Provided that, features can be transform into `re-usable package` to our code space.

#### Increasing Scability

Applying modular system design and feature based development to the project code base, provides seperating whole project to smaller pieces. That way, developers can apply `Seperation of Concern princible` to the project code-base, thus each piece can be dealt with different developer. With this, each developer will be responsible for just some modules instead of whole project. 

#### Increasing Maintainability

In large - monolithic applications, any change is made in non-modular code-space may require version control of large scaled and too much code files. On the other hand, with using modular architecture, mostly `less code file` will be examined by `observing module or feature related codes`. In this way, the majority of the project is dealt with relatively less code instead of scanning and trying to understand. Detecting the error and solving the bugs becomes easier and the `time is shortened`.

::: info Feature Based Development in Modularity
Using feature based development, `Unusualify/Modularity` provides development packages like
[Laravel-Form](https://github.com/unusualify/laravel-form){target="_self"} and [Pricable](https://github.com/unusualify/priceable){target="_self"} which can be added to any project using composer.
:::

## Module, Model and Route Definition Comparison
The term `Module` refers to the subject area or problem space that the software system is being designed do address. Assume building a E-Commerce application, to operate this type of application, it is necessary to integrate various areas like `Sales`, `Advertisement`,`Customer Management` and so on. The `Module` represents each of these `specific area` of business focus that the software is intented to support.

::: info Module in Laravel
Each module similar to a complete Laravel project. Every module will have its controllers, views, routes, middlewares, and etc. which are belonging to `module's routes`.
:::

On the other hand, `Route` refers to a distinct and identifiable object in covering module. Each route will have its own controller, route(s), entity model, repository, migrations and etc. Consequently, routes constructing the module layer. 

## Module and Routes Example
As mentioned before, each module is a Laravel project that has its own controllers, entities and etc. Following this convention, a module can be constructed with plain folder structure to build on it or with a parent domain that named recursively.

For an example, imagine building a ``Authorization`` module with:
* User
* User Roles
* Roles Permissions

Since authorization will be dealt with the User model itself, and capabilities of a user will be assigned with its role and roles permissions there is no need to have any `Authorization` model in the package. Now, Authorization can be constructed as a plain module structure then mentioned routes are can be constructed in it.
```
├─ Authorization
|    ├─ Config
|        └─ config.php
|    ├─ Database
|        ├─ factories
|        ├─ Migrations
|        ├─ Seeders
|    ├─ Entities
|        ├─ Slugs
|        └─ User.php *
|        └─ Role.php *
|        └─ Permission.php *
|    ├─ Http
|        ├─ Controllers
|        ├─ Middleware
|        ├─ Requests
|    ├─ Providers
|    ├─ Repositories
|    ├─ Resources
|        ├─ assets
|        ├─ lang
|        ├─ views
|    ├─ Routes
|    ├─ Tests
|    ├─ Transformers
|    └─ composer.json
|    └─ module.json
|    └─ routes_statuses.json*
``` 


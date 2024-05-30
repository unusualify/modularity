# Introduction to Modular Design
Modular design or `modularity design` is a design princible that subdivides a system into smaller parts called `modules`. Modular design aims modules to created, modified, replaced or exchanged with other modules or between different systems.

By means of ``Unusualify/Modularity`` a module is similar to a Laravel project, will have its own controllers, entities, migrations and etc. to construct the module. 

Basically a module's structure can be presented as:
```
├─ Testify
|    ├─ Config
|        └─ config.php
|    ├─ Database
|        ├─ factories
|        ├─ Migrations
|        ├─ Seeders
|    ├─ Entities
|        ├─ Slugs
|        └─ *.php (Entities)
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

## Module and Routes Definitions 
As mentioned before, each module is a Laravel project that has its own controllers, entities and etc. Following this convention, a module can be constructed with plain folder structure to build on it or with a parent domain that named recursively.

For an example,

Imagine building a authentication module with user roles and roles permissions
 

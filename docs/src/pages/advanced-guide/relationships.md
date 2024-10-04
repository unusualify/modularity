---
outline: deep
sidebarPos: 1

---

# Relationships

All of Modularity's relationships rely on [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships). We suppose that you know this relationship concepts. At now, we provide many of these as following:

- hasOne
- belongsTo
- hasMany
- belongsToMany
- hasOneThrough
- hasManyThrough
- morphTo
- morphToMany
- morphMany
- morphedByMany

## Get Started
We'll be explaining how to use this relationships on making and creating sources. We have some critical concepts for maintainability of system infrastructure. You should think each creation as a step or stage. Every stage interests both previous and next stage. You must follow instructions in the way we pointed while creating the system skeleton.

Modularity System has multiple relationship constructor mechanism. While making model and creating a module route, you can define relationships. But the **make:route** command get relationships schema and convert it the way adapted **make:model** _--relationships_. **|** delimeter can be considered array explode operator. For example, basically --relationships="name1:arg1|name2:arg2" option points stuff as following
``` php
  [
    name1 => [
      arg1
    ],
    name2 => [
      arg2
    ]
  ]
```

## Model Relationships

Model Relationships parameter add only methods to parent model, so it matters method names and parameters for special cases. 

<!-- "Model Relationships" => "belongsToMany:PackageFeature,position:integer,active:string|belongsToMany:PackageLanguage" -->
### Synopsis
```bash
php artisan modularity:make:model <moduleName> <modelName> [--relationships=<MODELRELATIONSHIPS>] [options]
```

```bash
--relationships=<MODELRELATIONSHIPS> (optional)
```
   Comma-separated list of relationships. Each relationship is defined as:

```js
<relationship_type>:<model_name>[,<field_name>:<field_type>]
```

   - `<relationship_type>`: The type of relationship (currently limited to "belongsToMany").
   - `<model_name>`: The name of the model involved in the relationship (e.g., PackageFeature, PackageLanguage).
   - `[,<field_name>:<field_type>]`: Optional field definitions, zero or more allowed.
       - `<field_name>`: The name of the field in the model (optional).
       - `<field_type>`: The data type of the field (optional, if specified).

   **Note:** Currently, this option only supports "belongsToMany" relationships. 
           Field definitions are optional but can be included for each relationship.

### Examples

Here are two valid examples of the `--relationships` argument:

1. Simple relationship with model name only:

```ini
--relationships="belongsToMany:Feature"
```

2. Relationship with a field definition:

```ini
--relationships="belongsToMany:PackageFeature,position:integer"
```

**Future Considerations:**

   Future versions of this utility may allow more complex relationship definitions with additional options. This help message provides a foundation for future expansion.

          


## Route Relationships

Route relationships parameter more complex than model relationship, both makes what model relationships does and other necessary system infrastructure elements. Pivot model and migration generating, chaining methods for sometimes pivot table column fields, reverse relationships to related models. The syntax is more similar to --schema than --relationships option of the model command.

<!-- "Route Relationships" => "package_feature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|package_language:belongsToMany" -->
### Synopsis
  <!-- package_feature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|package_language:belongsToMany -->
  <!-- [--relationships=[{routeName|columnName}:{relationshipCamelName|migrationMethodName}:{migrationChainMethod[:...]}[,...]][|...]] -->
```bash
php artisan modularity:make:route <moduleName> <routeName> [--relationships=<ROUTERELATIONSHIPS>] [options]
```

```bash
--relationships=<ROUTERELATIONSHIPS> (optional)
```
Comma-separated list of relationships. Each relationship is defined as:

```js
<model_name>:<relationship_type>,<field_name>:<field_type>[:<modifiers>]
```

- `<model_name>`: The name of the model involved in the relationship.
- `<relationship_type>`: The type of relationship (e.g., belongsToMany).
- `<field_name>`: The name of the field in the model.
- `<field_type>`: The data type of the field (e.g., integer, string).
- `[:<modifiers>]`: Optional modifiers for the field (e.g., unsigned, index, default(value)).

You can define multiple relationships separated by a pipe character (|).

### Examples

Here are two valid examples of the `--relationships` argument:

1. Simple relationship with model name only:

```ini
--relationships="PackageLanguage:morphToMany"
```

2. Relationship with a field definition:

```ini
--relationships="PackageFeature:belongsToMany,position:integer:unsigned:index,active:string:default(true)|PackageLanguage:morphToMany"
```
          

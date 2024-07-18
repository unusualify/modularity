---
# sidebarPos: 3
---
# Comparison Table <Badge type="tip" text="^0.9.2" />

The `v-input-comparison-table` component offers a comparison table with and selecting a radio input in basis. This is useful for showing detailed information about to select from multi selected items on table structure 

## Usage
You can consider it just like select input in regards to **items** attribute, you can use 'connect' attribute like 'ModuleName:RouteName|repository' or  'repository' attribute like RouteNameRepository::class. But in some cases, it can be crucial relational data sets for rendering items especially on column fields of table, you can use 'scopes' argument of repository:list method for this cases.
```
  [
    'type' => 'comparison-table',
    'items' => [],
    'connector' => '{ModuleName}:{RouteName}|repository:list:withs={RelationshipName}',
    'comparators' => [
        'features' => [
            'key' => 'features', // not required, specifies which attribute to take into account
            'field' => 'description', // not required, specifies which field of object to use,
            'itemClasses' => 'text-success font-weight-bold', // add class into span tag of each cell of row
            'title' => 'My Features', // optional, comparator cell title
        ],
        'prices' => []
    ]
    ...
  ],
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]


### 

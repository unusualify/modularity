---
# sidebarPos: 3
---
# Checklist Group <Badge type="tip" text="^0.9.2" />

The `v-input-checklist-group` component presents radio button selectable schemas. This is useful on scenarios like multiselectable schemas.

## Usage
It needs a schema attribute like standard-schema pattern. Types must be checklist for now.
``` php
  [
    ...,
    'type' => 'checklist-group', // type name
    'schema' => [ // required, for multiple radio options
        [
            'type' => 'checklist',
            'name' => 'country',
            'label' => 'Select Your Country',
            'selectedLabel' => 'Selected Countries',
            'connector' => '{ModuleName}:{RouteName}|repository:list:scopes=hasPackage:with=packageLanguages',
        ],
        [
            'type' => 'checklist',
            'name' => 'packageRegion',
            'label' => 'Select Your Region',
            'selectedLabel' => 'Selected Regions',
            'connector' => '{ModuleName}:{RouteName}|repository:list:scopes=hasPackage',
        ]
    ],
  ],
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]

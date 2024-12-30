---
# sidebarPos: 3
---
# Tab Group <Badge type="tip" text="^0.9.2" />

The `v-input-tab-group` component presents ease for long repetitive forms. You can consider it as alternative to **v-input-repeater**. You can create forms as much as possible, each forms will be on unique tab. So, the client can fill all forms without complexity
## Usage
It needs a schema attribute like standard-schema pattern. It creates clone schema for each data set. 'tabFields' attribute is crucial for filling each input items. You must follow the example.  
``` php
  [
    ...,
    'type' => 'tab-group',
    'name' => 'packages',
    'default' => [],
    'tabFields' => [
        'package_id' => 'packages',
        'packageLanguages' => 'packageLanguages'
    ],
    'schema' => [
        [
            'type' => 'any-input',
            'name' => 'package_id'
            ...
        ],
        [
            'type' => 'any-input',
            'name' => 'packageLanguages'

            ...

        ],
    ]
  ],
```

> [!IMPORTANT]
> This component was introduced in [v0.9.2]

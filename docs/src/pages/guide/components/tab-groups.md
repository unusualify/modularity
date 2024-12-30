---
# sidebarPos: 3
---
# Tab Groups <Badge type="tip" text="^0.10.0" />

The `ue-tab-groups` component presents **ue-tabs** component with some additional features and ease-to-use case. You must pass 'items' prop into the component such as passing it to **ue-table**. 

You must pass the 'group-key' prop creating groups from items into the component.

## Usage
It has 'items' prop as array, the **group-key** prop should be in each item of this array.
``` php
  @php
    $items = [
      ['id' => 1, 'name' => 'Deneme', 'description' => 'Description 1', 'category' => 'group 1'],
      ['id' => 2, 'name' => 'Deneme 2', 'description' => 'Description 2', 'category' => 'group 1'],
      ['id' => 3, 'name' => 'Yayın 3', 'description' => 'Description 3', 'category' => 'group 2'],
      ['id' => 4, 'name' => 'Yayın 4', 'description' => 'Description 4', 'category' => 'group 2'],
    ]
  @endphp

  <ue-tab-groups :items='@json($items)' group-key='category'/>
```

> [!IMPORTANT]
> This component was introduced in [v0.10.0]


### 

---
# sidebarPos: 3
---
# Select Scrolls <Badge type="tip" text="^0.9.1" />

The `v-input-select-scroll` component offers simple async functionality. This is useful when loading large sets of data and while scrolling on menu of select. 

Default input type is **v-autocomplete**.

## Usage
You can consider as standard select input, add input attributes to config as following:
```
  [
    'type' => 'autocomplete', // or 'select', 'combobox'
    'ext' => 'scroll',
    'connector' => '{ModuleName}:{RouteName}|uri',
    ...
  ],x
```
or
```
  [
    'type' => 'select-scroll',
    'connector' => '{ModuleName}:{RouteName}|uri',
    ...
  ],
```

> [!IMPORTANT]
> This component was introduced in [v0.9.1]


### 
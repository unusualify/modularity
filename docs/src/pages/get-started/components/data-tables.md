---
# https://vitepress.dev/reference/default-theme-home-page
sidebarPos: 1

---

# Data Tables

The data table component is used for displaying registered data in your index pages. Despite tabular user-interface is auto constructed while related route generation process, most listing functionalities are can be customized.

::: info Customization

Table functionalities and user-interface is highly customizable. In order to customize default-set, module config file will be used

:::
## Table Component Defaults

In default, Modularity package automatically generates an default table user-interface with default table functionalities like `create new button`, `filtering`, `pagination` and `an embeded create-edit form` based on served functionalities of route itself and `user's permission`. Furthermore, based on registered data properties and user's permissions, item actions like `delete`, `restore` will be placed. 

It is avaliable to serve desired user-interface and user-experience on each data table via related module config files. Go your module's `config.php` and customize `table_options` key-value pairs to observe change.

## Table and Related Component Props
Following table will show customizable key-value pairs, their description and default values. In order to observe better, you can visit blablabla

#### `embeddedForm`

Configures create-edit form behaviour to be served in same page and embedded to the table upper slot.

 - `Input Type:` `Boolean`  
 - `Variance:` `true|false`
 - `default:`  `true`

<br/>

#### `createOnModal`
Configures create forms behaviour to be served in a modal dialog if it is viable.

 - `Input Type:` `Boolean`  
 - `Variance:` `true|false`
 - `default:`  `true`

<br/>

#### `editOnModal`

Edit on Modal option will set the edit form behaviour to be appear in a modal dialog when its triggered.

 - `Input Type:` `Boolean`  
 - `Variance:` `true|false`
 - `default:`  `true`

<br/>

#### `rowActionsType`

Visual serving option of the item actions like `delete`,`edit` inline or with a dropdown button.

- `Input Type:` `String`
- `Variance:` `inline|dropdown`
- `default`: `inline`

<br/>

#### `tableClasses` 
Applies extra css classes to data table. Also, modularity serves some default css classes that can be used.

- `Input Type:` `String`
- `Variance`: `No Variance`
- `default`: `elevation-2`
  
::: tip Table Style Classes
Utility classes served under [VuetifyJS-Utility Classes](https://vuetifyjs.com/en/styles/borders/#sass-variables) can be observed and be used to construct customized data-table. Also modularity serves pre-defined styles which are `zebra-stripes`, `free-form`, `grid-form`.
:::

<br/>

#### `hideHeaders`
Hides the header row of the tabular component
- `Input Type:` `Boolean`
- `Variance`: `true|false`
- `default`: `false`

<br/>

#### `hideSearchField`
Hides the text-search field
- `Input Type:` `Boolean`
- `Variance`: `true|false`
- `default`: `false`

<br/>

#### `tableDensity`
Adjusts the vertical height used by the component.
- `Input Type:` `String`
- `Variance`: `'default' | 'comfortable' | 'compact'`
- `default`: `compact`

<br/>

#### `sticky`
Sticks the header to the top of the table.
- `Input Type:` `Boolean`
- `Variance`: `true|false`
- `default`: `false`

<br/>

#### `showSelect`
Shows the column with checkboxes for selecting items in the list. Bulk actions can be done on selected items
- `Input Type:` `Boolean`
- `Variance`: `true|false`
- `default`: `true`

<br/>

#### `toolbarOptions`
[Vuetify toolbar component](https://vuetifyjs.com/en/components/toolbars/) is used as a top wrapper of the data tables. It includes bulk action buttons, search field, filter buttons and create button in it. Toolbar can be customized in multiple ways, its background color, border and etc.

- `Input Type: Array`
- `default:` 
``` php
  [
    'color' => 'transparent', // rgb(255,255,255,1) or utility colors like white, purple
    'border' => false, // false, 'xs', 'sm', 'md', 'lg', 'xl'.
    'rounded' => false, // This can be 0, xs, sm, true, lg, xl, pill, circle, and shaped. string | number | boolean
    'collapse' => false, // false, true,
    'density' => 'compact', // prominent, comfortable, compact, default
    'elevation' => 0, // string or number refers to elevation
    'image' => '', //
  ]

```
<br/>


#### `addBtnOptions`
[Vuetify's default Button Component](https://vuetifyjs.com/en/components/buttons/#usage) is used to construct create button user-interface and some functionality. It can be customize the props vuetify serves and some extra props modularity serves.


- `Input Type: Array`
- `default:` 
``` php
[
  'variant' => 'elevated', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
  'color' => 'orange', // rgb(255,255,255,1) or utility colors like white, purple
  'prepend-icon' => 'mdi-plus', // material design icon name,
  'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
  'ripple' => true, // boolean
  'rounded' => 'md', // string | number | boolean - 0, xs, sm, true, lg, xl, pill, circle, and shaped.
  'class' => 'ml-2 text-white text-capitialize text-bold',
  'size' => 'default', //sizes: x-small, small, default, large, and x-large.
  'text' => 'CREATE NEW',
]
```
<br/>

#### `filterBtnOptions`
[Vuetify's default Button Component](https://vuetifyjs.com/en/components/buttons/#usage) is used to construct filter button user-interface and some functionality. It can be customize the props vuetify button serves and some extra props modularity serves.


- `Input Type: Array`
- `default:` 
``` php
[
  'variant' => 'elevated', //'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
  'color' => 'purple', // rgb(255,255,255,1) or utility colors like white, purple
  'readonly' => false, // boolean to set the button readonly mode, can be used to disable button
  'ripple' => true, // boolean
  'class' => 'mx-2 text-white text-capitialize rounded px-8 h-75',
  'size' => 'small', //sizes: x-small, small, default, large, and x-large.
  'prepend-icon' => 'mdi-chevron-down',
  'slim' => false,
]
```

::: warning Button Props
All props served under [Vuetify.js Button API Page](https://vuetifyjs.com/en/api/v-btn/#links) are avaliable to use for filter and create button of tabular user-interface.
:::

<br/>

#### `paginationOptions`

Pagination options controls pagination functionalities and pagination user-interface placed on the table footer. This version of modularity serves three different pagination options which are, `default`, `vuePagination`, and `infiniteScroll`.

- `Input Type:` `Array`
- `default: with default option`
  ```php
  [
    'footerComponent' => 'default', // default|vuePagination|null:
    'footerProps' => [
      'itemsPerPageOptions' => [
        ['value' => 1, 'title' => '1'],
        ['value' => 10, 'title' => '10'],
        ['value' => 20, 'title' => '20'],
        ['value' => 30, 'title' => '30'],
        ['value' => 40, 'title' => '40'],
        ['value' => 50, 'title' => '50'],
      ],
      'showCurrentPage' => true,
    ],
  ]
  ```

  ::: info Pagination Options
  There are three different pagination options `default`, `vuePagination`, and `infiniteScroll` Modularity serves.
  :::

  ::: tip Default Pagination
  For the `default` pagination option props, all default pagination options under [Vuetify.js Data Table Server API Reference](https://vuetifyjs.com/en/api/v-data-table-server/#props) can be used.
  :::


- `default: with vuePagination Option`
  ```php
  'footerProps' => 
    [ 
      'variant' => 'flat', //'flat' | 'elevated' | 'tonal' | 'outlined' | 'text' | 'plain' -- 'text' in default
      'active-color' => 'black', // utility colors or rgba(x,x,x,a),
      'color' => 'primary', // utility colors or rgba(x,x,x,a),
      'density' => 'default', // default | comfortable | compact
      'border' => false, // string|number|boolean xs, sm, md, lg, xl. -- false in default
      'elevation' => 3,// string | number or undefined in default
      'rounded' => 'default', // string|number or boolean 0.xs.sm.true,lg,xl,pill, circle, and shaped
      'show-first-last-page' => false, // boolean,
      'size' => 'default', // string | number  Sets the height and width of the component. Default unit is px. Can also use the following predefined sizes: x-small, small, default, large, and x-large.
      'total-visible' => 0 //| number  - if 0 is given numbers totally not be shown
    ]
  ```

  ::: tip Vue Pagination Component
  For this option [Vuetify.js Pagination Component](https://vuetifyjs.com/en/components/paginations/) is used. Please see the API Reference page for further customization options.
  :::
  ::: warning Pagination Number Buttons
  `total-visible` key assignment is optional. Assigning a number will control number of button shown on user-interface. `Not assigning` it will let table to construct it automatically. Lastly, assigning `0`(zero) as an input, only next and previous page buttons will be shown on the footer.
  :::



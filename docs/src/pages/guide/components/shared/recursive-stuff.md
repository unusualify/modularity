---
sidebarPos: 23
sidebarTitle: Recursive Stuff
---
# Recursive Stuff

`ue-recursive-stuff` is the configuration-driven component renderer at the heart of Modularous dynamic UI system. It takes a `configuration` object that describes a component tree and renders it recursively.

## Configuration Object Shape

```js
{
  tag: 'v-chip',            // any registered component or HTML tag
  attributes: {             // props / attrs bound to the component
    color: 'primary',
    size: 'small',
  },
  elements: 'Hello World',  // child content: string | config object | array of configs
  slots: {                  // named slot content, each value is another configuration
    prepend: { tag: 'v-icon', attributes: { icon: 'mdi-check' } }
  },
  bind: ['item'],           // keys from bindData to spread as attributes
  directives: {             // Vue directives to apply
    show: '$item.active'
  }
}
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `configuration` | `Object\|Array\|String` | `{}` | The component/element descriptor |
| `level` | `Number` | `0` | Current recursion depth (set automatically by recursive children) |
| `bindData` | `Object` | `{}` | Contextual data available for `$` interpolation inside attribute values |

## Data Binding Syntax

Attribute values starting with `$` are resolved from `bindData`:

```js
{ tag: 'span', elements: '$item.name' }
// renders the value of bindData.item.name
```

Expression syntax is also supported with `{...}`:

```js
{ tag: 'span', elements: '{ $count + 1 }' }
```

## Usage from PHP / Blade

Modularous backend services generate `ue-recursive-stuff` configuration objects automatically for table formatters, form slots, and index page blocks. You can also build them manually:

```php
$configuration = [
  'tag' => 'v-chip',
  'attributes' => ['color' => 'success', 'size' => 'small'],
  'elements' => 'Active',
];
```

```html
<ue-recursive-stuff :configuration='@json($configuration)' />
```

::: tip Formatters
Table column formatters defined in `config.php` (`'formatter' => [...]`) are converted to `ue-recursive-stuff` configurations automatically by the datatable rendering pipeline.
:::

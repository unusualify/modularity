---
sidebarPos: 5
sidebarTitle: Form
---
# Form

The `ue-form` component is the primary schema-driven form wrapper in Modularous. It wraps Vuetify's `v-form`, renders inputs from a schema object, handles submission, validation, and optional right-side content panels.

## Basic Usage

```php
@php
  $schema = $this->createFormSchema([
    ['type' => 'text', 'name' => 'name', 'label' => 'Name'],
    ['type' => 'email', 'name' => 'email', 'label' => 'Email'],
  ]);
@endphp

<ue-form
  :model-value='@json($item)'
  :schema='@json($schema)'
  action-url="{{ route('module.store') }}"
  title="Create User"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Object` | required | The form data model |
| `schema` | `Object` | required | Input schema — built with `createFormSchema()` |
| `actionUrl` | `String` | — | Form submission endpoint |
| `title` | `String\|Object` | — | Header title. Pass an object for advanced options (type, weight, color, etc.) |
| `subtitle` | `String` | — | Subtitle shown below the title |
| `noTitle` | `Boolean` | `false` | Hide the title row |
| `isEditing` | `Boolean` | `false` | Switches form into edit mode (PUT vs POST) |
| `async` | `Boolean` | `true` | Submit via axios; if `false`, submits the native HTML form |
| `hasSubmit` | `Boolean` | `false` | Render a built-in submit button |
| `buttonText` | `String` | — | Label for the built-in submit button |
| `hasDivider` | `Boolean` | `false` | Show a divider below the title row |
| `fillHeight` | `Boolean` | `false` | Stretch the form to fill available viewport height |
| `scrollable` | `Boolean` | `false` | Make the input area scrollable (useful inside modals) |
| `formClass` | `String\|Array` | `''` | Extra CSS classes on the inner `v-form` |
| `noDefaultFormPadding` | `Boolean` | `false` | Remove the default `pa-4` padding |
| `noDefaultSurface` | `Boolean` | `false` | Remove the default `bg-surface` background |
| `actions` | `Array\|Object` | `[]` | Action button definitions rendered by `FormActions` |
| `actionsPosition` | `String` | `'top'` | Where actions render: `title-right`, `title-center`, `top`, `middle`, `bottom`, `right-top`, `right-middle`, `right-bottom` |
| `rowAttribute` | `Object` | `{noGutters: false, class: 'py-4'}` | Props forwarded to the wrapping `v-row` around inputs |
| `rightSlotWidth` | `Number\|String` | `null` | Fixed width (px) of the right-side panel |
| `rightSlotMinWidth` | `Number\|String` | `300` | Min width (px) of the right-side panel |
| `rightSlotMaxWidth` | `Number\|String` | `600` | Max width (px) of the right-side panel |
| `rightSlotGap` | `Number` | `12` | Margin between the form and the right panel |
| `clearOnSaved` | `Boolean` | `false` | Reset form after a successful save |
| `refreshOnSaved` | `Boolean` | `false` | Reload the datatable after a successful save |
| `noWaitSourceLoading` | `Boolean` | `false` | Render inputs immediately without waiting for async source data |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `header.left` | `{title, subtitle, model, schema, formItem}` | Replaces the left side of the title row |
| `headerCenter` | — | Extra content injected into the title row center |
| `top` | `{item, schema}` | Content above the input block |
| `underside` | `{isEditing, item, schema}` | Content below the input block |
| `actions.prepend` | actions scope | Prepend content before action buttons |
| `actions.append` | actions scope | Append content after action buttons |
| `right-top` | — | Content at the top of the right panel |
| `right-middle` | — | Content in the middle of the right panel |
| `right-bottom` | — | Content at the bottom of the right panel |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `Object` | Emitted on every input change |
| `update:valid` | `Boolean` | Emitted when form validation state changes |
| `input` | input event object | Raw input event from the form base |
| `submitted` | response data | Emitted after a successful async submission |
| `actionComplete` | action result | Emitted when a `FormActions` button completes |

## Example — Form Inside a Blade View

```php
@php
  $schema = $this->createFormSchema([
    ['type' => 'text',   'name' => 'title',  'label' => 'Title',  'rules' => 'required'],
    ['type' => 'textarea','name' => 'body',  'label' => 'Body'],
    ['type' => 'select', 'name' => 'status', 'label' => 'Status',
      'items' => [
        ['value' => 'draft',     'label' => 'Draft'],
        ['value' => 'published', 'label' => 'Published'],
      ]
    ],
  ]);
@endphp

<ue-form
  :model-value='@json($post ?? [])'
  :schema='@json($schema)'
  action-url="{{ route('posts.store') }}"
  title="New Post"
  has-submit
  button-text="Save"
  is-editing="{{ isset($post) ? 'true' : 'false' }}"
/>
```

::: tip Schema Builder
The `createFormSchema()` helper (available inside Modularous controllers and views) normalises raw field arrays into the schema format `ue-form` expects. See [Hydrates](/system-reference/hydrates) for the full list of supported input types.
:::

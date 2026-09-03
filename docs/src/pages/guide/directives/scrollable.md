---
sidebarPos: 5
sidebarTitle: Scrollable
---

# v-scrollable

Marks an element as a scroll region (`ue-scrollable`). Use it on **height-constrained** parents so overflowing children scroll instead of spilling out of the box.

**File:** `vue/src/js/directives/scrollable.js` · **CSS:** `vue/src/sass/core/directives/_scrollable.scss`

Dashboard widgets already opt in via `widgetDirectives.scrollable` — see [Dashboard widgets](/guide/dashboard/overview).

## Fill parent (dashboard / constrained columns)

No height value. Adds `.ue-scrollable` and `min-height: 0` (flex items otherwise refuse to shrink below content). On hosts that also have a height utility (`h-50`, `h-100`, …), CSS then:

- clips the host
- fills a nested `v-card` to the host height
- keeps title / subtitle / actions pinned
- scrolls `.v-card-text`

```vue
<v-col
  v-scrollable
  class="h-50"
>
  <ue-system-console class="h-100" />
</v-col>
```

From PHP widget config (applied on the wrapper `v-col` by `ue-recursive-stuff`):

```php
'widgetAttributes' => [
    'class' => 'h-50',
    'style' => 'min-height: 160px',
],
'widgetDirectives' => [
    'scrollable' => true,
],
```

Do **not** put `v-scrollable` on a multi-root Vue widget (`ue-system-console` has dialogs beside the card). Directives belong on the single-root column.

## Explicit height

```vue
<div v-scrollable.height="240">
  Scrolls inside 240px
</div>
```

CSS length string:

```vue
<div v-scrollable.height="'50vh'">
  Scrolls inside half the viewport
</div>
```

A valid CSS length as the binding value (without `.height`) also sets `height` + `overflow-y: auto`:

```vue
<div v-scrollable="'100%'">
  …
</div>
```

## Notes

- Valid heights: number (px), or string matching `px|em|rem|vh|%`.
- Updating the bound height updates `el.style.height` on `updated`.
- Opt out on a widget: `'widgetDirectives' => ['scrollable' => false]`.
- `ue-recursive-stuff` accepts dotted modifiers (`scrollable.height`) and `{ value, arg, modifiers }` descriptors.

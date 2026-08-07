---
sidebarPos: 7
sidebarTitle: Transition
---

# v-transition

Applies a CSS transition class and toggles enter/leave state when the `d-none` class is added or removed (watched via `MutationObserver`).

**File:** `vue/src/js/directives/transition.js`

## Examples

Default (`scale` transition):

```vue
<div
  v-transition
  :class="{ 'd-none': !open }"
>
  Panel
</div>
```

Named argument + slow modifier:

```vue
<div
  v-transition:fade.slow
  :class="{ 'd-none': !visible }"
>
  Fades when shown
</div>
```

| Piece | Meaning |
|-------|---------|
| Argument (`:fade`, `:scale`, …) | Transition type class prefix (default `scale`) |
| `.slow` | Longer duration (`0.3s` vs `0.2s`) |

Requires matching CSS classes such as `{type}-transition` and `{type}-1` in the theme.

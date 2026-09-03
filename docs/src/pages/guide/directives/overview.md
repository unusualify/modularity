---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Directives
---

# Vue Directives Overview

Modularous ships a small set of global Vue directives under `vue/src/js/directives/`. They are registered in `plugins/UEConfig.js` via `app.use(...)`.

## Active directives

| Directive | File | Purpose |
|-----------|------|---------|
| [`v-viewport-fit`](./viewport-fit) | `viewport-fit.js` | Fill leftover viewport below dynamic chrome (filters / alerts) |
| [`v-column`](./column) | `column.js` | Apply Vuetify `v-col-*` classes from a binding object |
| [`v-fit-grid`](./fit-grid) | `fit-grid.js` | Flex parent + stretch first child to full size |
| [`v-scrollable`](./scrollable) | `scrollable.js` | Scroll region; fill parent or fixed height |
| [`v-svg`](./svg) | `svg.js` | Inject an SVG sprite icon into the element |
| [`v-transition`](./transition) | `transition.js` | Show/hide transitions driven by `d-none` |

Also registered: `v-mask` (from `v-mask` package) for input masks.

## Registration

```js
// vue/src/js/plugins/UEConfig.js
app.use(Column)
app.use(FitGrid)
app.use(Scrollable)
app.use(Transition)
app.use(ViewportFit)
app.use(SvgSprite)
```

New directives: add a file under `vue/src/js/directives/`, export `{ install(app) { app.directive('name', dir) } }`, then `app.use(...)` in `UEConfig.js`.

## Legacy (not registered)

| File | Notes |
|------|-------|
| `sticky_.js` | Vue 2-style API; not wired in `UEConfig` |
| `tooltip_.js` | Vue 2-style API; prefer Vuetify `v-tooltip` |

## Related

- Frontend agent rules: `vue/src/js/AGENTS.md`
- Example consumer: Module Route Inspect page (`Pages/ModuleRouteInspect.vue`) uses [`v-viewport-fit`](./viewport-fit)

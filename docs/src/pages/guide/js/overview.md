---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: JS
---

# JS form utilities

Modularous admin forms are schema-driven. Besides Hydrates and Vue input components, a small set of utilities under `vue/src/js/utils/` shape the model, flatten schema, and run cross-field events.

## Files

| File | Purpose |
|------|---------|
| [`form-events`](./form-events) | `ext` → `event` → `formEventFormatters` (set, update, filter, …) |
| `schema.js` | `isViewOnlyInput`, flatten wrap/group schema, translation input walk |
| `getFormData.js` | `getSchema` / `getModel` / submit payload; calls `handleEvents` on hydrate |

## Related

- Form inputs: [Forms overview](/guide/form-inputs/overview)
- Frontend map: [System reference — Frontend](/system-reference/frontend/overview)
- Agent rules: `vue/src/js/utils/formEventFormatters/AGENTS.md`

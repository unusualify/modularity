# Cms Module — Agent Rules

Built-in CMS module (`modules/Cms`). Public pages, URL routes, layouts, and presentation cache opt-in.

## Hard rules

1. Public HTML cache is **`presentationItem`** (file-primary URL store by default) — not admin Redis types (`formItem`, `formattedItem`, …).
2. Render path: CMS front controller → presentation cache helper → layout wrapper (shell/nav stay fresh; body may be cached).
3. Universal public front: Studly module + route for cache context come from the **model** (e.g. `CmsPublicFrontViewName` helpers), not a hard-coded `Cms/Public` pair.
4. Signed preview / force-preview URLs bypass presentation cache.
5. Warm must hit the **public site URL** and locale context — see `src/Services/Cache/AGENTS.md`.
6. Package development only: change module internals here; do not write end-user “how to create a module” guides in AGENTS.

## Typical touch points

| Concern | Area |
|---------|------|
| Public routing / UrlRoute | `modules/Cms` Http, Routing, Entities |
| Layouts / page shells | Resources views + layout builders |
| Presentation cache write/read | Controllers + Jobs coordinating with `Services/Cache` |
| Foundation schema | `modules/Cms/Database/Migrations` |

## Read before edit

- `docs/src/pages/guide/cms/overview.md`
- `docs/src/pages/guide/cms/public-routing.md`
- `docs/src/pages/guide/cms/page-layouts.md`
- `docs/src/pages/guide/module-route-cache/cms-public-pages.md`
- `docs/src/pages/guide/module-route-cache/url-stale-resilience.md`
- Cache internals: `src/Services/Cache/AGENTS.md`

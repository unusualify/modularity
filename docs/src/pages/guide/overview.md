---
sidebarPos: 1
sidebarTitle: Guide Overview
---

# Guide

This section covers UI components, forms, tables, and CMS public-site features in Modularous.

## CMS

| Page | Description |
|------|-------------|
| [CMS Overview](/guide/cms/overview) | Building blocks, request flow, relationships |
| [Public routing](/guide/cms/public-routing) | Catch-all order, host `web.php`, excludes |
| [Parent segments & URL routes](/guide/cms/parent-segments-and-url-routes) | Prefixes + UrlRoute registry |
| [Stylesheets](/guide/cms/stylesheets) | CSS bundles + public `.css` route |
| [Layout builders](/guide/cms/layout-builders) | Document shells + Blade sources |
| [Page layouts](/guide/cms/page-layouts) | Per-model presentation shells |
| [Redirects](/guide/cms/redirects) | Visitor redirect rules |
| [Sitemap & SEO](/guide/cms/sitemap-and-seo) | `/sitemap.xml`, robots.txt |
| [Configuration](/guide/cms/configuration) | `modularous.cms_*` keys |

## Dashboard

| Page | Description |
|------|-------------|
| [Dashboard widgets](/guide/dashboard/overview) | `ui_settings.dashboard.blocks`, widget schema, host overrides |
| [System Console](/guide/dashboard/system-console) | `system_console` config: down presets, cache and custom commands |

## Components

| Page | Description |
|------|-------------|
| [Data Tables](/guide/components/shared/data-tables) | Table component, table options, customization |
| [Forms](/guide/form-inputs/overview) | Form architecture, FormBase, schema flow |
| [Input Form Groups](/guide/form-inputs/input-form-groups) | Form groups and layout |
| [Input Checklist Group](/guide/form-inputs/input-checklist-group) | Checklist group input |
| [Input Comparison Table](/guide/form-inputs/input-comparison-table) | Comparison table input |
| [Input Filepond](/guide/form-inputs/input-filepond) | Filepond file upload |
| [Input Radio Group](/guide/form-inputs/input-radio-group) | Radio group input |
| [Input Select Scroll](/guide/form-inputs/input-select-scroll) | Scrollable select input |
| [Tab Groups](/guide/components/shared/tab-groups) | Tab groups for forms |
| [Tabs](/guide/components/shared/tabs) | Tab component |
| [Stepper Form](/guide/components/shared/stepper-form) | Stepper form component |

## JS

| Page | Description |
|------|-------------|
| [JS utilities overview](/guide/js/overview) | formEvents, schema, getFormData |
| [Form events](/guide/js/form-events) | `ext` → `event` → formatters; translated vs scalar |

## Directives

| Page | Description |
|------|-------------|
| [Directives overview](/guide/directives/overview) | Registration + active directive index |
| [v-viewport-fit](/guide/directives/viewport-fit) | Fill leftover viewport below chrome (tables) |
| [v-column](/guide/directives/column) | Vuetify column classes |
| [v-fit-grid](/guide/directives/fit-grid) | Flex + stretch first child |
| [v-scrollable](/guide/directives/scrollable) | Scroll region / fixed height |
| [v-svg](/guide/directives/svg) | SVG sprite injection |
| [v-transition](/guide/directives/transition) | `d-none`-driven transitions |

## Architecture Reference

For system internals (Hydrates, Repositories, schema flow), see [System Reference](/system-reference/overview).

---
sidebarPos: 0
sidebarTitle: Overview
---

# Components Overview

Modularous ships with 50+ Vue components covering forms, tables, modals, navigation, layouts, and more. All components live in `vue/src/js/components/`.

See [Components Overview](./overview) for how components are organized in the source tree, the input registry, and labs/experimental conventions.

## Forms & Inputs

| Component | Purpose |
|-----------|---------|
| [Form](./form) | Root form wrapper; wires fields to schema and submit flow |
| [Form Actions](./form-actions) | Submit / cancel / custom action buttons |
| [Form Summary Item](./form-summary-item) | Read-only summary row for form review |
| [Stepper Form](./stepper-form) | Legacy stepper form (see `stepper/` for current) |

Form inputs (30+ `input-*` components) are documented under [Form Inputs](../form-inputs/overview).

## Tables & Data

| Component | Purpose |
|-----------|---------|
| [Data Tables](./data-tables) | Primary table component — filters, sorting, pagination, actions |
| [Data Iterators](./data-iterators) | Row/card iterators (RichRow, RichCard) |
| [Table Binder](./table-binder) | Binds a repository response to a table |
| [Table Internals](./table-internals) | Lower-level table primitives |

## Modals

| Component | Purpose |
|-----------|---------|
| [Modal](./modal) | Base modal wrapper |
| [Dynamic Modal](./dynamic-modal) | Modal driven by route/payload |
| [Modal Media](./modal-media) | Media-picker modal |
| [Logout Modal](./logout-modal) | Session / logout confirmation |

## Navigation & Structure

| Component | Purpose |
|-----------|---------|
| [Tabs](./tabs) | Standard tabs |
| [Tab Groups](./tab-groups) | Grouped tabs with shared state |
| [Navigation Group](./navigation-group) | Sidebar navigation group |
| [Collapsible](./collapsible) | Collapsible wrapper |
| [Expansion](./expansion) | Expansion panel |

## Display

| Component | Purpose |
|-----------|---------|
| [Configurable Card](./configurable-card) | Card with slot-based sections |
| [Metric](./metric) | Stat/metric tile |
| [Property List](./property-list) | Key/value list for record detail |
| [List Section](./list-section) | Titled list block |
| [Text Display](./text-display) | Truncation, copy-on-click, formatting |
| [Title](./title) | Page/section title with actions |
| [Markdown Render](./markdown-render) | Markdown → HTML renderer |
| [Currency Number](./currency-number) | Formatted currency display |
| [SVG Icon](./svg-icon) | Icon renderer |

## Feedback

| Component | Purpose |
|-----------|---------|
| [Alert](./alert) | Alert banner (info, warning, error, success) |
| [Error Card](./error-card) | Error detail card |
| [Success](./success) | Success confirmation block |

## Files & Media

| Component | Purpose |
|-----------|---------|
| [File Item](./file-item) | File row with actions |
| [Filepond Preview](./filepond-preview) | Filepond attachment preview |
| [Uploader](./uploader) | File upload widget |

## Filters & Search

| Component | Purpose |
|-----------|---------|
| [Filter](./filter) | Filter bar |
| [Dropdown Filter](./dropdown-filter) | Dropdown-based filter |

## Actions & Utilities

| Component | Purpose |
|-----------|---------|
| [Btn](./btn) | Button wrapper with consistent styling |
| [Copy Text](./copy-text) | Copy-to-clipboard text |
| [Print Request](./print-request) | Print-ready request view |
| [Well Print](./well-print) | Print wrapper block |
| [Dynamic Component Renderer](./dynamic-component-renderer) | Renders a component by name |
| [Recursive Data Viewer](./recursive-data-viewer) | Tree/object viewer |
| [Recursive Stuff](./recursive-stuff) | Recursive utility rendering |

## Content & Messaging

| Component | Purpose |
|-----------|---------|
| [Blocks](./blocks) | Block system renderer |
| [Board Information Plus](./board-information-plus) | Info board card |
| [Chat Message](./chat-message) | Chat message row |
| [Assignee Details](./assignee-details) | Assignee info block |

## Auth

| Component | Purpose |
|-----------|---------|
| [Auth](./auth) | Auth shell / guard wrapper |
| [Impersonate Toolbar](./impersonate-toolbar) | Impersonation banner |

## Payments

| Component | Purpose |
|-----------|---------|
| [Revolut Checkout](./revolut-checkout) | Revolut payment checkout integration |

---

## Subcategories

| Subcategory | Description |
|-------------|-------------|
| [Layouts](./layouts/overview) | App layout components — Main, Sidebar, Home, Footer |
| [Stepper](./stepper/overview) | Stepper form components — Header, Content, Summary, Preview |
| [Labs](./labs/overview) | Experimental components — may change or be removed |
| [Others](./others/overview) | Assignment modal, custom form bases, datatable legacy |

---

## Related

- [Form Inputs](../form-inputs/overview) — `input-*` components (date, file, price, repeater, etc.)
- [Module Features](../module-features/overview) — traits that generate UI (files, payment, chat, etc.)
- [Hydrates](/system-reference/hydrates) — backend → frontend schema transformation

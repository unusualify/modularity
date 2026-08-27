---
sidebarPos: 32
sidebarTitle: Source Text
---

# Source Text

The `source-text` input type renders `VInputSourceText`, a compact source editor for long text (`md`, `txt`, `js`, `php`, `html`). The closed field is read-only: it shows the **first line** plus a chip `{lineCount} · {format}`. Clicking the field or the open-in-new icon opens a modal [CodeMirror 6](https://codemirror.net/) editor whose language follows schema `format`. **Keep** commits the draft to `modelValue`; **Cancel**, Esc, or backdrop close asks to discard when the draft is dirty. Format is schema-only — the user cannot change it.

For `md` and `html`, the modal toolbar has **Edit | Preview**. Markdown is rendered with `marked`. HTML preview uses a sandboxed iframe (`sandbox=""` — scripts do not run). `js`, `php`, and `txt` have no preview.

Validation (`rules`) runs on the **committed** value in the closed field, not on the in-modal draft. Empty Keep is allowed when the field is `nullable`.

## Hydrate

**Class:** `SourceTextHydrate`
**Config type:** `source-text`
**Output type:** `input-source-text` → `VInputSourceText`

The hydrate:

- Sets `type` to `input-source-text` and defaults `col.cols` to `12`
- Normalizes `format` to one of `md` | `txt` | `js` | `php` | `html` (unknown values become `md`)
- Defaults `rows` to `18` (modal editor min-height), `autoGrow` to `true`, `variant` to `outlined`, `density` to `comfortable`, `persistentHint` to `true`

## Format → editor language

| `format` | CodeMirror language | Preview |
|----------|---------------------|---------|
| `md` | `@codemirror/lang-markdown` | Rendered markdown (`marked`) |
| `html` | `@codemirror/lang-html` | Sandboxed iframe |
| `js` | `@codemirror/lang-javascript` | — |
| `php` | `@codemirror/lang-php` | — |
| `txt` | none (plain text, line numbers only) | — |

## Usage

### Markdown (llms.txt)

```php
[
    'name' => 'llms_txt',
    'type' => 'source-text',
    'label' => 'Global llms.txt',
    'format' => 'md',
    'rules' => 'nullable|string',
    'hint' => 'Markdown served at GET /llms.txt (llmstxt.org).',
]
```

### HTML snippet

```php
[
    'name'   => 'head',
    'type'   => 'source-text',
    'label'  => 'Head scripts',
    'format' => 'html',
    'rules'  => 'nullable|string',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `format` | `'md'` | CodeMirror language (`md`, `txt`, `js`, `php`, `html`). Not user-editable. |
| `rows` | `18` | Minimum editor height in the modal (`rows × 1.5rem`) |
| `autoGrow` | `true` | Kept for schema compatibility (CodeMirror fills the modal) |
| `variant` | `'outlined'` | Closed field variant |
| `density` | `'comfortable'` | Closed field density |
| `persistentHint` | `true` | Keep `hint` visible on the closed field |

## See Also

- [Sitemap & SEO](/guide/cms/sitemap-and-seo) — `seo.llms_txt` is served at `GET /llms.txt`
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract

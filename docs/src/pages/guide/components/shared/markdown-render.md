---
sidebarPos: 18
sidebarTitle: Markdown Render
---
# Markdown Render

`ue-markdown-render` converts a Markdown string to HTML using [marked](https://marked.js.org/) and renders it in a styled container. When the document contains headings, an auto-generated sticky table of contents appears in a right-side column.

## Usage

```html
<ue-markdown-render :markdown="article.body" />
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `markdown` | `String` | yes | Raw Markdown string to render |

## Features

- **Heading anchors** — every `h1`–`h6` gets a slug-based `id` attribute for deep linking.
- **Table of contents** — headings up to `h3` are extracted and shown in a sticky right-side nav on `md+` screens. The TOC is hidden when no headings are present.
- **Slug deduplication** — duplicate heading texts get `-2`, `-3`, … suffixes automatically.
- **GitHub-style prose styles** — code blocks, blockquotes, lists, and inline code are styled to match GitHub Markdown.

## Example — Render a CMS Page Body

```php
<ue-markdown-render :markdown='@json($page->body)' />
```

::: warning Sanitization
`ue-markdown-render` renders raw HTML via `v-html`. Never pass untrusted user-generated content without sanitizing it first on the server side.
:::

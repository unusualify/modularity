---
sidebarPos: 27
sidebarTitle: Well Print
---
# Well Print

`ue-well-print` renders text with automatic URL linkification and newline-to-`<br>` conversion. Detected URLs become clickable `<a>` tags that open in a new tab.

## Usage

```html
<!-- via prop -->
<ue-well-print text="Visit https://example.com for details." />

<!-- via slot (text content is extracted and linkified) -->
<ue-well-print>
  Check out https://example.com
</ue-well-print>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `text` | `String` | `''` | Text to render. Takes precedence over slot content |
| `fullText` | `String` | `null` | Untruncated version of `text`. Used to recover full URLs when `text` is a truncated excerpt |
| `noLinkify` | `Boolean` | `false` | Disable URL detection — text is still formatted but links are not created |

## Behaviour

- Detects `https://`, `http://`, `www.`, and bare domain patterns.
- Relative/bare URLs are normalised to `https://` before being set as `href`.
- `fullText` is useful when displaying a short preview: the visible text may cut a URL mid-string, but the `href` is sourced from `fullText` so the link points to the correct destination.
- Uses `v-html` internally — never pass unsanitised user input.
- `white-space: pre-line` is applied, so newlines in the source text produce visible line breaks.

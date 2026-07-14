---
sidebarPos: 41
sidebarTitle: FilePond Preview
---
# FilePond Preview

`ue-filepond-preview` renders a list of uploaded file entries as thumbnail cards or icon cards. Image files show a thumbnail fetched from `/api/filepond/preview/:uuid`; non-image files show a type-appropriate MDI icon. Hovering reveals download and preview actions.

## Usage

```html
<ue-filepond-preview
  :source="attachments"
  :image-size="64"
  show-file-name
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `source` | `Object\|Array` | required | Single file object or array of file objects. Each entry must have `uuid`, `file_name`, and `created_at` fields |
| `imageSize` | `Number\|String` | `64` | Width and height in pixels for each file card/thumbnail |
| `showFileName` | `Boolean` | `false` | Show the file name below the thumbnail |
| `showInlineFileName` | `Boolean` | `false` | Show the file name inline to the right of the thumbnail (takes full row width) |
| `maxFileNameLength` | `Number` | `10` | Maximum characters shown in the file name before truncation |
| `noOverlay` | `Boolean` | `false` | Disable the hover overlay with download/preview buttons |
| `showDate` | `Boolean` | `false` | Show the `created_at` date on each card |

## Behaviour

- **Images** (jpg, jpeg, png, gif, webp): fetches a preview blob from `/api/filepond/preview/:uuid` and displays it as a `v-img`.
- **Non-images**: displays an appropriate MDI icon (PDF, Word, Excel, or generic document).
- Clicking a previewable file opens a fullscreen dialog with the file in an `<iframe>` or `<v-img>`.
- Clicking a non-previewable file triggers a download via `/api/filepond/download/:uuid`.

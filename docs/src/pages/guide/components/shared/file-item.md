---
sidebarPos: 49
sidebarTitle: File Item
---
# File Item

`ue-file-item` renders a single row in a file attachment table. It shows the file extension icon, name, size, and a remove button. It is used internally by file-upload form fields.

## Usage

```html
<table>
  <tbody>
    <ue-file-item
      v-for="file in files"
      :key="file.id"
      name="attachments[]"
      :item="file"
      :draggable="true"
    />
  </tbody>
</table>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `String` | required | The `name` attribute for the hidden `<input type="hidden">` that submits the file ID |
| `item` | `Object` | `{}` | File object. Supports `id`, `name`, `size`, `extension`, `thumbnail`, `original` keys |
| `draggable` | `Boolean` | `false` | Show a drag handle cell for reordering |
| `itemLabel` | `String` | `'Item'` | Label used in confirmation messages |
| `endpoint` | `String` | `''` | Delete endpoint — called when the remove button is clicked |
| `max` | `Number` | `10` | Maximum number of files allowed (informational) |

## Behaviour

- If `item.extension` is present, an SVG icon matching the extension is shown.
- If `item.thumbnail` is present, a thumbnail `<img>` is shown alongside the file name.
- Clicking the close button removes the row from the file list.

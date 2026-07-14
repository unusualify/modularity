---
sidebarPos: 46
sidebarTitle: Modal Media
---
# Modal Media (Media Library)

`ue-modal-media` opens a fullscreen media library dialog. It combines `ue-filter`, `ue-dropdown-filter`, `ue-uploader`, and a media grid, allowing users to search, filter, upload, and select media files.

## Usage

```html
<ue-modal-media :type="mediaType" @insert="handleInsert">
  <template #activator="{ props }">
    <v-btn v-bind="props">Open Media Library</v-btn>
  </template>
</ue-modal-media>
```

## Key Props

| Prop | Type | Description |
|------|------|-------------|
| `type` | `Object\|String` | Media type configuration or type key. Controls which media types are shown and the upload endpoint |
| `types` | `Array` | Multiple media type tabs — renders type-filter chips |
| `authorized` | `Boolean` | Whether the current user can delete/modify media |
| `connector` | `Boolean` | Whether to show the "Insert" button (used when selecting for a form field) |
| `extraMetadatas` | `Array` | Extra metadata fields shown in the media sidebar |
| `translatableMetadatas` | `Array` | Metadata fields with per-locale values |
| `filterSchema` | `Object\|Array` | `ue-dropdown-filter` schema for advanced filtering |

## Events

| Event | Description |
|-------|-------------|
| `insert` | Emitted with the selected media items when the user confirms their selection |

## Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `activator` | `{ props }` | Element that opens the media library modal |

## Behaviour

- Fetches media from the configured endpoint using `ue-filter` search and `ue-dropdown-filter` advanced filters.
- Uploads are handled by `ue-uploader` and immediately available in the grid after completion.
- The "Insert" footer button only appears when `connector` is `true` and at least one item is selected.
- Closes automatically after Insert is clicked.

---
sidebarPos: 42
sidebarTitle: Uploader
---
# Uploader

`ue-uploader` is the file upload widget used inside the media library modal. It wraps the FineUploader library and supports S3, Azure Blob Storage, and traditional server-side endpoints.

## Usage

This component is used internally by `ue-modal-media`. You do not normally instantiate it directly — open the media library through `ue-modal-media` instead.

```html
<ue-uploader :type="mediaTypeConfig" />
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `type` | `Object` | yes | Media type configuration object. Must contain an `uploaderConfig` key (see below) |

## `uploaderConfig` shape

| Key | Type | Description |
|-----|------|-------------|
| `endpointType` | `String` | `'s3'`, `'azure'`, or `'traditional'` |
| `endpoint` | `String` | Upload endpoint URL |
| `allowedExtensions` | `Array` | Permitted file extensions, e.g. `['jpg', 'png', 'pdf']` |
| `maxFileSize` | `Number` | Maximum file size in bytes |
| `maxConnections` | `Number` | Parallel upload limit (default: 5) |

## Behaviour

- Provides a click-to-browse button and a desktop drop-zone.
- Commits uploaded media to the Vuex media library store (`MEDIA_LIBRARY` mutations) as uploads complete.
- File names are sanitised before upload via `sanitizeFilename`.

::: tip
Upload configuration is generated server-side by the Modularous media library service and passed down to `ue-modal-media`, which forwards it to `ue-uploader`. Refer to the Media Library setup guide for server-side configuration.
:::

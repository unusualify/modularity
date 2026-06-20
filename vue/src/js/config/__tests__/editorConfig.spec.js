import { describe, it, expect } from 'vitest'
import {
  buildEditorConfig,
  buildExtensions,
  DEFAULT_TOOLBAR_ITEMS,
  normalizeToolbarItems,
} from '@/config/editor/editorConfig.js'
import { resolveUploadedImageUrl } from '@/config/editor/editorUpload.js'

describe('editorConfig', () => {
  it('includes source mode, headings, image and table actions', () => {
    expect(DEFAULT_TOOLBAR_ITEMS).toContain('source')
    expect(DEFAULT_TOOLBAR_ITEMS).toContain('heading')
    expect(DEFAULT_TOOLBAR_ITEMS).toContain('image')
    expect(DEFAULT_TOOLBAR_ITEMS).toContain('table')
  })

  it('maps legacy CKEditor toolbar ids to TipTap actions', () => {
    const items = normalizeToolbarItems([
      'sourceEditing',
      'uploadImage',
      'insertTable',
      'bulletedList',
      'numberedList',
    ])

    expect(items).toEqual([
      'source',
      'image',
      'table',
      'bulletList',
      'orderedList',
    ])
  })

  it('builds config with upload url, height and extensions', () => {
    const config = buildEditorConfig({
      height: 360,
      uploadUrl: '/api/upload',
      language: 'en',
    })

    expect(config.uploadUrl).toBe('/api/upload')
    expect(config.language).toBe('en')
    expect(config.height).toBe(360)
    expect(config.toolbarItems).toEqual(DEFAULT_TOOLBAR_ITEMS)
    expect(buildExtensions()).toHaveLength(17)
    expect(config.extensions.length).toBeGreaterThan(0)
  })

  it('merges extra config overrides', () => {
    const config = buildEditorConfig({
      extraConfig: {
        placeholder: 'Start writing',
      },
    })

    expect(config.placeholder).toBe('Start writing')
  })
})

describe('editorUpload', () => {
  it('resolves media library upload responses', () => {
    expect(resolveUploadedImageUrl({
      data: {
        success: true,
        media: {
          original: 'https://cdn.example.com/image.jpg',
        },
      },
    })).toBe('https://cdn.example.com/image.jpg')
  })

  it('falls back to legacy ckeditor response keys', () => {
    expect(resolveUploadedImageUrl({
      data: {
        url: 'https://cdn.example.com/legacy.jpg',
      },
    })).toBe('https://cdn.example.com/legacy.jpg')
  })
})

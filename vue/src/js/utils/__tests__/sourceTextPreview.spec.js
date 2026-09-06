import { describe, expect, it } from 'vitest'
import {
  sourceTextHasPreview,
  sourceTextPreviewHtml,
  sourceTextPreviewSrcdoc,
} from '../sourceTextPreview.js'

describe('sourceTextHasPreview', () => {
  it('is true for md and html only', () => {
    expect(sourceTextHasPreview('md')).toBe(true)
    expect(sourceTextHasPreview('html')).toBe(true)
    expect(sourceTextHasPreview('txt')).toBe(false)
    expect(sourceTextHasPreview('js')).toBe(false)
    expect(sourceTextHasPreview('php')).toBe(false)
    expect(sourceTextHasPreview('json')).toBe(false)
  })
})

describe('sourceTextPreviewHtml', () => {
  it('renders markdown headings', () => {
    const html = sourceTextPreviewHtml('md', '# Hello')
    expect(html).toContain('<h1')
    expect(html).toContain('Hello')
  })

  it('returns html draft unchanged', () => {
    expect(sourceTextPreviewHtml('html', '<p>x</p>')).toBe('<p>x</p>')
  })

  it('returns empty for txt and empty md', () => {
    expect(sourceTextPreviewHtml('txt', 'plain')).toBe('')
    expect(sourceTextPreviewHtml('md', '')).toBe('')
  })
})

describe('sourceTextPreviewSrcdoc', () => {
  it('wraps a document around the fragment', () => {
    const doc = sourceTextPreviewSrcdoc('<p>x</p>')
    expect(doc).toContain('<!DOCTYPE html>')
    expect(doc).toContain('<p>x</p>')
  })
})

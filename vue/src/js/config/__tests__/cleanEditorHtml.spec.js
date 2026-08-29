import { describe, expect, it } from 'vitest'
import {
  cleanEditorHtml,
  isSameEditorHtml,
  normalizeEditorHtml,
} from '@/config/editor/cleanEditorHtml.js'

describe('cleanEditorHtml', () => {
  it('strips empty paragraphs between blocks', () => {
    expect(cleanEditorHtml('<p>Hello</p><p></p><p>World</p>')).toBe('<p>Hello</p><p>World</p>')
  })

  it('strips empty paragraphs that only contain whitespace or br', () => {
    expect(cleanEditorHtml('<p>Hello</p><p>&nbsp;</p><p><br></p><p>World</p>'))
      .toBe('<p>Hello</p><p>World</p>')
  })

  it('keeps paragraphs that contain text', () => {
    expect(cleanEditorHtml('<p>Keep me</p>')).toBe('<p>Keep me</p>')
  })
})

describe('isSameEditorHtml', () => {
  it('treats empty list-item paragraphs as the same document', () => {
    const editorHtml = `
      <p>Intro</p>
      <ul>
        <li>
          <p>
          </p>
        </li>
      </ul>
      <h3>Heading</h3>
    `
    const incomingHtml = cleanEditorHtml(editorHtml)

    expect(editorHtml).not.toBe(incomingHtml)
    expect(isSameEditorHtml(editorHtml, incomingHtml)).toBe(true)
  })

  it('treats an empty editor as equal to an empty string', () => {
    expect(isSameEditorHtml('<p></p>', '')).toBe(true)
    expect(normalizeEditorHtml('<p></p>')).toBe('')
  })

  it('detects a real content change', () => {
    expect(isSameEditorHtml('<p>Hello</p>', '<p>Hello!</p>')).toBe(false)
  })
})

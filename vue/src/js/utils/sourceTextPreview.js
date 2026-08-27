import { marked } from 'marked'
import { normalizeSourceTextFormat, sourceTextAsString } from '@/hooks/useSourceText'

export const SOURCE_TEXT_PREVIEW_FORMATS = Object.freeze(['md', 'html'])

/**
 * @param {unknown} format
 * @returns {boolean}
 */
export function sourceTextHasPreview (format) {
  return SOURCE_TEXT_PREVIEW_FORMATS.includes(normalizeSourceTextFormat(format))
}

/**
 * HTML fragment for the preview pane. {@code html} is the draft as-is (shown in a sandboxed iframe).
 * {@code md} is parsed with {@code marked}. Other formats return empty.
 *
 * @param {unknown} format
 * @param {unknown} text
 * @returns {string}
 */
export function sourceTextPreviewHtml (format, text) {
  const body = sourceTextAsString(text)
  const kind = normalizeSourceTextFormat(format)

  if (kind === 'html') {
    return body
  }

  if (kind !== 'md') {
    return ''
  }

  if (body === '') {
    return ''
  }

  try {
    return marked.parse(body, { async: false, gfm: true }) ?? ''
  } catch {
    return ''
  }
}

/**
 * Full HTML document for a sandboxed iframe (html format).
 *
 * @param {string} innerHtml
 * @returns {string}
 */
export function sourceTextPreviewSrcdoc (innerHtml) {
  return `<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>
html,body{margin:0;padding:0;background:transparent;color:#24292f;font:16px/1.6 system-ui,sans-serif;}
body{padding:8px 4px;}
img{max-width:100%;height:auto;}
</style></head><body>${innerHtml}</body></html>`
}

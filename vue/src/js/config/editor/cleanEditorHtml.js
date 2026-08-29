/**
 * Remove empty paragraphs produced by the editor between block nodes.
 *
 * @param {string|null|undefined} html
 * @returns {string}
 */
export function cleanEditorHtml (html) {
  if (!html?.trim()) {
    return ''
  }

  return html
    .replace(/<p>(?:\s|&nbsp;|&#160;|<br\s*\/?>)*<\/p>/gi, '')
    .trim()
}

/**
 * Canonical HTML the editor emits to v-model.
 *
 * @param {string|null|undefined} html
 * @returns {string}
 */
export function normalizeEditorHtml (html) {
  if (!html || html === '<p></p>') {
    return ''
  }

  return cleanEditorHtml(html)
}

/**
 * Compare editor document HTML with incoming v-model HTML using the same
 * serialization. Raw getHTML() includes empty list <p></p> nodes that
 * cleanEditorHtml strips; treating those as different would call setContent
 * and jump the cursor to the end.
 *
 * @param {string|null|undefined} editorHtml
 * @param {string|null|undefined} incomingHtml
 * @returns {boolean}
 */
export function isSameEditorHtml (editorHtml, incomingHtml) {
  return normalizeEditorHtml(editorHtml) === normalizeEditorHtml(incomingHtml)
}

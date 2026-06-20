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

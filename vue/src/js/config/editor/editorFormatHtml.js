const VOID_ELEMENTS = new Set([
  'area',
  'base',
  'br',
  'col',
  'embed',
  'hr',
  'img',
  'input',
  'link',
  'meta',
  'param',
  'source',
  'track',
  'wbr',
])

/**
 * Pretty-print editor HTML for source mode (legacy CKEditor-like layout).
 *
 * @param {string|null|undefined} html
 * @returns {string}
 */
export function formatEditorHtml (html) {
  if (!html?.trim()) {
    return ''
  }

  const normalized = html
    .replace(/>\s+</g, '><')
    .replace(/>\s+/g, '>')
    .replace(/\s+</g, '<')
    .trim()

  const tokens = normalized
    .split(/(?=<)/g)
    .map((token) => token.trim())
    .filter(Boolean)

  let formatted = ''
  let indent = 0
  const pad = () => '  '.repeat(indent)

  for (const token of tokens) {
    const isClosing = /^<\//.test(token)
    const isSelfClosing = /\/>$/.test(token) || /^<!/.test(token) || /^<\?/.test(token)
    const tagMatch = token.match(/^<\/?([a-zA-Z0-9-]+)/)
    const tagName = tagMatch?.[1]?.toLowerCase()

    if (isClosing) {
      indent = Math.max(indent - 1, 0)
      formatted += `${pad()}${token}\n`
      continue
    }

    formatted += `${pad()}${token}\n`

    if (!isSelfClosing && tagName && !VOID_ELEMENTS.has(tagName) && !token.includes('</')) {
      indent += 1
    }
  }

  return formatted.trimEnd()
}

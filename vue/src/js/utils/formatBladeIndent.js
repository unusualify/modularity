/**
 * Heuristic Blade / HTML segment indentation (not a full parser).
 * Normalizes leading whitespace per line from structural `@directives` and block HTML tags.
 *
 * Limitations: multi-line opening tags without `>` on the first line are not depth-adjusted;
 * inline PHP/JS braces inside `@php` are not re-indented beyond the block level.
 */

const VOID_HTML = new Set([
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

/** Longest token first for prefix checks */
const BLADE_OPEN = [
  '@forelse',
  '@foreach',
  '@verbatim',
  '@component',
  '@section',
  '@prepend',
  '@fragment',
  '@persist',
  '@unless',
  '@isset',
  '@empty',
  '@auth',
  '@guest',
  '@while',
  '@switch',
  '@error',
  '@push',
  '@once',
  '@slot',
  '@for',
  '@if',
  '@php',
].sort((a, b) => b.length - a.length)

const BLADE_CLOSE = [
  '@endforelse',
  '@endforeach',
  '@endverbatim',
  '@endcomponent',
  '@endsection',
  '@endprepend',
  '@endfragment',
  '@endpersist',
  '@endunless',
  '@endisset',
  '@endempty',
  '@endauth',
  '@endguest',
  '@endwhile',
  '@endswitch',
  '@enderror',
  '@endpush',
  '@endonce',
  '@endslot',
  '@endfor',
  '@endif',
  '@endphp',
  '@show',
].sort((a, b) => b.length - a.length)

function escapeRe (s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

function startsWithBladeToken (trimmed, token) {
  const re = new RegExp(`^${escapeRe(token)}(\\b|[\\s(])`)

  return re.test(trimmed)
}

function isBladeOpen (trimmed) {
  return BLADE_OPEN.some((t) => startsWithBladeToken(trimmed, t))
}

function isBladeClose (trimmed) {
  return BLADE_CLOSE.some((t) => startsWithBladeToken(trimmed, t))
}

/** @else / @elseif align with the owning @if (outdent one level for this line only). */
function isBladeMiddle (trimmed) {
  return /^@(else|elseif)(\b|[\s(])/.test(trimmed)
}

function isHtmlClosing (trimmed) {
  return /^<\/[\w:-]+\s*>\s*$/.test(trimmed)
}

/**
 * Single-line opening tag that should increase depth (not void, not self-closed, not open+close on one line).
 */
function isHtmlOpenIncrement (trimmed) {
  if (!trimmed.startsWith('<')) {
    return false
  }
  if (/^<\//.test(trimmed) || /^<!/.test(trimmed) || /^{{\s*--/.test(trimmed)) {
    return false
  }
  if (!trimmed.endsWith('>')) {
    return false
  }
  if (/\/\s*>$/.test(trimmed)) {
    return false
  }

  const m = trimmed.match(/^<([\w:-]+)\b/i)
  if (!m) {
    return false
  }
  const tag = m[1].toLowerCase()
  if (VOID_HTML.has(tag)) {
    return false
  }

  const closeRe = new RegExp(`</${escapeRe(tag)}\\s*>`, 'i')

  return !closeRe.test(trimmed)
}

/**
 * @param {string} source
 * @param {number} [indentLen]
 * @returns {string}
 */
export function formatBladeIndent (source, indentLen = 2) {
  const unitLen = Math.min(Math.max(Number(indentLen) || 2, 1), 8)
  const unit = ' '.repeat(unitLen)
  const text = String(source ?? '').replace(/\r\n/g, '\n')
  const lines = text.split('\n')
  const out = []
  let depth = 0

  for (let i = 0; i < lines.length; i++) {
    const raw = lines[i]
    const trimmedLeft = raw.replace(/^\s+/, '')
    const content = trimmedLeft.replace(/\s+$/, '')

    if (content === '') {
      out.push('')
      continue
    }

    if (isHtmlClosing(content)) {
      depth = Math.max(0, depth - 1)
    } else if (isBladeClose(content)) {
      depth = Math.max(0, depth - 1)
    } else if (isBladeMiddle(content)) {
      depth = Math.max(0, depth - 1)
    }

    out.push(unit.repeat(depth) + content)

    if (isBladeMiddle(content)) {
      depth += 1
    } else if (isHtmlOpenIncrement(content)) {
      depth += 1
    } else if (isBladeOpen(content)) {
      depth += 1
    }
  }

  return out.join('\n')
}

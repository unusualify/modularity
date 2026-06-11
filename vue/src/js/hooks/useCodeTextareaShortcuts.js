import { nextTick } from 'vue'

/** @typedef {{ $el?: HTMLElement }} VueLikeHost */

function unwrapMaybeRefOrGetter (x) {
  if (typeof x === 'function') {
    return x()
  }

  const v =
    typeof x === 'object' &&
    x != null &&
    Object.prototype.hasOwnProperty.call(x, 'value')
      ? x.value
      : x

  return v
}

/**
 * Finds the underlying native textarea behind a Vuetify v-textarea (or similar) host ref.
 *
 * @param {unknown|(() => unknown)} hostLike Getter, Vue Ref, Vue component ({@code .$el}), or HTMLElement
 * @returns {HTMLTextAreaElement|null}
 */
export function resolveNativeTextareaFromVuetifyHost (hostLike) {
  const host = unwrapMaybeRefOrGetter(hostLike)

  if (host == null) {
    return null
  }

  if (host instanceof HTMLTextAreaElement) {
    return host
  }

  const root = '$el' in host && host.$el instanceof HTMLElement ? host.$el : host

  if (root instanceof HTMLTextAreaElement) {
    return root
  }

  if (root != null && typeof root.querySelector === 'function') {
    const ta = root.querySelector('textarea')

    return ta instanceof HTMLTextAreaElement ? ta : null
  }

  return null
}

function clampIndentLen (indentLen, fallback = 2) {
  const n =
    typeof indentLen === 'number' && Number.isFinite(indentLen)
      ? indentLen | 0
      : fallback

  return Math.min(Math.max(n, 1), 16)
}

function indentChunk (indentLen) {
  return ' '.repeat(clampIndentLen(indentLen))
}

function outdentLeadingOneStep (body, indentLen) {
  if (!body.length) {
    return body
  }
  if (body[0] === '\t') {
    return body.slice(1)
  }

  let i = 0
  let rm = 0
  const lim = clampIndentLen(indentLen)
  while (i < body.length && rm < lim && body[i] === ' ') {
    i += 1
    rm += 1
  }

  return body.slice(i)
}

/** @typedef {{ bs: number, beExclusive: number }} LineBlockSlice */

/**
 * Expands `[start,end)` to touched full lines while including the terminating newline slice when applicable.
 *
 * @param {string} text
 * @param {number} start
 * @param {number} end
 * @returns {LineBlockSlice}
 */
export function textareaLineBlockSlice (text, start, end) {
  const lo = Math.min(start, end)
  const hi = Math.max(start, end)

  /** Start of logical line touching {@code lo} */
  const bs = lo <= 0 ? 0 : text.lastIndexOf('\n', lo - 1) + 1

  /** First newline at/after hi, included in exclusive end (or EOF) */
  let beExclusive = text.indexOf('\n', hi)
  if (beExclusive === -1) {
    beExclusive = text.length
  } else {
    beExclusive += 1
  }

  return { bs, beExclusive: Math.max(beExclusive, bs) }
}

/**
 * Applies Tab indent / Shift+Tab outdent inside a plaintext model at `[selStart, selEnd)`.
 *
 * @param {object} p
 * @param {string} p.text
 * @param {number} p.selStart
 * @param {number} p.selEnd
 * @param {boolean} p.shiftTab
 * @param {number} [p.indentLen]
 * @returns {{ text: string, selStart: number, selEnd: number }}
 */
export function applyTabOrShiftTabInText ({
  text,
  selStart,
  selEnd,
  shiftTab,
  indentLen = 2,
}) {
  const unit = clampIndentLen(indentLen)
  const indent = indentChunk(unit)

  /** Collapsed caret: Tab inserts spaces • Shift+Tab outdents the current logical line once */
  if (selStart === selEnd) {
    const caret = selStart
    if (!shiftTab) {
      const next = `${text.slice(0, caret)}${indent}${text.slice(caret)}`
      const c = caret + indent.length

      return { text: next, selStart: c, selEnd: c }
    }

    /** Shift+Tab with collapsed caret: only the caret line */
    const { bs } = textareaLineBlockSlice(text, caret, caret)
    const nl = text.indexOf('\n', bs)
    const lineEndExclusive = nl === -1 ? text.length : nl
    const prefix = text.slice(0, bs)
    const body = text.slice(bs, lineEndExclusive)
    const suffix = nl === -1 ? '' : text.slice(nl)

    const nextBody = outdentLeadingOneStep(body, unit)
    const shrink = body.length - nextBody.length
    const nextText = `${prefix}${nextBody}${suffix}`

    /** If caret was inside stripped leading whitespace, snap to indent start */
    const rel = caret - bs
    const caretNext = rel <= shrink ? bs : caret - shrink
    const c = Math.min(Math.max(caretNext, bs), bs + nextBody.length)

    return { text: nextText, selStart: c, selEnd: c }
  }

  /** Expanded selection spanning full touched lines within the textarea model */
  const { bs, beExclusive } = textareaLineBlockSlice(text, selStart, selEnd)

  /** Partial-line selections clamp to IDE-style full touched lines */
  const fragment = text.slice(bs, beExclusive)

  /** Empty slice */
  if (!fragment.length) {
    return { text, selStart, selEnd }
  }

  const lines = fragment.split('\n')

  /** Mutate each logical line touched by expansion */
  const nextLines =
    shiftTab === true ? lines.map((l) => outdentLeadingOneStep(l, unit)) : lines.map((l) => `${indent}${l}`)

  const nextFrag = nextLines.join('\n')
  const full = `${text.slice(0, bs)}${nextFrag}${text.slice(beExclusive)}`

  /** Uniform selection over the mutated line block */
  const toExclusive = bs + nextFrag.length

  return {
    text: full,
    selStart: bs,
    selEnd: toExclusive,
  }
}

async function restoreTextareaCaret (resolveHost, selStart, selEnd) {
  await nextTick()
  const ta = resolveNativeTextareaFromVuetifyHost(resolveHost)

  try {
    if (ta instanceof HTMLTextAreaElement) {
      ta.focus()
      ta.selectionStart = selStart
      ta.selectionEnd = selEnd
    }
  } catch {
    /** Some embedded browsers disallow imperative selection restores */
  }
}

/**
 * Factory for monospace/code textareas:
 * captures plain Tab indents/outdents plus optional `{@code Alt+Shift+F/M/C}` (practical),
 * retaining legacy `{@code ⌘|Ctrl + Alt + F/M/C}`.
 *
 * Tab / Shift+Tab uses `{@code preventDefault()}`, so Ctrl/Meta/Alt Tab chords still behave normally.
 *
 * @param {object} opts
 * @param {(() => unknown)} opts.resolveHost Vuetify ref host (or HTMLElement)
 * @param {() => string} opts.getText
 * @param {(next: string) => void} opts.setText
 * @param {() => number} [opts.getIndentLen]
 * @param {object} [opts.actions]
 * @param {() => void} [opts.actions.formatPretty]
 * @param {() => void} [opts.actions.formatMinify]
 * @param {() => void|Promise<void>} [opts.actions.copy]
 */

/**
 * @returns {(evt: KeyboardEvent) => void}
 */
export function createCodeTextareaKeydownHandler (opts) {
  const resolveHost = typeof opts.resolveHost === 'function' ? opts.resolveHost : () => opts.resolveHost
  const getText = opts.getText
  const setText = opts.setText

  let getIndentLen = opts.getIndentLen
  getIndentLen = typeof getIndentLen === 'function' ? getIndentLen : () => 2

  const actions = opts.actions ?? {}

  return (evt) => {
    /** IME */
    if (evt.isComposing === true || evt.keyCode === 229) {
      return
    }

    const ta0 = resolveNativeTextareaFromVuetifyHost(resolveHost)
    if (ta0 == null || ta0.disabled === true || ta0.readOnly === true) {
      return
    }

    /** Prevent default roam order • treat Tab purely as indentation */
    if (evt.key === 'Tab' && evt.ctrlKey === false && evt.metaKey === false && evt.altKey === false) {
      evt.preventDefault()

      const cur = typeof getText === 'function' ? getText() : ta0.value
      const ss = ta0.selectionStart
      const se = ta0.selectionEnd

      const res = applyTabOrShiftTabInText({
        text: String(cur ?? ''),
        selStart: ss,
        selEnd: se,
        shiftTab: evt.shiftKey === true,
        indentLen: getIndentLen(),
      })

      if (typeof setText === 'function' && res.text !== cur) {
        setText(res.text)
      }

      /** Caret survives Vuetify re-render after programmatic v-model hops */
      void restoreTextareaCaret(resolveHost, res.selStart, res.selEnd)

      return
    }

    /** ----- Code toolbar shortcuts ----- */
    if (!evt.altKey || evt.repeat === true) {
      return
    }

    const k = evt.key?.toLowerCase?.() ?? ''
    /** Limited key set avoids dead-key combos */
    if (k !== 'f' && k !== 'm' && k !== 'c') {
      return
    }

    const cmdOrCtrlMod = evt.ctrlKey === true || evt.metaKey === true
    const practicalChord = evt.shiftKey === true && cmdOrCtrlMod === false
    const legacyChord = evt.shiftKey !== true && cmdOrCtrlMod === true

    if (!practicalChord && !legacyChord) {
      return
    }

    if (typeof actions !== 'object' || actions === null) {
      return
    }

    if (k === 'f') {
      if (typeof actions.formatPretty !== 'function') {
        return
      }
      evt.preventDefault()

      actions.formatPretty()

      return
    }

    if (k === 'm') {
      if (typeof actions.formatMinify !== 'function') {
        return
      }
      evt.preventDefault()

      actions.formatMinify()

      return
    }

    if (k === 'c') {
      if (typeof actions.copy !== 'function') {
        return
      }
      evt.preventDefault()

      void actions.copy()

      return
    }
  }
}

/**
 * Composable façade wrapping {@link createCodeTextareaKeydownHandler}.
 *
 * @param {Parameters<typeof createCodeTextareaKeydownHandler>[0]} opts
 */

export function useCodeTextareaShortcuts (opts) {
  return {
    onCodeTextareaKeydown: createCodeTextareaKeydownHandler(opts),
  }

}

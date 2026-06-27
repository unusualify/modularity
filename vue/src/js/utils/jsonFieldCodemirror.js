/**
 * CodeMirror 6 setup for JsonField: JSON syntax, gutter fold/unfold, tab indent, toolbar chords.
 */
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { json } from '@codemirror/lang-json'
import {
  bracketMatching,
  defaultHighlightStyle,
  ensureSyntaxTree,
  foldEffect,
  foldGutter,
  foldInside,
  foldKeymap,
  forceParsing,
  indentOnInput,
  indentUnit,
  syntaxHighlighting,
  syntaxTree,
  syntaxTreeAvailable,
} from '@codemirror/language'
import { EditorSelection, EditorState, Prec } from '@codemirror/state'
import {
  drawSelection,
  dropCursor,
  EditorView,
  highlightActiveLine,
  highlightActiveLineGutter,
  keymap,
  lineNumbers,
  rectangularSelection,
} from '@codemirror/view'

/**
 * @param {object} opts
 * @param {number} opts.indentLen
 * @param {boolean} opts.readOnly
 * @param {(text: string) => void} opts.onDocChange
 * @param {() => void} opts.onFocus
 * @param {() => void} opts.onBlur
 * @param {() => void} opts.formatPretty
 * @param {() => void} opts.formatMinify
 * @param {() => void | Promise<void>} opts.copyToClipboard
 */
export function buildJsonFieldCodemirrorExtensions (opts) {
  const indentLen = Math.min(Math.max(Number(opts.indentLen) || 2, 1), 16)
  const space = ' '.repeat(indentLen)
  return [
    lineNumbers(),
    highlightActiveLine(),
    highlightActiveLineGutter(),
    drawSelection(),
    dropCursor(),
    rectangularSelection(),
    indentUnit.of(space),
    bracketMatching(),
    foldGutter(),
    indentOnInput(),
    json(),
    syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
    history(),
    keymap.of([indentWithTab]),
    keymap.of(foldKeymap),
    keymap.of(historyKeymap),
    keymap.of(defaultKeymap),
    Prec.highest(
      keymap.of([
        {
          key: 'Mod-Alt-f',
          run: () => {
            opts.formatPretty()

            return true
          },
        },
        {
          key: 'Alt-Shift-f',
          run: () => {
            opts.formatPretty()

            return true
          },
        },
        {
          key: 'Mod-Alt-m',
          run: () => {
            opts.formatMinify()

            return true
          },
        },
        {
          key: 'Alt-Shift-m',
          run: () => {
            opts.formatMinify()

            return true
          },
        },
        {
          key: 'Mod-Alt-c',
          run: () => {
            void opts.copyToClipboard()

            return true
          },
        },
        {
          key: 'Alt-Shift-c',
          run: () => {
            void opts.copyToClipboard()

            return true
          },
        },
      ]),
    ),
    EditorState.readOnly.of(Boolean(opts.readOnly)),
    EditorView.lineWrapping,
    EditorView.domEventHandlers({
      focus: () => {
        opts.onFocus?.()
      },
      blur: () => {
        opts.onBlur?.()
      },
      /**
       * VTextarea wires VField @click → onControlClick → textarea.focus(). That steals focus from
       * CodeMirror after mousedown already placed the caret, so the selection jumps (often “down”).
       */
      mousedown: (e) => {
        e.stopPropagation()
      },
      click: (e) => {
        e.stopPropagation()
      },
      dblclick: (e) => {
        e.stopPropagation()
      },
    }),
    EditorView.updateListener.of((u) => {
      if (!u.docChanged) {
        return
      }
      opts.onDocChange(u.state.doc.toString())
    }),
    EditorView.theme(
      {
        '&': {
          height: '100%',
          /** Match Vuetify v-field (16px); fixed 13px broke posAtCoords vs visible lines. */
          fontSize: 'inherit',
          lineHeight: '1.45',
          color: 'rgb(var(--v-theme-on-surface))',
        },
        '.cm-scroller': {
          fontFamily:
            'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
          fontSize: 'inherit',
          lineHeight: 'inherit',
        },
        '.cm-content': {
          caretColor: 'rgb(var(--v-theme-primary))',
          fontFamily: 'inherit',
        },
        '.cm-line': {
          lineHeight: 'inherit',
        },
        /** drawSelection: default black / light gray is invisible on dark Vuetify surfaces */
        '.cm-cursor, .cm-dropCursor': {
          borderLeft: '2px solid rgb(var(--v-theme-primary))',
          marginLeft: '-1px',
        },
        '.cm-selectionBackground': {
          backgroundColor: 'rgba(var(--v-theme-primary), 0.22)',
        },
        '&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground': {
          backgroundColor: 'rgba(var(--v-theme-primary), 0.28)',
        },
        '.cm-gutters': {
          backgroundColor: 'rgb(var(--v-theme-surface))',
          color: 'rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity))',
          border: 'none',
          borderRight: 'thin solid rgba(var(--v-border-color), var(--v-border-opacity))',
        },
        '.cm-activeLineGutter': {
          backgroundColor: 'rgba(var(--v-theme-primary), 0.08)',
        },
      },
      { dark: false },
    ),
  ]
}

/** Retries when the JSON tree is not fully parsed yet (large docs). */
const jsonFoldRetryCount = new WeakMap()

/** Single pending rAF per view so retries do not stack and fight user clicks. */
const jsonFoldRetryRafId = new WeakMap()

/**
 * Cancel scheduled fold retries (e.g. before destroying the editor).
 * @param {import('@codemirror/view').EditorView} view
 */
export function cancelJsonFieldFoldRetries (view) {
  const id = jsonFoldRetryRafId.get(view)
  if (id != null) {
    cancelAnimationFrame(id)
    jsonFoldRetryRafId.delete(view)
  }
  jsonFoldRetryCount.delete(view)
}

/**
 * Fold JSON Object/Array nodes whose nesting depth is >= minDepth (root object = 1).
 * Waits for a complete Lezer parse so sibling nodes at the same depth are all included.
 * Does not move the selection while the editor is focused, so delayed parse retries do not
 * override mouse placement of the caret.
 * @param {import('@codemirror/view').EditorView} view
 * @param {number} minDepthFold
 */
export function applyInitialJsonFolds (view, minDepthFold) {
  const d = Math.min(Math.max(Math.floor(Number(minDepthFold)) || 3, 1), 64)
  let state = view.state

  try {
    forceParsing(view, state.doc.length, 25000)
  } catch {
    /** ignore */
  }
  state = view.state

  for (let i = 0; i < 120; i++) {
    try {
      ensureSyntaxTree(state, state.doc.length, 500)
    } catch {
      break
    }
    if (syntaxTreeAvailable(state, state.doc.length)) {
      break
    }
    state = view.state
  }

  if (!syntaxTreeAvailable(state, state.doc.length)) {
    const n = (jsonFoldRetryCount.get(view) ?? 0) + 1
    if (n <= 16) {
      jsonFoldRetryCount.set(view, n)
      const prev = jsonFoldRetryRafId.get(view)
      if (prev != null) {
        cancelAnimationFrame(prev)
      }
      const id = requestAnimationFrame(() => {
        jsonFoldRetryRafId.delete(view)
        applyInitialJsonFolds(view, minDepthFold)
      })
      jsonFoldRetryRafId.set(view, id)
    }

    return
  }
  jsonFoldRetryCount.delete(view)
  const pendingRaf = jsonFoldRetryRafId.get(view)
  if (pendingRaf != null) {
    cancelAnimationFrame(pendingRaf)
    jsonFoldRetryRafId.delete(view)
  }

  const tree = syntaxTree(state)
  if (!tree || tree.length === 0) {
    return
  }

  const ranges = []
  function walk (node, depthObjArray) {
    let cur = depthObjArray
    const nm = node.type.name
    if (nm === 'Object' || nm === 'Array') {
      cur += 1
      if (cur >= d) {
        const fr = foldInside(node)
        if (fr && fr.from < fr.to) {
          ranges.push(fr)
        }
      }
    }
    for (let ch = node.firstChild; ch; ch = ch.nextSibling) {
      walk(ch, cur)
    }
  }
  walk(tree.topNode, 0)

  if (!ranges.length) {
    return
  }

  const seen = new Set()
  const uniq = []
  for (const r of ranges) {
    const k = `${r.from}:${r.to}`
    if (seen.has(k)) {
      continue
    }
    seen.add(k)
    uniq.push(r)
  }
  uniq.sort((a, b) => b.from - a.from)

  const effects = uniq.map((r) => foldEffect.of(r))
  /** While focused, never override selection — rAF parse retries were stealing the caret after click. */
  if (!view.hasFocus) {
    const anchor = Math.min(state.selection.main.head, Math.max(0, state.doc.length))
    const safeHead = uniq.some((r) => r.from < anchor && r.to > anchor) ? 0 : anchor
    view.dispatch({
      selection: EditorSelection.cursor(safeHead),
      effects,
    })

    return
  }

  view.dispatch({ effects })
}

/**
 * @param {import('@codemirror/view').EditorView} view
 * @param {string} text
 * @returns {boolean} true if document was replaced
 */
export function setCodemirrorDoc (view, text) {
  const cur = view.state.doc.toString()
  if (cur === text) {
    return false
  }
  view.dispatch({
    changes: { from: 0, to: view.state.doc.length, insert: text },
  })

  return true
}

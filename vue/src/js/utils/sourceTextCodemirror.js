/**
 * CodeMirror 6 for SourceText: language from schema {@code format} (md|txt|js|php|html).
 */
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { html } from '@codemirror/lang-html'
import { javascript } from '@codemirror/lang-javascript'
import { markdown } from '@codemirror/lang-markdown'
import { php } from '@codemirror/lang-php'
import {
  bracketMatching,
  defaultHighlightStyle,
  foldGutter,
  foldKeymap,
  indentOnInput,
  indentUnit,
  syntaxHighlighting,
} from '@codemirror/language'
import { EditorState } from '@codemirror/state'
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
import { normalizeSourceTextFormat } from '@/hooks/useSourceText'

/**
 * Language support for a source-text format. {@code txt} has no highlighter.
 *
 * @param {unknown} format
 * @returns {import('@codemirror/state').Extension}
 */
export function sourceTextLanguageExtension (format) {
  switch (normalizeSourceTextFormat(format)) {
    case 'html':
      return html()
    case 'js':
      return javascript()
    case 'php':
      return php()
    case 'txt':
      return []
    case 'md':
    default:
      return markdown()
  }
}

/**
 * @param {object} opts
 * @param {unknown} opts.format
 * @param {boolean} [opts.readOnly]
 * @param {number} [opts.indentLen]
 * @param {(text: string) => void} opts.onDocChange
 */
export function buildSourceTextCodemirrorExtensions (opts) {
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
    sourceTextLanguageExtension(opts.format),
    syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
    history(),
    keymap.of([indentWithTab]),
    keymap.of(foldKeymap),
    keymap.of(historyKeymap),
    keymap.of(defaultKeymap),
    EditorState.readOnly.of(Boolean(opts.readOnly)),
    EditorView.lineWrapping,
    EditorView.updateListener.of((u) => {
      if (!u.docChanged) {
        return
      }
      opts.onDocChange(u.state.doc.toString())
    }),
    EditorView.theme(
      {
        '&': {
          height: 'auto',
          minHeight: '16rem',
          fontSize: '0.875rem',
          lineHeight: '1.5',
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

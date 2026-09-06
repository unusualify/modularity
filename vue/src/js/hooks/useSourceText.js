import { computed, ref, toRef, watch } from 'vue'

export const SOURCE_TEXT_FORMATS = Object.freeze(['md', 'txt', 'js', 'php', 'html', 'json'])

export const SOURCE_TEXT_JSON_INDENT = 2

/**
 * @param {unknown} format
 * @returns {'md'|'txt'|'js'|'php'|'html'|'json'}
 */
export function normalizeSourceTextFormat (format) {
  const value = String(format ?? 'md').toLowerCase()

  return SOURCE_TEXT_FORMATS.includes(value) ? value : 'md'
}

/**
 * @param {unknown} indent
 * @returns {number}
 */
export function normalizeSourceTextJsonIndent (indent) {
  const n = Number(indent)

  if (!Number.isFinite(n)) {
    return SOURCE_TEXT_JSON_INDENT
  }

  return Math.min(Math.max(Math.floor(n), 1), 16)
}

/**
 * @param {unknown} value
 * @returns {string}
 */
export function sourceTextAsString (value) {
  if (value == null) {
    return ''
  }

  return String(value)
}

/**
 * Pretty JSON for the modal draft. Objects/arrays are stringified; valid JSON
 * strings are re-pretty-printed; invalid strings are left as-is so the editor
 * can keep a fixable document.
 *
 * @param {unknown} value
 * @param {number} [indent]
 * @returns {string}
 */
export function sourceTextJsonToDraft (value, indent = SOURCE_TEXT_JSON_INDENT) {
  const spaces = normalizeSourceTextJsonIndent(indent)

  if (value == null) {
    return ''
  }

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (trimmed === '') {
      return ''
    }
    try {
      return JSON.stringify(JSON.parse(trimmed), null, spaces)
    } catch {
      return value
    }
  }

  try {
    return JSON.stringify(value, null, spaces)
  } catch {
    return String(value)
  }
}

/**
 * Compact one-line preview for the closed field (JSON-LD first line is otherwise `{`).
 *
 * @param {unknown} value
 * @returns {string}
 */
export function sourceTextJsonClosedPreview (value) {
  if (value == null) {
    return ''
  }

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (trimmed === '') {
      return ''
    }
    try {
      return JSON.stringify(JSON.parse(trimmed))
    } catch {
      return sourceTextFirstLine(value)
    }
  }

  try {
    return JSON.stringify(value)
  } catch {
    return ''
  }
}

/**
 * @param {unknown} text
 * @returns {{ ok: true, value: unknown } | { ok: false, error: string }}
 */
export function parseSourceTextJsonDraft (text) {
  const trimmed = String(text ?? '').trim()

  if (trimmed === '') {
    return { ok: true, value: null }
  }

  try {
    return { ok: true, value: JSON.parse(trimmed) }
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : 'Invalid JSON',
    }
  }
}

/**
 * @param {unknown} text
 * @param {number | 0} indent  `0` minifies; otherwise pretty-print width
 * @returns {{ ok: true, text: string } | { ok: false, error: string }}
 */
export function formatSourceTextJsonDraft (text, indent = SOURCE_TEXT_JSON_INDENT) {
  const parsed = parseSourceTextJsonDraft(text)

  if (!parsed.ok) {
    return parsed
  }

  if (parsed.value === null) {
    return { ok: true, text: '' }
  }

  try {
    const pretty = indent === 0
      ? JSON.stringify(parsed.value)
      : JSON.stringify(parsed.value, null, normalizeSourceTextJsonIndent(indent))

    return { ok: true, text: pretty }
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : 'Invalid JSON',
    }
  }
}

/**
 * First line of committed source (no trailing newline).
 *
 * @param {unknown} value
 * @returns {string}
 */
export function sourceTextFirstLine (value) {
  const text = sourceTextAsString(value)

  if (text === '') {
    return ''
  }

  const match = text.match(/^[^\r\n]*/)

  return match ? match[0] : ''
}

/**
 * @param {unknown} value
 * @returns {number}
 */
export function sourceTextLineCount (value) {
  const text = sourceTextAsString(value)

  if (text === '') {
    return 0
  }

  return text.split(/\r\n|\n|\r/).length
}

/**
 * Modal source editor: draft is local until Keep; closed-field rules run on committed modelValue.
 *
 * {@code format: 'json'} Keep emits a parsed value (object/array/primitive) or {@code null}
 * when empty, so Laravel {@code array}/{@code json} casts are not double-encoded.
 *
 * @param {object} props
 * @param {(event: string, ...args: unknown[]) => void} emit
 */
export default function useSourceText (props, emit) {
  const dialogOpen = ref(false)
  const confirmDiscard = ref(false)
  const draft = ref('')
  const pane = ref('edit')
  const parseError = ref('')

  const format = computed(() => normalizeSourceTextFormat(toRef(props, 'format').value))
  const jsonIndent = computed(() => normalizeSourceTextJsonIndent(props.jsonIndent))
  const hasPreview = computed(() => format.value === 'md' || format.value === 'html')
  const committed = computed(() => {
    const value = props.modelValue ?? props.default ?? (format.value === 'json' ? null : '')

    return format.value === 'json'
      ? sourceTextJsonToDraft(value, jsonIndent.value)
      : sourceTextAsString(value)
  })
  const firstLine = computed(() => (
    format.value === 'json'
      ? sourceTextJsonClosedPreview(props.modelValue ?? props.default ?? null)
      : sourceTextFirstLine(committed.value)
  ))
  const lineCount = computed(() => sourceTextLineCount(committed.value))
  const chipLabel = computed(() => `${lineCount.value} · ${format.value}`)
  const isDirty = computed(() => draft.value !== committed.value)
  const isDisabled = computed(() => Boolean(props.disabled || props.readonly))

  watch(draft, () => {
    if (parseError.value !== '') {
      parseError.value = ''
    }
  })

  /**
   * Vuetify rules for the closed field: evaluate against committed text, not the first-line preview.
   */
  const closedRules = computed(() => {
    const rules = props.rules

    if (!Array.isArray(rules)) {
      return rules ?? []
    }

    return rules.map((rule) => {
      if (typeof rule !== 'function') {
        return rule
      }

      return () => rule(committed.value)
    })
  })

  function openEditor () {
    if (isDisabled.value) {
      return
    }

    draft.value = committed.value
    confirmDiscard.value = false
    parseError.value = ''
    pane.value = 'edit'
    dialogOpen.value = true
  }

  function closeEditor () {
    dialogOpen.value = false
    confirmDiscard.value = false
    parseError.value = ''
  }

  function requestClose () {
    if (isDirty.value) {
      confirmDiscard.value = true
      dialogOpen.value = true

      return
    }

    closeEditor()
  }

  function onDialogUpdate (open) {
    if (open) {
      dialogOpen.value = true

      return
    }

    requestClose()
  }

  function applyJsonFormat (indent) {
    const result = formatSourceTextJsonDraft(draft.value, indent)

    if (!result.ok) {
      parseError.value = result.error

      return
    }

    parseError.value = ''
    draft.value = result.text
  }

  function formatPretty () {
    applyJsonFormat(jsonIndent.value)
  }

  function formatMinify () {
    applyJsonFormat(0)
  }

  function keep () {
    if (format.value === 'json') {
      const result = parseSourceTextJsonDraft(draft.value)

      if (!result.ok) {
        parseError.value = result.error

        return
      }

      parseError.value = ''
      emit('update:modelValue', result.value)
      emit('change', result.value)
      closeEditor()

      return
    }

    emit('update:modelValue', draft.value)
    emit('change', draft.value)
    closeEditor()
  }

  function discard () {
    draft.value = committed.value
    closeEditor()
  }

  function stayEditing () {
    confirmDiscard.value = false
    dialogOpen.value = true
  }

  return {
    dialogOpen,
    confirmDiscard,
    draft,
    pane,
    committed,
    format,
    jsonIndent,
    hasPreview,
    firstLine,
    lineCount,
    chipLabel,
    isDirty,
    isDisabled,
    closedRules,
    parseError,
    openEditor,
    closeEditor,
    requestClose,
    onDialogUpdate,
    formatPretty,
    formatMinify,
    keep,
    discard,
    stayEditing,
  }
}

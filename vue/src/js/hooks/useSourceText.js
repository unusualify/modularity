import { computed, ref, toRef } from 'vue'

export const SOURCE_TEXT_FORMATS = Object.freeze(['md', 'txt', 'js', 'php', 'html'])

/**
 * @param {unknown} format
 * @returns {'md'|'txt'|'js'|'php'|'html'}
 */
export function normalizeSourceTextFormat (format) {
  const value = String(format ?? 'md').toLowerCase()

  return SOURCE_TEXT_FORMATS.includes(value) ? value : 'md'
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
 * @param {object} props
 * @param {(event: string, ...args: unknown[]) => void} emit
 */
export default function useSourceText (props, emit) {
  const dialogOpen = ref(false)
  const confirmDiscard = ref(false)
  const draft = ref('')
  const pane = ref('edit')

  const format = computed(() => normalizeSourceTextFormat(toRef(props, 'format').value))
  const hasPreview = computed(() => format.value === 'md' || format.value === 'html')
  const committed = computed(() => sourceTextAsString(props.modelValue ?? props.default ?? ''))
  const firstLine = computed(() => sourceTextFirstLine(committed.value))
  const lineCount = computed(() => sourceTextLineCount(committed.value))
  const chipLabel = computed(() => `${lineCount.value} · ${format.value}`)
  const isDirty = computed(() => draft.value !== committed.value)
  const isDisabled = computed(() => Boolean(props.disabled || props.readonly))

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
    pane.value = 'edit'
    dialogOpen.value = true
  }

  function closeEditor () {
    dialogOpen.value = false
    confirmDiscard.value = false
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

  function keep () {
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
    hasPreview,
    firstLine,
    lineCount,
    chipLabel,
    isDirty,
    isDisabled,
    closedRules,
    openEditor,
    closeEditor,
    requestClose,
    onDialogUpdate,
    keep,
    discard,
    stayEditing,
  }
}

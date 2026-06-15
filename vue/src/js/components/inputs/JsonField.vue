<!--
  Structured JSON editing for form models (object/array). Validates JSON syntax for Vuetify + blocks submit while invalid.

  Toolbar: prettify/minify/copy and preset inserts. While the textarea is focused:
  • Tab / Shift+Tab — indent/outdent JSON with spaces aligned to JsonField indent settings
  • Alt+Shift+F / M / C — format • minify • copy (recommended)
  • ⌘ or Ctrl + Alt + F / M / C — same actions (legacy)

  JSON is edited in CodeMirror 6 (gutter fold on the JSON text). Prop jsonCmFoldFromDepth (default 3): Object/Array at that nesting level and deeper start folded; overridable via schema.
-->
<template>
  <div class="ue-input-json-field">
    <v-alert
      v-if="jsonFieldSubtitle"
      border="start"
      class="mb-2 text-body-2"
      density="compact"
      prominent
      type="info"
      variant="tonal"
    >
      {{ jsonFieldSubtitle }}
    </v-alert>

    <div v-if="variantChipsNormalized.length" class="d-flex flex-wrap ga-2 mb-2 align-start">
      <span class="text-caption text-medium-emphasis text-no-wrap mt-1">
        {{ variantChipsLabel }}
      </span>
      <div class="d-flex flex-wrap ga-1">
        <v-tooltip v-for="(chip, ix) in variantChipsNormalized" :key="'vc-' + ix" location="top" max-width="360">
          <template #activator="{ props }">
            <v-chip size="small" variant="tonal" color="primary" class="cursor-default" v-bind="props">{{ chip.label }}</v-chip>
          </template>
          <div class="text-caption">{{ chip.detail }}</div>
        </v-tooltip>
      </div>
    </div>

    <v-expansion-panels
      v-if="guidePanelsNormalized.length"
      v-model="guideOpen"
      variant="accordion"
      class="ue-input-json-field__guide mb-2 border rounded"
      elevation="0"
      multiple
    >
      <v-expansion-panel
        v-for="(panel, ix) in guidePanelsNormalized"
        :key="'gp-' + ix"
        rounded="lg"
      >
        <v-expansion-panel-title class="text-body-2 py-3 min-h-auto">
          {{ panel.title }}
        </v-expansion-panel-title>
        <v-expansion-panel-text class="text-body-2 text-medium-emphasis">
          <div class="ue-json-field-guide-body">{{ panel.body }}</div>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

    <div class="d-flex flex-wrap align-center ga-2 mb-2 ue-input-json-field__toolbar">
      <v-btn
        size="small"
        variant="tonal"
        color="primary"
        prepend-icon="mdi-code-braces"
        @click="formatPretty"
      >
        {{ $t('fields.json_toolbar_format', 'Format') }}
      </v-btn>
      <v-btn
        size="small"
        variant="tonal"
        prepend-icon="mdi-arrow-collapse-horizontal"
        @click="formatMinify"
      >
        {{ $t('fields.json_toolbar_minify', 'Minify') }}
      </v-btn>
      <v-btn
        size="small"
        variant="text"
        prepend-icon="mdi-content-copy"
        @click="copyToClipboard"
      >
        {{ $t('fields.json_toolbar_copy', 'Copy') }}
      </v-btn>

      <v-menu v-if="allSnippets.length" location="bottom" :close-on-content-click="true">
        <template #activator="{ props: menuProps }">
          <v-btn
            v-bind="menuProps"
            size="small"
            variant="text"
            append-icon="mdi-chevron-down"
            prepend-icon="mdi-lightning-bolt"
          >
            {{ $t('fields.json_toolbar_snippets', 'Quick insert') }}
          </v-btn>
        </template>
        <v-list density="compact" class="pa-1" max-width="360">
          <v-list-item
            v-for="(row, ix) in allSnippets"
            :key="ix"
            rounded="sm"
            @click="applySnippet(row)"
          >
            <v-list-item-title class="text-body-2">{{ row.label }}</v-list-item-title>
            <v-list-item-subtitle v-if="row.hint">{{ row.hint }}</v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </v-menu>

      <span class="text-caption text-medium-emphasis ms-auto d-none d-sm-inline">
        {{ $t('fields.json_shortcuts_hint', 'Tab / Shift+Tab: indent • Alt+Shift + F/M/C • also ⌘/Ctrl+Alt+F/M/C') }}
      </span>
    </div>

    <v-textarea
      ref="VInput"
      v-model="draftText"
      v-bind="textareaBind"
      :rules="mergedRules"
      class="font-mono ue-input-json-field__textarea ue-json-cm-host"
      spellcheck="false"
      autocomplete="off"
      autocorrect="off"
    />
  </div>
</template>

<script setup>
  import { computed, nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { debounce } from 'lodash-es'
  import { omit } from 'lodash-es'
  import { EditorState } from '@codemirror/state'
  import { EditorView } from '@codemirror/view'
  import { makeInputProps, makeInputEmits } from '@/hooks'
  import {
    applyInitialJsonFolds,
    buildJsonFieldCodemirrorExtensions,
    cancelJsonFieldFoldRetries,
    setCodemirrorDoc,
  } from './jsonFieldCodemirror.js'

  defineOptions({
    name: 'v-input-json-field'
  })

  const emit = defineEmits([...makeInputEmits])

  const props = defineProps({
    ...makeInputProps(),
    rules: {
      type: [String, Array],
      default: undefined
    },
    rows: {
      type: Number,
      default: 14
    },
    maxRows: {
      type: Number,
      default: 36
    },
    autoGrow: {
      type: Boolean,
      default: true
    },
    jsonIndent: {
      type: Number,
      default: 2
    },
    /**
     * Hydrate / schema list: {@code { label, hint?, insert } }.
     */
    jsonSnippets: {
      type: Array,
      default: () => []
    },
    validateOn: {
      type: String,
      default: 'blur lazy'
    },
    variant: {
      type: String,
      default: 'outlined'
    },
    density: {
      type: String,
      default: 'comfortable'
    },
    persistentHint: {
      type: Boolean,
      default: true
    },
    /** Kısa üst özet; schema ile de verilebilir. */
    jsonFieldSubtitle: {
      type: String,
      default: ''
    },
    /** {@code [{ label, detail }]} — araç çipleri ve tooltip açıklamaları */
    jsonFieldVariantChips: {
      type: Array,
      default: () => []
    },
    /** {@code [{ title, body }]} — genişleyen kılavuz (düz metin, newline ile) */
    jsonFieldGuideSections: {
      type: Array,
      default: () => []
    },
    /** Kılavuz panel başlığı (tek panelde kullanılır) */
    jsonFieldGuideTitle: {
      type: String,
      default: ''
    },
    /**
     * CodeMirror: kök JSON objesi/dizisi derinlik 1 sayılır; bu seviye ve üzerindeki Object/Array düğümleri
     * ilk yüklemede / dışarıdan metin gelince varsayılan olarak katlanır (gutter fold).
     */
    jsonCmFoldFromDepth: {
      type: Number,
      default: 3
    }
  })

  const { t } = useI18n({ useScope: 'global' })
  const VInput = ref(null)
  const jsonEditorView = shallowRef(null)

  const draftText = ref('')
  const syncingDraft = ref(false)
  const focused = ref(false)

  let lastEmittedSignature = ''

  function flushDraftValidation () {
    nextTick(() => {
      nextTick(() => {
        VInput.value?.resetValidation?.()
        VInput.value?.validate?.()
      })
    })
  }

  const effectiveSchema = computed(() => {
    const nested = props.obj?.schema
    if (nested && typeof nested === 'object' && Object.keys(nested).length > 0) {
      return nested
    }
    return omit(props, [
      'modelValue',
      'obj',
      'hideIfEmpty',
      'default',
      'protectInitialValue',
      'isEditing',
      'editable',
      'creatable',
      'rules',
      'jsonFieldSubtitle',
      'jsonFieldVariantChips',
      'jsonFieldGuideSections',
      'jsonFieldGuideTitle',
      'jsonCmFoldFromDepth',
    ])
  })

  const textareaBind = computed(() => {
    const s = effectiveSchema.value
    return omit(
      {
        label: props.label ?? s.label,
        hint: s.hint,
        persistentHint: s.persistentHint ?? props.persistentHint,
        rows: s.rows ?? props.rows,
        maxRows: s.maxRows ?? props.maxRows,
        autoGrow: s.autoGrow ?? props.autoGrow,
        density: s.density ?? props.density,
        variant: s.variant ?? props.variant,
        disabled: s.disabled,
        readonly: s.readonly,
        placeholder: s.placeholder ?? '{ }',
        validateOn: s.validateOn ?? props.validateOn,
        hideDetails: s.hideDetails === true ? true : 'auto'
      },
      ['type']
    )
  })

  const jsonCmFoldFromDepthEffective = computed(() => {
    const s = effectiveSchema.value
    const raw =
      s.jsonCmFoldFromDepth !== undefined && s.jsonCmFoldFromDepth !== null
        ? Number(s.jsonCmFoldFromDepth)
        : Number(props.jsonCmFoldFromDepth)
    const n = Number.isFinite(raw) ? Math.floor(raw) : 3

    return Math.min(Math.max(n, 1), 64)
  })

  function allSnippetRows () {
    const raw = props.jsonSnippets ?? effectiveSchema.value.jsonSnippets
    if (!Array.isArray(raw)) {
      return []
    }

    return raw.filter((row) => row != null && typeof row === 'object' && typeof row.label === 'string')
  }

  const allSnippets = computed(() => allSnippetRows())

  function laravelRuleKeywords () {
    const raw = props.rules ?? effectiveSchema.value.rules ?? ''
    if (!raw) {
      return new Set()
    }
    const parts = typeof raw === 'string' ? raw.split('|') : raw

    return new Set(parts.map((p) => (typeof p === 'string' ? p.split(':')[0] : '').trim()))
  }

  const allowsEmptyJson = computed(() => laravelRuleKeywords().has('nullable'))

  const guideOpen = ref([])

  const variantChipsLabel = computed(() =>
    t('fields.json_field_piece_labels', 'Bölümler (üzerine gel):'),
  )

  const jsonFieldSubtitle = computed(
    () => props.jsonFieldSubtitle || effectiveSchema.value.jsonFieldSubtitle || '',
  )

  function normalizeVariantChipList () {
    const raw =
      props.jsonFieldVariantChips?.length > 0
        ? props.jsonFieldVariantChips
        : effectiveSchema.value.jsonFieldVariantChips
    if (!Array.isArray(raw)) {
      return []
    }

    return raw.filter(
      (c) =>
        c != null &&
        typeof c === 'object' &&
        typeof c.label === 'string' &&
        typeof c.detail === 'string',
    )
  }

  const variantChipsNormalized = computed(() => normalizeVariantChipList())

  function normalizeGuidePanels () {
    const rawSections =
      props.jsonFieldGuideSections?.length > 0
        ? props.jsonFieldGuideSections
        : effectiveSchema.value.jsonFieldGuideSections
    const mergedTitle =
      props.jsonFieldGuideTitle || effectiveSchema.value.jsonFieldGuideTitle || ''

    /** @type {{ title: string, body: string }[]} */
    const panels = []

    if (!Array.isArray(rawSections)) {
      return panels
    }

    for (let i = 0; i < rawSections.length; i++) {
      const row = rawSections[i]
      if (row == null || typeof row !== 'object') {
        continue
      }
      const st = typeof row.title === 'string' ? row.title : ''
      const sb = typeof row.body === 'string' ? row.body : ''
      if (st === '' && sb === '') {
        continue
      }
      const titleFinal =
        i === 0 && mergedTitle !== ''
          ? `${mergedTitle}: ${st || t('fields.json_field_guide_section', 'detay')}`
          : (st !== '' ? st : t('fields.json_field_guide_section', 'detay'))

      panels.push({ title: titleFinal, body: sb })
    }

    return panels
  }

  const guidePanelsNormalized = computed(() => normalizeGuidePanels())

  function signatureForParsed (parsed) {
    try {
      return JSON.stringify(parsed == null ? null : parsed)
    } catch {
      return String(parsed)
    }
  }

  function stringifyValue (value, indent = null) {
    const spaces = indent == null ? (effectiveSchema.value.jsonIndent ?? props.jsonIndent) : indent
    if (value === null || value === undefined) {
      return ''
    }
    try {
      return JSON.stringify(value, null, typeof spaces === 'number' ? spaces : 2)
    } catch {
      return String(value)
    }
  }

  function initFromExternalValue (value) {
    /** When API sends {@code null} and field is nullable, show empty textarea. */
    if (value === null && allowsEmptyJson.value) {
      syncingDraft.value = true
      draftText.value = ''
      lastEmittedSignature = signatureForParsed(null)
      nextTick(() => {
        syncingDraft.value = false
        flushDraftValidation()
      })

      return
    }

    const resolved =
      value === undefined || value === null ? props.default ?? effectiveSchema.value.default ?? {} : value
    const sig = signatureForParsed(resolved)
    syncingDraft.value = true
    draftText.value = stringifyValue(resolved)
    lastEmittedSignature = sig
    nextTick(() => {
      syncingDraft.value = false
      flushDraftValidation()
    })
  }

  watch(
    () => props.modelValue,
    (next) => {
      if (focused.value) {
        return
      }
      const sig = signatureForParsed(
        next === null && allowsEmptyJson.value ? null : (next ?? props.default ?? effectiveSchema.value.default ?? {}),
      )
      if (sig === lastEmittedSignature) {
        return
      }
      initFromExternalValue(next)
    },
    { deep: true, immediate: true }
  )

  function parseDraftQuiet (text) {
    const trimmed = String(text ?? '').trim()
    if (trimmed === '') {
      return allowsEmptyJson.value ? null : undefined
    }
    try {
      return JSON.parse(trimmed)
    } catch {
      return undefined
    }
  }

  function normalizeInsertToText (insert) {
    if (typeof insert === 'string') {
      return insert
    }
    try {
      return JSON.stringify(insert, null, props.jsonIndent)
    } catch {
      return ''
    }
  }

  function applySnippet (row) {
    if (row === null || typeof row !== 'object' || typeof row.insert === 'undefined') {
      return
    }
    syncingDraft.value = true
    draftText.value = normalizeInsertToText(row.insert)
    syncingDraft.value = false
    tryCommitFromDraft(false)
    syncEditorFromDraft()
  }

  function formatPretty () {
    const trimmed = draftText.value?.trim?.() ?? ''
    if (trimmed === '' && allowsEmptyJson.value) {
      syncingDraft.value = true
      draftText.value = ''
      syncingDraft.value = false
      tryCommitFromDraft(false)
      syncEditorFromDraft()

      return
    }
    try {
      syncingDraft.value = true
      draftText.value = JSON.stringify(JSON.parse(draftText.value), null, props.jsonIndent)
      syncingDraft.value = false
      tryCommitFromDraft(false)
      syncEditorFromDraft()
    } catch {
      syncingDraft.value = false
      VInput.value?.validate?.()
    }
  }

  function formatMinify () {
    const trimmed = draftText.value?.trim?.() ?? ''
    if (trimmed === '' && allowsEmptyJson.value) {
      syncingDraft.value = true
      draftText.value = ''
      syncingDraft.value = false
      tryCommitFromDraft(false)
      syncEditorFromDraft()

      return
    }
    try {
      syncingDraft.value = true
      draftText.value = JSON.stringify(JSON.parse(draftText.value))
      syncingDraft.value = false
      tryCommitFromDraft(false)
      syncEditorFromDraft()
    } catch {
      syncingDraft.value = false
      VInput.value?.validate?.()
    }
  }

  async function copyToClipboard () {
    const text = jsonEditorView.value ? jsonEditorView.value.state.doc.toString() : (draftText.value ?? '')
    try {
      await navigator.clipboard.writeText(text)
    } catch {
      /** ignore */
    }
  }

  function destroyJsonEditor () {
    if (jsonEditorView.value) {
      cancelJsonFieldFoldRetries(jsonEditorView.value)
      jsonEditorView.value.destroy()
      jsonEditorView.value = null
    }
  }

  function syncEditorFromDraft () {
    const v = jsonEditorView.value
    if (!v) {
      return
    }
    if (setCodemirrorDoc(v, draftText.value ?? '')) {
      applyInitialJsonFolds(v, jsonCmFoldFromDepthEffective.value)
    }
  }

  function ensureJsonEditor () {
    const comp = VInput.value
    if (!comp?.$el) {
      return
    }
    const root = comp.$el
    const tnodes = root.querySelectorAll('textarea')
    let mainTa = null
    for (let i = 0; i < tnodes.length; i++) {
      const n = tnodes[i]
      if (!n.classList.contains('v-textarea__sizer')) {
        mainTa = n
        break
      }
    }
    if (!mainTa?.parentElement) {
      return
    }
    const wrap = mainTa.parentElement
    wrap.style.position = 'relative'
    mainTa.style.pointerEvents = 'none'
    mainTa.tabIndex = -1

    let mountEl = wrap.querySelector(':scope > .ue-json-cm-mount')
    if (!mountEl) {
      mountEl = document.createElement('div')
      mountEl.className = 'ue-json-cm-mount'
      mountEl.setAttribute('role', 'textbox')
      mountEl.setAttribute('aria-multiline', 'true')
      const lab = textareaBind.value?.label ?? props.label ?? effectiveSchema.value?.label
      if (typeof lab === 'string' && lab !== '') {
        mountEl.setAttribute('aria-label', lab)
      }
      wrap.appendChild(mountEl)
    }

    destroyJsonEditor()

    const tb = textareaBind.value
    const indentLen = Number(effectiveSchema.value?.jsonIndent ?? props.jsonIndent ?? 2)
    const exts = buildJsonFieldCodemirrorExtensions({
      indentLen,
      readOnly: Boolean(tb?.disabled || tb?.readonly),
      onDocChange: (s) => {
        if (s === draftText.value) {
          return
        }
        draftText.value = s
      },
      onFocus,
      onBlur,
      formatPretty,
      formatMinify,
      copyToClipboard,
    })

    jsonEditorView.value = new EditorView({
      state: EditorState.create({
        doc: draftText.value ?? '',
        extensions: exts,
      }),
      parent: mountEl,
    })
    applyInitialJsonFolds(jsonEditorView.value, jsonCmFoldFromDepthEffective.value)
  }

  const jsonEditorRebuildKey = computed(() => [
    textareaBind.value?.disabled,
    textareaBind.value?.readonly,
    effectiveSchema.value?.jsonIndent ?? props.jsonIndent,
  ])

  watch(
    VInput,
    (v) => {
      if (!v) {
        destroyJsonEditor()

        return
      }
      nextTick(() => {
        ensureJsonEditor()
      })
    },
    { flush: 'post' },
  )

  watch(jsonEditorRebuildKey, () => {
    if (!VInput.value) {
      return
    }
    nextTick(() => {
      ensureJsonEditor()
    })
  })

  watch(jsonCmFoldFromDepthEffective, (depth) => {
    if (!jsonEditorView.value) {
      return
    }
    applyInitialJsonFolds(jsonEditorView.value, depth)
  })

  function mergedRulesComputed () {
    const kw = laravelRuleKeywords()
    const nullable = kw.has('nullable')
    const required = kw.has('required')

    const ruleParseJson = (v) => {
      const text = typeof v === 'string' ? v : String(v ?? '')
      const trimmed = text.trim()
      if (trimmed === '') {
        if (required && !nullable) {
          return t('validation.required', 'This field is required.')
        }
        if (nullable) {
          return true
        }

        return t('validation.json_empty', 'Enter valid JSON or clear the field.')
      }

      try {
        JSON.parse(trimmed)

        return true
      } catch (e) {
        const hint = typeof e.message === 'string' ? e.message : ''

        return hint
          ? t('validation.json_invalid_hint', `Invalid JSON: ${hint}`)
          : t('validation.json_invalid', 'Invalid JSON.')
      }
    }

    const ruleNoEmptyObjectIfRequiredStrict = (v) => {
      const trimmed = String(v ?? '').trim()
      if (trimmed === '') {
        return true
      }

      try {
        const parsed = JSON.parse(trimmed)
        const emptyObject =
          typeof parsed === 'object' &&
          parsed !== null &&
          !Array.isArray(parsed) &&
          Object.keys(parsed).length === 0
        if (emptyObject) {
          return t('validation.json_required_nonempty', 'JSON must not be an empty object.')
        }
      } catch {
        /** syntax errors handled by the first rule */
      }

      return true
    }

    return required && !nullable ? [ruleParseJson, ruleNoEmptyObjectIfRequiredStrict] : [ruleParseJson]
  }

  const mergedRules = computed(() => mergedRulesComputed())

  function tryCommitFromDraft (debounced) {
    const parsed = parseDraftQuiet(draftText.value)
    if (parsed === undefined) {
      if (!debounced) {
        VInput.value?.validate?.()
      }

      return
    }
    const sig = signatureForParsed(parsed)
    if (sig === lastEmittedSignature) {
      return
    }
    lastEmittedSignature = sig
    emit('update:modelValue', parsed)
    emit('change', parsed)
  }

  const debouncedCommit = debounce(() => tryCommitFromDraft(true), 450)

  function onFocus () {
    focused.value = true
  }

  function onBlur () {
    focused.value = false
    debouncedCommit.flush()
    tryCommitFromDraft(false)
  }

  watch(draftText, () => {
    syncEditorFromDraft()
    if (syncingDraft.value || !focused.value) {
      return
    }
    debouncedCommit()
  })

  onMounted(() => {
    flushDraftValidation()
  })

  onBeforeUnmount(() => {
    destroyJsonEditor()
  })

  defineExpose({
    validate: (...args) => {
      if (jsonEditorView.value) {
        draftText.value = jsonEditorView.value.state.doc.toString()
      }

      return VInput.value?.validate?.(...args)
    },
  })
</script>

<style scoped>
  .font-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  }

  .ue-input-json-field__textarea :deep(textarea) {
    line-height: 1.45;
    tab-size: 2;
  }

  /* CodeMirror sits over the native textarea (same v-field); textarea keeps v-model + rules */
  .ue-json-cm-host :deep(.v-field__input) {
    position: relative;
  }

  .ue-json-cm-host :deep(textarea:not(.v-textarea__sizer)) {
    pointer-events: none !important;
    opacity: 0 !important;
    caret-color: transparent !important;
  }

  .ue-json-cm-host :deep(.v-field__outline) {
    pointer-events: none;
  }

  /**
   * v-field__input applies padding to in-flow controls; absolute inset:0 starts at the padding
   * box edge, so CodeMirror lines sat above the real textarea text — clicks mapped "too high",
   * caret felt like it jumped downward. Mirror the same inset as the field input.
   */
  .ue-json-cm-host :deep(.ue-json-cm-mount) {
    position: absolute;
    inset: 0;
    z-index: 6;
    box-sizing: border-box;
    padding-inline: var(--v-field-padding-start) var(--v-field-padding-end);
    padding-top: var(--v-field-input-padding-top);
    padding-bottom: var(--v-field-input-padding-bottom);
  }

  .ue-json-cm-host :deep(.ue-json-cm-mount .cm-editor) {
    height: 100%;
    outline: none;
  }

  .ue-json-cm-host :deep(.ue-json-cm-mount .cm-scroller) {
    overflow: auto;
  }

  .ga-2 {
    gap: 8px;
  }

  .ue-json-field-guide-body {
    white-space: pre-wrap;
    word-break: break-word;
  }

  .ue-input-json-field__guide :deep(.v-expansion-panel-title) {
    min-height: 44px;
  }

  .ue-input-json-field__guide :deep(.v-expansion-panel-text__wrapper) {
    padding-block: 0 12px;
  }

  .cursor-default {
    cursor: default;
  }
</style>

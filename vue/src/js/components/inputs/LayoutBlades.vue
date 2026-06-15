<template>
  <div class="ue-input-layout-blades w-100">
    <p
      v-if="layoutBladesSubtitle"
      class="text-body-2 text-medium-emphasis mb-2">
      {{ layoutBladesSubtitle }}
    </p>
    <div class="d-flex flex-wrap align-center mb-1" style="gap: 8px">
      <v-btn
        v-bind="triggerBtnAttrs"
        type="button"
        @click="openModal">
        <v-icon start>
          mdi-monitor-screenshot
        </v-icon>
        {{ displayModalTriggerLabel }}
      </v-btn>
      <v-alert
        v-if="warnPersistMainForm"
        type="warning"
        density="compact"
        variant="tonal"
        class="ma-0 flex-grow-1"
        style="max-width: min(100%, 520px)"
        :text="persistMainFormWarningText"
      />
      <span class="text-caption text-medium-emphasis">{{ segmentSummary }}</span>
    </div>

    <v-dialog
      v-model="modalOpen"
      fullscreen
      scrollable
      scrim
      transition="dialog-bottom-transition"
      class="ue-input-layout-blades__dialog">
      <v-card class="ue-input-layout-blades__modal-card d-flex flex-column rounded-0 h-100">
        <v-toolbar
          density="comfortable"
          class="px-2 flex-shrink-0"
          color="surface">
          <v-toolbar-title class="text-subtitle-1 text-truncate pe-2" style="max-width: min(40vw, 280px)">
            {{ displayModalTitle }}
          </v-toolbar-title>
          <v-spacer />
          <div class="d-flex align-center ga-1 flex-shrink-0">
            <v-btn
              v-for="vp in VIEWPORTS"
              :key="vp.key"
              icon
              size="small"
              variant="text"
              :title="vp.label"
              :class="previewViewport === vp.key ? 'ue-lb-vp-btn--active' : 'ue-lb-vp-btn--inactive'"
              @click="previewViewport = vp.key"
            >
              <v-icon :size="vp.iconSize">{{ vp.icon }}</v-icon>
            </v-btn>
          </div>
        </v-toolbar>

        <v-divider class="flex-shrink-0" />

        <v-card-text class="pa-0 flex-grow-1 d-flex flex-column overflow-hidden min-height-0">
          <v-alert
            v-if="previewError"
            type="warning"
            density="compact"
            class="ma-4 mb-0"
            :text="previewError"
          />

          <v-progress-linear
            v-if="previewLoading"
            indeterminate
          />

          <div
            ref="splitRootRef"
            class="ue-lb-split-grid flex-grow-1 min-height-0 overflow-hidden"
            :style="{ gridTemplateColumns: `${editorPaneFraction}fr 6px ${1 - editorPaneFraction}fr` }"
          >
            <div class="ue-lb-editor-pane d-flex flex-column min-width-0 min-height-0 overflow-hidden">
              <div class="pa-4 pb-2 pt-3 flex-shrink-0">
                <v-tabs
                  v-model="tab"
                  density="compact"
                  class="border-b mb-1">
                  <v-tab value="head">
                    Head
                  </v-tab>
                  <v-tab value="body">
                    Body
                  </v-tab>
                  <v-tab value="footer">
                    Footer
                  </v-tab>
                </v-tabs>
                <div class="d-flex flex-wrap align-center mb-2" style="gap: 8px">
                  <v-btn
                    size="x-small"
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-format-align-left"
                    type="button"
                    @click="formatBladePretty(tab)"
                  >
                    {{ $t('fields.json_toolbar_format', 'Format') }}
                  </v-btn>
                  <v-btn
                    size="x-small"
                    variant="text"
                    prepend-icon="mdi-content-copy"
                    type="button"
                    @click="copySegment(tab)"
                  >
                    {{ $t('fields.json_toolbar_copy', 'Copy') }}
                  </v-btn>
                  <span class="text-caption text-medium-emphasis ms-auto d-none d-lg-inline">
                    {{ $t('fields.layout_blades_shortcuts_hint', 'Tab / Shift+Tab: indent • Alt+Shift + F (format) / C (copy) • also ⌘/Ctrl+Alt+F/C') }}
                  </span>
                </div>
              </div>
              <div class="flex-grow-1 overflow-y-auto overflow-x-hidden min-height-0 px-4 pb-4">
                <v-window v-model="tab">
                  <v-window-item
                    v-for="key in SEGMENTS"
                    :key="key"
                    :value="key">
                    <v-textarea
                      :ref="(el) => registerSegmentHost(key, el)"
                      v-bind="textareaAttrsModal"
                      :model-value="draft[key]"
                      class="font-mono ue-input-layout-blades__textarea"
                      spellcheck="false"
                      autocomplete="off"
                      autocorrect="off"
                      @update:model-value="patchDraftSegment(key, $event)"
                      @keydown="(evt) => onSegmentTextareaKeydown(evt, key)"
                    />
                  </v-window-item>
                </v-window>
              </div>
            </div>
            <div
              class="ue-lb-splitter flex-shrink-0"
              :title="$t('fields.layout_blades_splitter_hint', 'Drag to resize panes')"
              role="separator"
              @pointerdown.prevent="onSplitterPointerDown"
            />
            <div class="ue-lb-preview-pane d-flex flex-column min-width-0 min-height-0 overflow-hidden bg-surface-variant">
              <div class="d-flex flex-wrap align-center gap-2 mb-1 pa-2 pb-0 flex-shrink-0">
                <span class="text-caption text-medium-emphasis">
                  {{ displayPreviewColumnTitle }}
                </span>
                <v-spacer class="d-none d-sm-block" />
                <v-btn
                  size="small"
                  variant="flat"
                  color="primary"
                  prepend-icon="mdi-refresh"
                  type="button"
                  :disabled="previewButtonDisabled"
                  :loading="previewLoading"
                  @click="runPreview"
                >
                  {{ $t('fields.layout_blades_run_preview', 'Run preview') }}
                </v-btn>
              </div>
              <div class="ue-lb-preview-frame-scroll flex-grow-1 min-height-0 overflow-auto pa-2 pt-0 ue-lb-preview-chrome">
                <div
                  :style="previewFrameStyle"
                  class="ue-lb-preview-frame-box d-flex flex-column"
                >
                  <iframe
                    v-if="effectivePreviewUrl && canRunShellPreview"
                    :key="previewIframeKey"
                    class="ue-input-layout-blades__iframe flex-grow-1 w-100 rounded border"
                    title="layout-shell-preview"
                    sandbox="allow-scripts allow-same-origin"
                    referrerpolicy="no-referrer"
                    :srcdoc="previewSrcdoc"
                  />
                  <div
                    v-else
                    class="text-body-2 text-medium-emphasis pa-4 bg-surface"
                  >
                    {{ previewUnavailableReason }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>

        <v-divider class="flex-shrink-0" />

        <v-card-actions class="justify-end flex-shrink-0 pa-3 bg-surface">
          <v-btn
            variant="flat"
            color="error"
            prepend-icon="mdi-close"
            @click="modalOpen = false">
            {{ $t('messages.close', 'Close') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
  import { computed, inject, onBeforeUnmount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { makeInputEmits, makeInputProps, createCodeTextareaKeydownHandler } from '@/hooks'
  import { formatBladeIndent } from '@/utils/formatBladeIndent'

  defineOptions({ name: 'v-input-layout-blades' })

  const { t } = useI18n({ useScope: 'global' })
  const emit = defineEmits([...makeInputEmits])

  const props = defineProps({
    ...makeInputProps(),
    layoutBladesSubtitle: {
      type: String,
      default: ''
    },
    textareaRows: {
      type: Number,
      default: 12
    },
    shellDraftPreviewUrl: {
      type: String,
      default: ''
    },
    shellPreviewMode: {
      type: String,
      default: 'layout_builder'
    },
    modalEditorTitle: {
      type: String,
      default: ''
    },
    modalOpenButtonLabel: {
      type: String,
      default: ''
    },
    modalPreviewColumnTitle: {
      type: String,
      default: ''
    },
    /** Tab / Shift+Tab indent width (also reads {@code schema.jsonIndent} / {@code schema.codeIndent}). */
    codeIndent: {
      type: Number,
      default: 2
    }
  })

  const ueFormPayload = inject('ueFormPayload', computed(() => ({})))

  /** Same viewport presets as {@link RevisionPreviewDialog} / {@link Revision.vue}. */
  const VIEWPORTS = [
    { key: 'desktop', icon: 'mdi-monitor', label: 'Desktop (1278px)', width: 1278, iconSize: 'default' },
    { key: 'laptop', icon: 'mdi-laptop', label: 'Laptop (1024px)', width: 1024, iconSize: 'default' },
    { key: 'tablet', icon: 'mdi-tablet', label: 'Tablet (768px)', width: 768, iconSize: 'small' },
    { key: 'mobile', icon: 'mdi-cellphone', label: 'Mobile (320px)', width: 320, iconSize: 'x-small' },
  ]

  const SEGMENTS = ['head', 'body', 'footer']

  /** Vuetify {@code v-textarea} host per segment (for Tab indent + native selection restore). */
  const segmentHosts = ref({
    head: null,
    body: null,
    footer: null
  })

  function registerSegmentHost (key, el) {
    segmentHosts.value[key] = el ?? null
  }

  const tab = ref('head')
  const modalOpen = ref(false)
  /** Left column weight in horizontal grid (0.22–0.78); middle is fixed 6px splitter. */
  const editorPaneFraction = ref(0.5)
  const splitRootRef = ref(null)
  /** @type {import('vue').Ref<string>} */
  const previewViewport = ref('desktop')
  let splitterDragging = false
  const draft = ref(normalizeSegments(props.modelValue))
  const previewSrcdoc = ref('')
  const previewLoading = ref(false)
  const previewError = ref('')
  const previewIframeKey = ref(0)
  /** JSON.stringify(normalizeSegments(...)) of draft after last successful preview; null = not previewed this session. */
  const lastPreviewedSnapshot = ref(null)
  /** First value seen from the server/parent; updated only on external prop changes (not our own emits). */
  const firstEverBaseline = ref(normalizeSegments(props.modelValue))
  /** Tracks last payload we emitted so parent echo does not reset {@code firstEverBaseline}. */
  const lastEmittedJson = ref('')
  const warnPersistMainForm = ref(false)

  const persistMainFormWarningText = computed(() =>
    t(
      'fields.layout_blades_persist_main_form_warning',
      'Layout blades were changed. Save the record with the main form so your changes are stored on the server.'
    )
  )

  function segmentLabelKey (key) {
    return `cms.layout_segments.${key}`
  }

  function defaultSegmentLabel (key) {
    return key.charAt(0).toUpperCase() + key.slice(1)
  }

  const textareaAttrs = computed(() => {
    const s = props.obj?.schema && typeof props.obj.schema === 'object' ? props.obj.schema : {}

    return {
      variant: s.variant ?? 'outlined',
      density: s.density ?? 'comfortable',
      rows: s.rows ?? props.textareaRows,
      maxRows: s.maxRows ?? 36,
      autoGrow: s.autoGrow !== false,
      hideDetails: 'auto',
      disabled: !!(s.disabled),
      readonly: !!(s.readonly)
    }
  })

  const textareaAttrsModal = computed(() => ({
    ...textareaAttrs.value,
    maxRows: Math.min(Number(textareaAttrs.value.maxRows) || 36, 36),
  }))

  const previewFrameStyle = computed(() => {
    const vp = VIEWPORTS.find((v) => v.key === previewViewport.value) ?? VIEWPORTS[0]
    return {
      maxWidth: `${vp.width}px`,
      width: '100%',
      margin: '12px auto',
      borderRadius: '8px',
      backgroundColor: '#ffffff',
      boxShadow: '0 4px 24px rgba(0, 0, 0, 0.35)',
      padding: '0',
      transition: 'max-width 0.28s ease',
      minHeight: 'min(42vh, 420px)',
      flex: '1 1 auto',
    }
  })

  const triggerBtnAttrs = computed(() => ({
    variant: props.obj?.schema?.variant ?? 'tonal',
    color: props.obj?.schema?.color ?? 'primary',
    size: props.obj?.schema?.density === 'compact' ? 'small' : 'default',
    disabled: !!(props.obj?.schema?.disabled || props.obj?.schema?.readonly)
  }))

  const effectivePreviewUrl = computed(() => String(props.shellDraftPreviewUrl ?? '').trim())

  function normalizeSegments (raw) {
    const base = { head: '', body: '', footer: '' }
    if (!raw || typeof raw !== 'object') {
      return base
    }
    for (const k of SEGMENTS) {
      base[k] = typeof raw[k] === 'string' ? raw[k] : ''
    }
    return base
  }

  const displayModalTitle = computed(() =>
    props.modalEditorTitle || props.label || props.obj?.schema?.label || 'Layout Blade segments')

  const displayModalTriggerLabel = computed(() => props.modalOpenButtonLabel || 'Edit Blade segments')

  const displayPreviewColumnTitle = computed(() => props.modalPreviewColumnTitle || 'Shell preview')

  const previewContext = computed(() => {
    const m = ueFormPayload.value
    const row = (m && typeof m === 'object') ? m : {}

    return {
      layout_builder_id: row.layout_builder_id ?? row.id ?? null,
      blade_source: row.blade_source ?? null,
      blade_view_name: row.blade_view_name ?? null,
      style_sheet_id: row.style_sheet_id ?? null,
      style_sheet_slugs: row.style_sheet_slugs ?? null
    }
  })

  const canRunShellPreview = computed(() => Boolean(effectivePreviewUrl.value))

  const previewUnavailableReason = computed(() => {
    if (!effectivePreviewUrl.value) {
      return 'Preview is disabled in configuration.'
    }
    return ''
  })

  function segmentsJson (segments) {
    return JSON.stringify(normalizeSegments(segments))
  }

  watch(
    () => props.modelValue,
    (nv) => {
      const norm = normalizeSegments(nv)
      const json = segmentsJson(norm)
      if (json === lastEmittedJson.value) {
        if (!modalOpen.value) {
          draft.value = norm
        }
        return
      }
      firstEverBaseline.value = { ...norm }
      lastEmittedJson.value = json
      warnPersistMainForm.value = false
      if (!modalOpen.value) {
        draft.value = norm
      }
    },
    { deep: true, immediate: true }
  )

  const draftSegmentsKey = computed(() => segmentsJson(draft.value))

  const previewButtonDisabled = computed(() => {
    if (!canRunShellPreview.value || previewLoading.value) {
      return true
    }
    if (lastPreviewedSnapshot.value === null) {
      return false
    }
    return lastPreviewedSnapshot.value === draftSegmentsKey.value
  })

  watch(
    previewContext,
    () => {
      if (modalOpen.value) {
        lastPreviewedSnapshot.value = null
      }
    },
    { deep: true }
  )



  function setEmittedPayload (raw) {
    const next = { ...normalizeSegments(raw) }
    lastEmittedJson.value = segmentsJson(next)
    emit('update:modelValue', next)
  }

  function patchDraftSegment (key, value) {
    draft.value = { ...normalizeSegments(draft.value), [key]: value ?? '' }
    if (!modalOpen.value) {
      setEmittedPayload(draft.value)
    }
  }

  function indentLenForBlade () {
    const s = props.obj?.schema && typeof props.obj.schema === 'object' ? props.obj.schema : {}
    const raw = Number(s.jsonIndent ?? s.codeIndent ?? props.codeIndent ?? 2)

    return Number.isFinite(raw) ? Math.min(Math.max(raw | 0, 1), 16) : 2
  }

  function formatBladePretty (segmentKey) {
    const raw = String(draft.value[segmentKey] ?? '')
    const next = formatBladeIndent(raw, indentLenForBlade())
    patchDraftSegment(segmentKey, next)
  }

  function onSplitterPointerDown () {
    if (!splitRootRef.value) {
      return
    }
    splitterDragging = true
    window.addEventListener('pointermove', onSplitterPointerMove)
    window.addEventListener('pointerup', onSplitterPointerUp, { capture: true })
    window.addEventListener('pointercancel', onSplitterPointerUp, { capture: true })
    try {
      document.body.style.cursor = 'col-resize'
      document.body.style.userSelect = 'none'
    } catch {
      /** ignore */
    }
  }

  function onSplitterPointerMove (e) {
    if (!splitterDragging || !splitRootRef.value) {
      return
    }
    const rect = splitRootRef.value.getBoundingClientRect()
    const w = rect.width || 1
    const x = e.clientX - rect.left
    const next = x / w
    const min = 0.22
    const max = 0.78
    editorPaneFraction.value = Math.min(max, Math.max(min, next))
  }

  function onSplitterPointerUp () {
    if (!splitterDragging) {
      return
    }
    splitterDragging = false
    window.removeEventListener('pointermove', onSplitterPointerMove)
    window.removeEventListener('pointerup', onSplitterPointerUp, { capture: true })
    window.removeEventListener('pointercancel', onSplitterPointerUp, { capture: true })
    try {
      document.body.style.cursor = ''
      document.body.style.userSelect = ''
    } catch {
      /** ignore */
    }
  }

  async function copySegment (segmentKey) {
    try {
      await navigator.clipboard.writeText(String(draft.value[segmentKey] ?? ''))
    } catch {
      /** ignore */
    }
  }

  const segmentKeydownHandlers = Object.fromEntries(
    SEGMENTS.map((key) => [
      key,
      createCodeTextareaKeydownHandler({
        resolveHost: () => segmentHosts.value[key],
        getText: () => draft.value[key] ?? '',
        setText: (next) => patchDraftSegment(key, next),
        getIndentLen: indentLenForBlade,
        actions: {
          formatPretty: () => formatBladePretty(key),
          formatMinify: () => {},
          copy: () => copySegment(key)
        }
      })
    ])
  )

  function onSegmentTextareaKeydown (evt, key) {
    const fn = segmentKeydownHandlers[key]
    if (typeof fn === 'function') {
      fn(evt)
    }
  }

  function commitDraftToModel () {
    setEmittedPayload(draft.value)
  }

  function getCsrfHeaders () {
    const headers = {
      Accept: 'text/html,application/xhtml+xml',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
    const meta = typeof document !== 'undefined'
      ? document.querySelector('meta[name="csrf-token"]')
      : null
    if (meta?.content) {
      headers['X-CSRF-TOKEN'] = meta.content
    }
    if (typeof document !== 'undefined') {
      const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/)
      if (m?.[1]) {
        try {
          headers['X-XSRF-TOKEN'] = decodeURIComponent(m[1])
        } catch {
          headers['X-XSRF-TOKEN'] = m[1]
        }
      }
    }
    return headers
  }

  function buildPreviewPayload () {
    const segments = normalizeSegments(draft.value)
    const model = ueFormPayload.value
    const row = model && typeof model === 'object' ? model : {}
    const tmc = row.target_model_class
    const targetModelPayload = typeof tmc === 'string' && tmc.trim() !== ''
      ? { target_model_class: tmc.trim() }
      : {}

    const ctx = previewContext.value

    return {
      draft: 'layout_builder',
      layout_builder_id: ctx.layout_builder_id,
      blade_segments: segments,
      blade_source: ctx.blade_source ?? undefined,
      blade_view_name: ctx.blade_view_name ?? undefined,
      style_sheet_id: ctx.style_sheet_id ?? undefined,
      style_sheet_slugs: ctx.style_sheet_slugs ?? undefined,
      ...targetModelPayload
    }
  }

  async function runPreview () {
    if (!modalOpen.value || !effectivePreviewUrl.value || !canRunShellPreview.value) {
      return
    }
    previewLoading.value = true
    previewError.value = ''
    try {
      const res = await fetch(effectivePreviewUrl.value, {
        method: 'POST',
        credentials: 'same-origin',
        headers: getCsrfHeaders(),
        body: JSON.stringify(buildPreviewPayload())
      })
      if (!res.ok) {
        const text = await res.text()
        previewError.value = text?.slice(0, 400) || res.statusText
        previewSrcdoc.value = ''
        return
      }
      previewSrcdoc.value = await res.text()
      lastPreviewedSnapshot.value = draftSegmentsKey.value
    } catch (e) {
      previewError.value = e?.message || 'Preview request failed.'
      previewSrcdoc.value = ''
    } finally {
      previewLoading.value = false
    }
  }

  watch(modalOpen, (open) => {
    if (open) {
      draft.value = normalizeSegments(props.modelValue)
      previewError.value = ''
      previewIframeKey.value += 1
      lastPreviewedSnapshot.value = null
      previewSrcdoc.value = ''
      previewViewport.value = 'desktop'
      return
    }
    const closedCommit = normalizeSegments(draft.value)
    commitDraftToModel()
    warnPersistMainForm.value = segmentsJson(closedCommit) !== segmentsJson(firstEverBaseline.value)
  })

  function openModal () {
    modalOpen.value = true
  }

  const segmentSummary = computed(() => {
    const s = normalizeSegments(modalOpen.value ? draft.value : props.modelValue)
    const lengths = SEGMENTS.map((k) => `${k} ${((s[k] ?? '').length)}c`)
    return lengths.join(' · ')
  })

  onBeforeUnmount(() => {
    onSplitterPointerUp()
  })

</script>

<style lang="scss">
  .ue-lb-split-grid {
    display: grid;
    min-height: 0;
  }

  .ue-lb-splitter {
    cursor: col-resize;
    touch-action: none;
    background: rgba(0, 0, 0, 0.1);

    &:hover {
      background: rgba(25, 118, 210, 0.28);
    }
  }

  .ue-lb-preview-chrome {
    background: #3a3a3a;
  }

  .ue-lb-preview-frame-box {
    min-width: 0;
  }

  .ue-lb-vp-btn--active {
    background: rgba(25, 118, 210, 0.16);
    border-radius: 6px;
  }

  .ue-lb-vp-btn--inactive {
    opacity: 0.65;

    &:hover {
      opacity: 0.95;
    }
  }

  .ue-input-layout-blades__iframe {
    min-height: 800px;
    flex: 1 1 auto;
    background: #fff;
    border: 0;
  }

  // @media (min-width: 960px) {
  //   .ue-input-layout-blades__iframe {
  //     min-height: 0;
  //   }
  // }

  .ue-input-layout-blades__preview-col {
    min-height: 100%;
  }
</style>

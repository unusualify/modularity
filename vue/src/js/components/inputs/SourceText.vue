<!--
  Closed: readonly preview of the first line + "{lineCount} · {format}" chip.
  Open: modal CodeMirror 6 editor (language from schema format). md/html get Edit | Preview.
  json Keep emits a parsed value (or null when empty). Format / Minify / Copy in the toolbar.
  Keep commits draft; Cancel / Esc / backdrop confirms discard when dirty.
-->
<template>
  <div class="ue-input-source-text" v-bind="$attrs">
    <v-text-field
      ref="VInput"
      :model-value="firstLine"
      :label="label"
      :hint="hint"
      :persistent-hint="persistentHint"
      :variant="variant"
      :density="density"
      :disabled="isDisabled"
      :readonly="true"
      :placeholder="emptyPlaceholder"
      :rules="closedRules"
      hide-details="auto"
      class="ue-input-source-text__preview"
      @click="openEditor"
    >
      <template #append-inner>
        <v-chip
          size="small"
          variant="tonal"
          density="compact"
          class="me-1"
        >
          {{ chipLabel }}
        </v-chip>
        <v-tooltip location="top">
          <template #activator="{ props: tipProps }">
            <v-btn
              v-bind="tipProps"
              icon
              size="small"
              variant="text"
              density="compact"
              :disabled="isDisabled"
              :aria-label="openEditorLabel"
              @click.stop="openEditor"
            >
              <v-icon icon="mdi-open-in-new" size="20" />
            </v-btn>
          </template>
          <span>{{ openEditorLabel }}</span>
        </v-tooltip>
      </template>
    </v-text-field>

    <v-dialog
      :model-value="dialogOpen"
      :fullscreen="xs"
      :max-width="xs ? undefined : 900"
      :max-height="xs ? undefined : '90vh'"
      scrollable
      :persistent="confirmDiscard"
      scroll-strategy="none"
      :scrim="true"
      @update:model-value="onDialogUpdate"
      @after-enter="mountEditor"
    >
      <v-card
        class="ue-input-source-text__dialog d-flex flex-column"
        :class="{ 'h-100': xs }"
      >
        <v-toolbar density="comfortable" color="surface" class="flex-shrink-0 px-2">
          <v-toolbar-title class="text-subtitle-1 text-truncate pe-2">
            {{ label || openEditorLabel }}
          </v-toolbar-title>
          <v-spacer />
          <v-chip
            size="small"
            variant="outlined"
            density="compact"
            class="me-2"
          >
            {{ format }}
          </v-chip>
          <template v-if="format === 'json'">
            <v-btn
              size="small"
              variant="tonal"
              color="primary"
              class="me-1"
              prepend-icon="mdi-code-braces"
              :disabled="isDisabled"
              @click="formatPretty"
            >
              {{ t('fields.json_toolbar_format', 'Format') }}
            </v-btn>
            <v-btn
              size="small"
              variant="tonal"
              class="me-1"
              prepend-icon="mdi-arrow-collapse-horizontal"
              :disabled="isDisabled"
              @click="formatMinify"
            >
              {{ t('fields.json_toolbar_minify', 'Minify') }}
            </v-btn>
            <v-btn
              size="small"
              variant="text"
              class="me-2"
              prepend-icon="mdi-content-copy"
              @click="copyToClipboard"
            >
              {{ t('fields.json_toolbar_copy', 'Copy') }}
            </v-btn>
          </template>
          <v-btn-toggle
            v-if="hasPreview"
            v-model="pane"
            mandatory
            density="compact"
            variant="outlined"
            divided
            class="me-2"
          >
            <v-btn value="edit" size="small">
              {{ t('fields.edit', 'Edit') }}
            </v-btn>
            <v-btn value="preview" size="small">
              {{ t('messages.source-text.preview', 'Preview') }}
            </v-btn>
          </v-btn-toggle>
          <v-btn
            icon
            variant="text"
            :aria-label="t('fields.close', 'Close')"
            @click="requestClose"
          >
            <v-icon icon="mdi-close" />
          </v-btn>
        </v-toolbar>

        <v-card-text class="ue-input-source-text__body flex-grow-1 pa-4 d-flex flex-column">
          <v-alert
            v-if="parseError"
            type="error"
            variant="tonal"
            density="compact"
            class="mb-3 flex-shrink-0"
          >
            {{ parseError }}
          </v-alert>
          <div class="ue-input-source-text__stage flex-grow-1">
            <div
              v-show="showEditor"
              ref="cmHost"
              class="ue-input-source-text__cm-host"
              role="textbox"
              aria-multiline="true"
              :aria-label="label || openEditorLabel"
            />
            <div
              v-show="showPreview"
              class="ue-input-source-text__preview-pane"
            >
            <p
              v-if="!previewHtml"
              class="text-body-2 text-medium-emphasis ma-0"
            >
              {{ t('messages.source-text.preview-empty', 'Nothing to preview') }}
            </p>
            <div
              v-else-if="format === 'md'"
              class="ue-input-source-text__md markdown-body"
              v-html="previewHtml"
            />
            <template v-else>
              <iframe
                class="ue-input-source-text__html-frame"
                sandbox=""
                :srcdoc="previewSrcdoc"
                :title="t('messages.source-text.preview', 'Preview')"
              />
              <p class="text-caption text-medium-emphasis mt-2 mb-0">
                {{ t('messages.source-text.preview-scripts-off', 'Scripts are disabled in preview.') }}
              </p>
            </template>
            </div>
          </div>
          <p
            v-if="hint"
            class="text-caption text-medium-emphasis mt-2 mb-0 flex-shrink-0"
          >
            {{ hint }}
          </p>
        </v-card-text>

        <v-divider class="flex-shrink-0" />
        <v-card-actions class="pa-4 flex-shrink-0">
          <v-spacer />
          <v-btn variant="text" @click="requestClose">
            {{ t('fields.cancel', 'Cancel') }}
          </v-btn>
          <v-btn color="primary" variant="flat" @click="keep">
            {{ t('messages.source-text.keep', 'Keep') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog
      :model-value="confirmDiscard"
      max-width="440"
      scrim="dark"
      :z-index="10000"
      @update:model-value="(open) => { if (!open) stayEditing() }"
    >
      <v-card rounded="lg">
        <v-card-title class="text-h6">
          {{ t('messages.source-text.discard-title', 'Discard changes?') }}
        </v-card-title>
        <v-card-text class="text-body-2 text-medium-emphasis">
          {{ t('messages.source-text.discard-body', 'You have unsaved edits. Close without keeping them?') }}
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="stayEditing">
            {{ t('fields.cancel', 'Cancel') }}
          </v-btn>
          <v-btn color="error" variant="flat" @click="discard">
            {{ t('messages.source-text.discard', 'Discard') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
  import { computed, nextTick, onBeforeUnmount, ref, shallowRef, watch } from 'vue'
  import { EditorState } from '@codemirror/state'
  import { EditorView } from '@codemirror/view'
  import { useDisplay } from 'vuetify'
  import { useI18n } from 'vue-i18n'
  import { makeInputEmits, makeInputProps, useInput, useSourceText } from '@/hooks'
  import { buildSourceTextCodemirrorExtensions } from '@/utils/sourceTextCodemirror.js'
  import { setCodemirrorDoc } from '@/utils/jsonFieldCodemirror.js'
  import { sourceTextPreviewHtml, sourceTextPreviewSrcdoc } from '@/utils/sourceTextPreview.js'

  defineOptions({
    name: 'v-input-source-text',
    inheritAttrs: false,
  })

  const emit = defineEmits([...makeInputEmits])

  const props = defineProps({
    ...makeInputProps(),
    format: {
      type: String,
      default: 'md',
    },
    jsonIndent: {
      type: Number,
      default: 2,
    },
    rows: {
      type: Number,
      default: 18,
    },
    autoGrow: {
      type: Boolean,
      default: true,
    },
    hint: {
      type: String,
      default: undefined,
    },
    persistentHint: {
      type: Boolean,
      default: true,
    },
    variant: {
      type: String,
      default: 'outlined',
    },
    density: {
      type: String,
      default: 'comfortable',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    rules: {
      type: [String, Array],
      default: undefined,
    },
    placeholder: {
      type: String,
      default: undefined,
    },
    type: {
      type: String,
      default: undefined,
    },
  })

  const { t } = useI18n()
  const { xs } = useDisplay()

  const { VInput } = useInput(props, { emit })

  const {
    dialogOpen,
    confirmDiscard,
    draft,
    pane,
    format,
    hasPreview,
    firstLine,
    chipLabel,
    isDisabled,
    closedRules,
    parseError,
    openEditor,
    requestClose,
    onDialogUpdate,
    formatPretty,
    formatMinify,
    keep,
    discard,
    stayEditing,
  } = useSourceText(props, emit)

  const cmHost = ref(null)
  const editorView = shallowRef(null)

  const openEditorLabel = computed(() => t('messages.source-text.open', 'Open editor'))
  const emptyPlaceholder = computed(
    () => props.placeholder || t('messages.source-text.empty', 'Empty'),
  )
  const showEditor = computed(() => !hasPreview.value || pane.value === 'edit')
  const showPreview = computed(() => hasPreview.value && pane.value === 'preview')
  const previewHtml = computed(() => sourceTextPreviewHtml(format.value, draft.value))
  const previewSrcdoc = computed(() => sourceTextPreviewSrcdoc(previewHtml.value))

  function destroyEditor () {
    if (!editorView.value) {
      return
    }
    editorView.value.destroy()
    editorView.value = null
  }

  function mountEditor () {
    const parent = cmHost.value
    if (!parent || !dialogOpen.value) {
      return
    }

    if (editorView.value && editorView.value.dom.parentElement === parent) {
      editorView.value.focus()

      return
    }

    destroyEditor()

    editorView.value = new EditorView({
      state: EditorState.create({
        doc: draft.value ?? '',
        extensions: buildSourceTextCodemirrorExtensions({
          format: format.value,
          readOnly: isDisabled.value,
          onDocChange: (text) => {
            if (text === draft.value) {
              return
            }
            draft.value = text
          },
        }),
      }),
      parent,
    })
    editorView.value.focus()
  }

  async function copyToClipboard () {
    const text = editorView.value ? editorView.value.state.doc.toString() : (draft.value ?? '')
    try {
      await navigator.clipboard.writeText(text)
    } catch {
      /** ignore */
    }
  }

  watch(
    [dialogOpen, cmHost],
    ([open, host]) => {
      if (open && host) {
        nextTick(() => mountEditor())

        return
      }
      if (!open) {
        destroyEditor()
      }
    },
  )

  watch(pane, (next) => {
    if (next !== 'edit' || !dialogOpen.value) {
      return
    }
    nextTick(() => {
      editorView.value?.requestMeasure?.()
      editorView.value?.focus?.()
    })
  })

  watch(draft, (text) => {
    const view = editorView.value
    if (!view) {
      return
    }
    setCodemirrorDoc(view, text ?? '')
  })

  onBeforeUnmount(() => {
    destroyEditor()
  })
</script>

<style scoped>
.ue-input-source-text__dialog {
  max-height: 90vh;
  overflow: hidden;
}

.ue-input-source-text__dialog.h-100 {
  max-height: 100%;
  height: 100%;
}

.ue-input-source-text__body {
  min-height: 0;
  max-height: calc(90vh - 9rem);
  overflow-y: auto;
}

.ue-input-source-text__dialog.h-100 .ue-input-source-text__body {
  max-height: none;
}

.ue-input-source-text__stage {
  min-height: 16rem;
}

.ue-input-source-text__cm-host,
.ue-input-source-text__preview-pane {
  min-height: 16rem;
}

.ue-input-source-text__cm-host {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
}

.ue-input-source-text__cm-host :deep(.cm-editor) {
  height: auto;
  min-height: 16rem;
  outline: none;
}

.ue-input-source-text__cm-host :deep(.cm-scroller) {
  overflow: visible;
}

.ue-input-source-text__preview-pane {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
  padding: 12px 16px;
}

.ue-input-source-text__html-frame {
  width: 100%;
  min-height: 40vh;
  border: 0;
  background: rgb(var(--v-theme-surface));
}

.ue-input-source-text__md :deep(h1) {
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0 0 12px;
}
.ue-input-source-text__md :deep(h2) {
  font-size: 1.35rem;
  font-weight: 600;
  margin: 20px 0 10px;
}
.ue-input-source-text__md :deep(h3) {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 16px 0 8px;
}
.ue-input-source-text__md :deep(p) {
  margin: 0 0 12px;
}
.ue-input-source-text__md :deep(ul),
.ue-input-source-text__md :deep(ol) {
  margin: 0 0 12px;
  padding-inline-start: 1.5rem;
}
.ue-input-source-text__md :deep(code) {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.875em;
  padding: 0.1em 0.35em;
  border-radius: 4px;
  background: rgba(var(--v-theme-on-surface), 0.08);
}
.ue-input-source-text__md :deep(pre) {
  overflow-x: auto;
  padding: 12px;
  border-radius: 6px;
  margin: 0 0 12px;
  background: rgba(var(--v-theme-on-surface), 0.06);
}
.ue-input-source-text__md :deep(pre code) {
  padding: 0;
  background: transparent;
}
.ue-input-source-text__md :deep(blockquote) {
  margin: 0 0 12px;
  padding-inline-start: 12px;
  border-inline-start: 4px solid rgba(var(--v-border-color), var(--v-border-opacity));
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}
.ue-input-source-text__md :deep(a) {
  color: rgb(var(--v-theme-primary));
}
.ue-input-source-text__md :deep(img) {
  max-width: 100%;
  height: auto;
}
</style>

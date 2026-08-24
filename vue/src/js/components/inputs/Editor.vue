<!-- v-input-editor.vue — UPDATED -->
<!--
  Mevcut dosyadan farklar:
  1. Slash command extension entegrasyonu (VueRenderer ile EditorSlashMenu mount)
  2. Mention extension entegrasyonu (VueRenderer ile EditorMentionMenu mount)
  3. Tablo BubbleMenu eklendi (EditorTableBubble)
  4. wordCount computed → EditorToolbar'a geçildi (showWordCount prop ile)
  5. Yeni props: mentionItems, showWordCount
  6. onBeforeUnmount'ta slashRenderer/mentionRenderer destroy çağrısı
-->

<template>
  <v-input
    ref="VInput"
    v-model="input"
    hide-details="auto"
    :rules="rules"
    class="v-input-editor ue-input-editor"
  >
    <template #default="defaultSlot">
      <div
        class="ue-input-editor__shell"
        :class="{
          'ue-input-editor__shell--disabled': disabled,
          'ue-input-editor__shell--focused': isFocused,
          'ue-input-editor__shell--error': defaultSlot.hasError,
        }"
        :style="shellStyle"
      >
        <label
          v-if="fieldLabel"
          class="ue-input-editor__label text-body-medium text-medium-emphasis mb-2 d-block"
        >
          {{ fieldLabel }}
        </label>

        <v-alert
          v-if="loadError"
          type="error"
          variant="tonal"
          density="compact"
          class="ma-3"
        >
          {{ loadError }}
        </v-alert>

        <template v-else>
          <editor-toolbar
            ref="toolbarRef"
            :editor="editor"
            :rows="toolbarRows"
            :disabled="disabled"
            :upload-url="uploadUrl"
            :source-mode="sourceMode"
            :show-word-count="showWordCount"
            :word-count="wordCount"
            @toggle-source="toggleSourceMode"
            @upload-error="onUploadError"
          />

          <editor-source-code
            v-if="sourceMode"
            v-model="sourceHtml"
            :disabled="disabled"
            @focus="onSourceFocus"
            @blur="onSourceBlur"
          />

          <div
            v-else
            class="ue-input-editor__content-wrap"
          >
            <editor-content
              :editor="editor"
              class="ue-input-editor__content"
            />

            <!-- Mevcut: resim bubble -->
            <bubble-menu
              v-if="editor"
              :editor="editor"
              :should-show="shouldShowImageBubble"
              :options="{ placement: 'top' }"
            >
              <editor-image-bubble :editor="editor" />
            </bubble-menu>

            <!-- YENİ: tablo bubble -->
            <bubble-menu
              v-if="editor"
              :editor="editor"
              :should-show="shouldShowTableBubble"
              :options="{ placement: 'top' }"
            >
              <editor-table-bubble :editor="editor" />
            </bubble-menu>
          </div>
        </template>
      </div>
    </template>
  </v-input>
</template>

<script>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { BubbleMenu } from '@tiptap/vue-3/menus'
import { VueRenderer } from '@tiptap/vue-3'
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import { buildEditorConfig } from '@/config/editor/editorConfig.js'
import { formatEditorHtml } from '@/config/editor/editorFormatHtml.js'
import { cleanEditorHtml } from '@/config/editor/cleanEditorHtml.js'
import {
  buildInteractiveExtensions,
} from '@/config/editor/editorExtensions.js'
import EditorToolbar from '__components/editor/EditorToolbar.vue'
import EditorImageBubble from '__components/editor/EditorImageBubble.vue'
import EditorTableBubble from '__components/editor/EditorTableBubble.vue'   // YENİ
import EditorSourceCode from '__components/editor/EditorSourceCode.vue'
import EditorSlashMenu from '__components/editor/EditorSlashMenu.vue'       // YENİ
import EditorMentionMenu from '__components/editor/EditorMentionMenu.vue'   // YENİ

export default {
  name: 'v-input-editor',
  components: {
    EditorContent,
    EditorToolbar,
    EditorImageBubble,
    EditorTableBubble,
    EditorSourceCode,
    BubbleMenu,
  },
  emits: [...makeInputEmits],
  props: {
    ...makeInputProps(),
    height:       { type: [Number, String], default: 300 },
    uploadUrl:    { type: String,  default: null },
    language:     { type: String,  default: null },
    toolbar:      { type: [Object, Array, String], default: null },
    editorConfig: { type: Object,  default: null },
    rules:        { type: Array,   default: () => [] },
    variant:      { type: String,  default: 'outlined' },

    // ── YENİ ──────────────────────────────────────────────
    /** @mention için kişi listesi: Array<{ id, label, avatar?, subtitle? }> | (query) => Promise */
    mentionItems:         { type: [Array, Function], default: () => [] },
    /** Toolbar sağında kelime / karakter sayacı göster */
    showWordCount:        { type: Boolean, default: false },
  },
  setup (props, context) {
    const isFocused   = ref(false)
    const loadError   = ref(null)
    const sourceMode  = ref(false)
    const sourceHtml  = ref('')
    const inputApi    = useInput(props, context)

    // renderer ref'leri → onBeforeUnmount'ta temizlenir
    let slashRenderer   = null
    let mentionRenderer = null
    const toolbarRef    = ref(null)

    const openImageUpload = () => {
      toolbarRef.value?.openImageUpload?.()
    }

    // ── Slash command render fonksiyonları ────────────────
    const slashRenderFns = {
      onStart (props) {
        slashRenderer = new VueRenderer(EditorSlashMenu, {
          props,
          editor: props.editor,
        })
      },
      onUpdate (props) {
        slashRenderer?.updateProps(props)
      },
      onKeyDown (props) {
        return slashRenderer?.ref?.onKeyDown(props) ?? false
      },
      onExit () {
        slashRenderer?.destroy()
        slashRenderer = null
      },
    }

    // ── Mention render fonksiyonları ──────────────────────
    const mentionRenderFns = {
      onStart (props) {
        mentionRenderer = new VueRenderer(EditorMentionMenu, {
          props,
          editor: props.editor,
        })
      },
      onUpdate (props) {
        mentionRenderer?.updateProps(props)
      },
      onKeyDown (props) {
        return mentionRenderer?.ref?.onKeyDown(props) ?? false
      },
      onExit () {
        mentionRenderer?.destroy()
        mentionRenderer = null
      },
    }

    // ── Editor config ─────────────────────────────────────
    const normalizedToolbar = computed(() => {
      if (!props.toolbar) return null
      if (typeof props.toolbar === 'string') {
        try { return JSON.parse(props.toolbar) } catch { return null }
      }
      return props.toolbar
    })

    const editorOptions = computed(() => buildEditorConfig({
      height:      props.height,
      uploadUrl:   props.uploadUrl,
      language:    props.language,
      toolbar:     normalizedToolbar.value,
      extraConfig: props.editorConfig,
    }))

    const toolbarRows = computed(() => editorOptions.value.toolbarRows)
    const toolbarItems = computed(() => editorOptions.value.toolbarItems)

    const shellStyle = computed(() => ({
      '--ue-editor-min-height': `${Number(props.height) || 300}px`,
    }))

    const fieldLabel = computed(() => props.label || inputApi.boundProps?.label || '')
    const disabled   = computed(() => props.editable === false || props.editable === 'false')

    const serializeEditorHtml = (activeEditor) => {
      const html = activeEditor.getHTML()
      return cleanEditorHtml(html === '<p></p>' ? '' : html)
    }

    // ── Tiptap editor ─────────────────────────────────────
    const editor = useEditor({
      content: inputApi.input.value ?? '',
      extensions: [
        ...editorOptions.value.extensions,
        ...buildInteractiveExtensions({
          mentionItems: props.mentionItems,
          slashRenderFns,
          mentionRenderFns,
          openImageUpload,
        }),
      ],
      editable: !disabled.value,
      onUpdate: ({ editor: activeEditor }) => {
        if (sourceMode.value) return
        inputApi.input.value = serializeEditorHtml(activeEditor)
      },
      onFocus: () => { isFocused.value = true;  context.emit('focus') },
      onBlur:  () => { isFocused.value = false; context.emit('blur') },
    })

    // ── Word count ────────────────────────────────────────
    const wordCount = computed(() => {
      if (!editor.value || !props.showWordCount) return null
      const text = editor.value.state.doc.textContent
      const words = text.trim() ? text.trim().split(/\s+/).length : 0
      return { words, chars: text.length }
    })

    // ── Watchers (mevcut) ─────────────────────────────────
    watch(
      () => inputApi.input.value,
      (value) => {
        const normalized = value ?? ''
        if (!editor.value || sourceMode.value) {
          sourceHtml.value = normalized
          return
        }
        const current    = editor.value.getHTML()
        const comparable = current === '<p></p>' ? '' : current
        if (normalized !== comparable) {
          editor.value.commands.setContent(normalized || '', false)
        }
      },
      { immediate: true },
    )

    watch(disabled, (value) => { editor.value?.setEditable(!value) })

    watch(editorOptions, (options) => {
      if (!editor.value) return
      editor.value.setOptions({ extensions: options.extensions })
    })

    // ── Source mode ───────────────────────────────────────
    const toggleSourceMode = () => {
      if (!editor.value) return
      if (!sourceMode.value) {
        sourceHtml.value = formatEditorHtml(editor.value.getHTML())
        sourceMode.value = true
        return
      }
      try {
        editor.value.commands.setContent(sourceHtml.value || '', false)
        inputApi.input.value = serializeEditorHtml(editor.value)
        sourceMode.value = false
        loadError.value  = null
      } catch (error) {
        loadError.value = error?.message || 'Invalid HTML source.'
      }
    }

    const onSourceFocus = () => { isFocused.value = true;  context.emit('focus') }
    const onSourceBlur  = () => {
      isFocused.value = false
      if (editor.value) {
        try {
          editor.value.commands.setContent(sourceHtml.value || '', false)
          inputApi.input.value = serializeEditorHtml(editor.value)
        } catch (error) {
          loadError.value = error?.message || 'Invalid HTML source.'
        }
      }
      context.emit('blur')
    }

    const onUploadError = (error) => {
      loadError.value = error?.message || 'Image upload failed.'
    }

    // ── Bubble menu should-show ───────────────────────────
    const shouldShowImageBubble = ({ editor: e }) => e.isActive('image')
    const shouldShowTableBubble = ({ editor: e }) => {          // YENİ
      return e.isActive('table') ||
             e.isActive('tableCell') ||
             e.isActive('tableHeader')
    }

    onBeforeUnmount(() => {
      editor.value?.destroy()
      slashRenderer?.destroy()
      mentionRenderer?.destroy()
    })

    return {
      ...inputApi,
      editor,
      toolbarRows,
      toolbarItems,
      sourceMode,
      sourceHtml,
      isFocused,
      loadError,
      shellStyle,
      fieldLabel,
      wordCount,
      toggleSourceMode,
      onSourceFocus,
      onSourceBlur,
      onUploadError,
      shouldShowImageBubble,
      shouldShowTableBubble,
      toolbarRef,
    }
  },
  computed: {
    disabled () {
      return this.editable === false || this.editable === 'false'
    },
  },
}
</script>

<style lang="scss">
  .ue-input-editor {
    width: 100%;

    &__shell {
      width: 100%;
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
      border-radius: var(--v-input-border-radius, 4px);
      background: rgb(var(--v-theme-surface));
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
      overflow: visible;

      &--focused {
        border-color: rgb(var(--v-theme-primary));
        box-shadow: 0 0 0 1px rgb(var(--v-theme-primary));
      }

      &--error {
        border-color: rgb(var(--v-theme-error));
        box-shadow: 0 0 0 1px rgb(var(--v-theme-error));
      }

      &--disabled {
        opacity: 0.62;
        pointer-events: none;
      }
    }

    &__label {
      padding: 12px 12px 0;
    }

    &__toolbar {
      border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
      background: rgba(var(--v-theme-on-surface), 0.03);
    }

    &__content-wrap {
      position: relative;
    }

    &__content {
      overflow: auto;
    }

    &__content .tiptap {
      min-height: var(--ue-editor-min-height, 300px);
      padding: 1rem 1.25rem;
      outline: none;

      &:focus {
        outline: none;
      }

      > * + * {
        margin-top: 0.75em;
      }

      ul,
      ol {
        padding-left: 1.5rem;
      }

      blockquote {
        border-left: 3px solid rgba(var(--v-theme-primary), 0.5);
        margin-left: 0;
        padding-left: 1rem;
        color: rgba(var(--v-theme-on-surface), 0.7);
      }

      pre {
        background: rgba(var(--v-theme-on-surface), 0.06);
        border-radius: 4px;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        font-size: 13px;
      }

      table {
        border-collapse: collapse;
        width: 100%;
      }

      th,
      td {
        border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
        padding: 0.5rem 0.75rem;
        vertical-align: top;
      }

      img.ProseMirror-selectednode {
        outline: 2px solid rgb(var(--v-theme-primary));
        outline-offset: 2px;
      }

      .ue-editor-image:not(.ue-editor-image--float-left):not(.ue-editor-image--float-right) {
        clear: both;
      }

      ul[data-type='taskList'] {
        list-style: none;
        padding-left: 0;

        li {
          display: flex;
          align-items: flex-start;
          gap: 0.5rem;

          > label {
            margin-top: 0.2rem;
          }
        }
      }

      .media-embed {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        margin: 1rem 0;

        iframe {
          position: absolute;
          inset: 0;
          width: 100%;
          height: 100%;
          border: 0;
        }
      }

      [data-page-break='true'] {
        border-top: 1px dashed rgba(var(--v-border-color), var(--v-border-opacity));
        margin: 1.5rem 0;
        height: 0;
      }

      mark {
        background-color: #fef08a;
        border-radius: 2px;
        padding: 0 0.15em;
      }
    }
  }
</style>

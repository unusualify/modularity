<template>
  <div
    ref="rootEl"
    class="ue-input-editor__source"
    :class="{ 'ue-input-editor__source--disabled': disabled }"
  />
</template>

<script>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { EditorState } from '@codemirror/state'
import {
  EditorView,
  highlightActiveLine,
  highlightActiveLineGutter,
  keymap,
  lineNumbers,
} from '@codemirror/view'
import { html } from '@codemirror/lang-html'
import { defaultKeymap, indentWithTab } from '@codemirror/commands'
import {
  bracketMatching,
  defaultHighlightStyle,
  indentOnInput,
  syntaxHighlighting,
} from '@codemirror/language'

const editorTheme = EditorView.theme({
  '&': {
    fontSize: '13px',
    minHeight: 'var(--ue-editor-min-height, 300px)',
    backgroundColor: 'rgb(var(--v-theme-surface))',
    color: 'rgb(var(--v-theme-on-surface))',
  },
  '&.cm-focused': {
    outline: 'none',
  },
  '.cm-scroller': {
    minHeight: 'var(--ue-editor-min-height, 300px)',
    overflow: 'auto',
    fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
  },
  '.cm-content': {
    padding: '1rem 0.75rem',
    caretColor: 'rgb(var(--v-theme-primary))',
  },
  '.cm-gutters': {
    backgroundColor: 'rgba(var(--v-theme-on-surface), 0.03)',
    color: 'rgba(var(--v-theme-on-surface), 0.45)',
    borderRight: '1px solid rgba(var(--v-border-color), var(--v-border-opacity))',
    minHeight: 'var(--ue-editor-min-height, 300px)',
  },
  '.cm-activeLineGutter': {
    backgroundColor: 'rgba(var(--v-theme-primary), 0.08)',
  },
  '.cm-activeLine': {
    backgroundColor: 'rgba(var(--v-theme-primary), 0.05)',
  },
  '.cm-selectionBackground, ::selection': {
    backgroundColor: 'rgba(var(--v-theme-primary), 0.18) !important',
  },
}, { dark: false })

export default {
  name: 'EditorSourceCode',
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue', 'focus', 'blur'],
  setup (props, { emit }) {
    const rootEl = ref(null)
    let view = null
    let suppressUpdate = false

    const createState = (doc) => EditorState.create({
      doc,
      extensions: [
        html(),
        lineNumbers(),
        highlightActiveLine(),
        highlightActiveLineGutter(),
        bracketMatching(),
        indentOnInput(),
        syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
        editorTheme,
        EditorView.lineWrapping,
        EditorView.editable.of(!props.disabled),
        EditorState.readOnly.of(props.disabled),
        keymap.of([...defaultKeymap, indentWithTab]),
        EditorView.updateListener.of((update) => {
          if (update.docChanged && !suppressUpdate) {
            emit('update:modelValue', update.state.doc.toString())
          }

          if (update.focusChanged) {
            if (update.view.hasFocus) {
              emit('focus')
            } else {
              emit('blur')
            }
          }
        }),
      ],
    })

    onMounted(() => {
      if (!(rootEl.value instanceof HTMLElement)) {
        return
      }

      view = new EditorView({
        state: createState(props.modelValue ?? ''),
        parent: rootEl.value,
      })
    })

    watch(
      () => props.modelValue,
      (value) => {
        if (!view) {
          return
        }

        const current = view.state.doc.toString()
        const next = value ?? ''

        if (next === current) {
          return
        }

        suppressUpdate = true
        view.dispatch({
          changes: {
            from: 0,
            to: current.length,
            insert: next,
          },
        })
        suppressUpdate = false
      },
    )

    onBeforeUnmount(() => {
      view?.destroy()
      view = null
    })

    return {
      rootEl,
    }
  },
}
</script>

<style lang="scss">
.ue-input-editor__source {
  width: 100%;

  .cm-editor {
    width: 100%;
  }

  &--disabled {
    opacity: 0.62;
    pointer-events: none;
  }
}
</style>

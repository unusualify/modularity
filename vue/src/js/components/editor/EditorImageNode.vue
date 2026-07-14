<template>
  <node-view-wrapper
    as="div"
    data-drag-handle
    class="ue-editor-image"
    :class="{
      'ue-editor-image--selected': selected,
      'ue-editor-image--float-left': node.attrs.float === 'left',
      'ue-editor-image--float-right': node.attrs.float === 'right',
    }"
    :style="containerStyle"
  >
    <component
      :is="node.attrs.href ? 'a' : 'span'"
      class="ue-editor-image__link"
      :href="node.attrs.href || undefined"
      :target="node.attrs.href ? '_blank' : undefined"
      :rel="node.attrs.href ? 'noopener noreferrer' : undefined"
      @click.stop
    >
      <span
        class="ue-editor-image__shell"
        :style="shellStyle"
      >
        <img
          ref="imageEl"
          class="ue-editor-image__img"
          :src="node.attrs.src"
          :alt="node.attrs.alt || ''"
          :title="node.attrs.title || undefined"
          :style="imageStyle"
          draggable="false"
        >

        <div
          v-if="selected"
          class="ue-editor-image__frame"
        >
          <button
            v-for="handle in resizeHandles"
            :key="handle.id"
            type="button"
            class="ue-editor-image__handle"
            :class="`ue-editor-image__handle--${handle.id}`"
            :aria-label="handle.label"
            @mousedown.prevent="startResize($event, handle.id)"
          />
          <div class="ue-editor-image__size-label">
            {{ widthLabel }}
          </div>
        </div>
      </span>
    </component>
  </node-view-wrapper>
</template>

<script>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { normalizeWidth } from '@/config/editor/editorImageExtension.js'

const RESIZE_HANDLES = [
  { id: 'nw', label: 'Resize top left' },
  { id: 'ne', label: 'Resize top right' },
  { id: 'sw', label: 'Resize bottom left' },
  { id: 'se', label: 'Resize bottom right' },
  { id: 'w', label: 'Resize left edge' },
  { id: 'e', label: 'Resize right edge' },
]

export default {
  name: 'EditorImageNode',
  components: {
    NodeViewWrapper,
  },
  props: nodeViewProps,
  data () {
    return {
      resizeHandles: RESIZE_HANDLES,
      resizeState: null,
    }
  },
  computed: {
    normalizedWidth () {
      return normalizeWidth(this.node.attrs.width) || '100%'
    },
    containerStyle () {
      if (this.node.attrs.float === 'left') {
        return {
          float: 'left',
          width: this.normalizedWidth,
          maxWidth: '100%',
          marginRight: '1rem',
          marginBottom: '0.5rem',
        }
      }

      if (this.node.attrs.float === 'right') {
        return {
          float: 'right',
          width: this.normalizedWidth,
          maxWidth: '100%',
          marginLeft: '1rem',
          marginBottom: '0.5rem',
        }
      }

      return {
        textAlign: this.node.attrs.align || 'center',
      }
    },
    shellStyle () {
      const isFloated = this.node.attrs.float !== 'none'

      return {
        display: isFloated ? 'block' : 'inline-block',
        position: 'relative',
        width: isFloated ? '100%' : this.normalizedWidth,
        maxWidth: '100%',
        verticalAlign: 'top',
        lineHeight: 0,
      }
    },
    imageStyle () {
      return [
        'width: 100%',
        'max-width: 100%',
        'height: auto',
        'display: block',
      ].join('; ')
    },
    widthLabel () {
      const width = this.node.attrs.width ?? '100%'

      return String(width).includes('%') ? String(width) : `${width}%`
    },
  },
  mounted () {
    window.addEventListener('mousemove', this.onMouseMove)
    window.addEventListener('mouseup', this.stopResize)
  },
  beforeUnmount () {
    window.removeEventListener('mousemove', this.onMouseMove)
    window.removeEventListener('mouseup', this.stopResize)
  },
  methods: {
    getContentWidth () {
      const editorDom = this.editor?.view?.dom

      if (!(editorDom instanceof HTMLElement)) {
        return 0
      }

      const styles = window.getComputedStyle(editorDom)

      return editorDom.clientWidth
        - parseFloat(styles.paddingLeft || '0')
        - parseFloat(styles.paddingRight || '0')
    },
    startResize (event, handle) {
      const image = this.$refs.imageEl

      if (!(image instanceof HTMLImageElement)) {
        return
      }

      this.resizeState = {
        handle,
        startX: event.clientX,
        startWidth: image.getBoundingClientRect().width,
        contentWidth: this.getContentWidth(),
      }
    },
    onMouseMove (event) {
      if (!this.resizeState) {
        return
      }

      const { handle, startX, startWidth, contentWidth } = this.resizeState

      if (!contentWidth) {
        return
      }

      let delta = event.clientX - startX

      if (['nw', 'sw', 'w'].includes(handle)) {
        delta *= -1
      }

      const nextWidth = Math.max(40, startWidth + delta)
      const percentage = Math.min(100, Math.max(5, Math.round((nextWidth / contentWidth) * 1000) / 10))
      const width = normalizeWidth(`${percentage}%`)

      this.updateAttributes({ width })
    },
    stopResize () {
      this.resizeState = null
    },
  },
}
</script>

<style lang="scss">
.ue-editor-image {
  display: block;
  width: 100%;
  margin: 0.75rem 0;
  line-height: 0;

  &--float-left,
  &--float-right {
    width: auto;
    margin-top: 0;
    clear: none;
  }

  &__link {
    display: block;
    width: 100%;
    color: inherit;
    text-decoration: none;
  }

  &__shell {
    box-sizing: border-box;
  }

  &__img {
    box-sizing: border-box;
    vertical-align: top;
  }

  &--selected .ue-editor-image__img {
    outline: 2px solid rgb(var(--v-theme-primary));
    outline-offset: 2px;
  }

  &__frame {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }

  &__handle {
    position: absolute;
    width: 12px;
    height: 12px;
    border: 2px solid rgb(var(--v-theme-surface));
    border-radius: 50%;
    background: rgb(var(--v-theme-primary));
    padding: 0;
    pointer-events: auto;
    z-index: 2;

    &--nw {
      top: -6px;
      left: -6px;
      cursor: nwse-resize;
    }

    &--ne {
      top: -6px;
      right: -6px;
      cursor: nesw-resize;
    }

    &--sw {
      bottom: -6px;
      left: -6px;
      cursor: nesw-resize;
    }

    &--se {
      right: -6px;
      bottom: -6px;
      cursor: nwse-resize;
    }

    &--w {
      top: 50%;
      left: -6px;
      transform: translateY(-50%);
      cursor: ew-resize;
    }

    &--e {
      top: 50%;
      right: -6px;
      transform: translateY(-50%);
      cursor: ew-resize;
    }
  }

  &__size-label {
    position: absolute;
    left: 50%;
    bottom: -28px;
    transform: translateX(-50%);
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 12px;
    line-height: 1.4;
    white-space: nowrap;
    pointer-events: none;
  }
}
</style>

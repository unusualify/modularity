<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-image-gallery"
  >
    <template v-slot:default="defaultSlot">
      <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-locale--is-ltr">
        <div class="v-field__field" data-no-activator="">
          <div class="media w-100">
            <div class="_media__field">
              <Draggable
                v-if="input.length > 0"
                :model-value="input"
                class="image-gallery__grid mb-2"
                :class="{ 'image-gallery__grid--draggable': isGalleryDraggable }"
                item-key="id"
                v-bind="galleryDragOptions"
                @update:model-value="onGalleryReorder"
                @start="onGalleryDragStart"
                @end="onGalleryDragEnd"
              >
                <template #item="{ element: media, index }">
                  <div
                    class="image-gallery__item"
                    :class="{ 'image-gallery__item--dragging': galleryDragActive }"
                  >
                    <div class="image-gallery__thumb">
                      <button
                        v-if="isGalleryDraggable"
                        type="button"
                        class="image-gallery__drag-handle"
                        data-test="imageGalleryDragHandle"
                        :title="$t('fields.medias.reorder', 'Reorder')"
                        :aria-label="$t('fields.medias.reorder', 'Reorder')"
                        @click.stop
                      >
                        <v-icon icon="mdi-drag" size="14" color="white" />
                      </button>
                      <v-hover v-slot="{ isHovering, props: hoverProps }">
                        <div
                          class="image-gallery__thumb-body"
                          v-bind="hoverProps"
                          data-test="imageGalleryThumb"
                          @click="onThumbClick(index)"
                          @dragstart.prevent
                        >
                          <v-img
                            :src="media.thumbnail"
                            :alt="media.alt || media.name"
                            width="72"
                            height="72"
                            contain
                          />
                          <v-overlay
                            v-if="!disabled"
                            :model-value="isHovering && !galleryDragActive"
                            contained
                            class="align-center justify-center image-gallery__overlay"
                            scrim="rgba(0, 0, 0, 0.8)"
                          >
                            <div class="image-gallery__actions" @click.stop>
                              <v-icon
                                icon="mdi-eye"
                                size="default"
                                color="white"
                                variant="text"
                                data-test="showButton"
                                :title="$t('fields.medias.preview', 'Preview')"
                                @click="openPreview(index)"
                              />
                              <v-icon
                                icon="$delete"
                                size="default"
                                color="#ff8a80"
                                variant="text"
                                data-test="deleteButton"
                                :title="$t('fields.medias.delete', 'Delete')"
                                @click="deleteMediaClick(index, defaultSlot.validate)"
                              />
                            </div>
                          </v-overlay>
                        </div>
                      </v-hover>
                    </div>
                  </div>
                </template>
              </Draggable>

              <v-btn
                v-if="!disabled && remainingItems > 0"
                data-test="addButton"
                append-icon="$add"
                variant="outlined"
                block
                @click="openMediaLibrary(remainingItems)"
              >
                {{ addLabel }}
              </v-btn>

              <input :name="name" :value="JSON.stringify(input)" type="hidden">
            </div>
          </div>
        </div>

        <div class="v-field__outline">
          <div class="v-field__outline__start"></div>
          <div class="v-field__outline__notch">
            <label class="v-label v-field-label v-field-label--floating" aria-hidden="true">
              <slot name="label" v-bind="{ label: label }">
                {{ boundProps.label }}
              </slot>
            </label>
          </div>
          <div class="v-field__outline__end"></div>
        </div>
      </div>

      <v-dialog
        v-model="deleteDialogOpen"
        max-width="420"
      >
        <v-card>
          <v-card-title>
            {{ $t('media-library.dialogs.delete.delete-media-title', 'Delete media') }}
          </v-card-title>
          <v-card-text>
            <span v-html="$t('media-library.dialogs.delete.delete-media-desc', 'Are you sure?<br/>This change can\'t be undone.')" />
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              variant="text"
              data-test="deleteCancelButton"
              @click="cancelDeleteMedia"
            >
              {{ $t('fields.cancel', 'Cancel') }}
            </v-btn>
            <v-btn
              color="error"
              data-test="deleteConfirmButton"
              @click="confirmDeleteMedia"
            >
              {{ $t('fields.medias.delete', 'Delete') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <v-dialog
        v-model="previewOpen"
        max-width="720"
        scrollable
      >
        <v-card v-if="previewMedia">
          <v-toolbar density="compact" color="primary">
            <v-toolbar-title class="text-truncate">
              {{ previewMedia.name }}
            </v-toolbar-title>
            <v-spacer />
            <v-btn icon="$close" variant="text" @click="previewOpen = false" />
          </v-toolbar>

          <v-card-text class="pa-4">
            <div class="image-gallery__preview-image mb-4">
              <v-img
                :src="previewImageSrc"
                :alt="previewMedia.alt || previewMedia.name"
                max-height="480"
                contain
              />
            </div>

            <v-list density="compact" class="pa-0 bg-transparent">
              <v-list-item
                v-if="previewMedia.name"
                :title="$t('fields.medias.filename', 'Filename')"
                :subtitle="previewMedia.name"
              />
              <v-list-item
                v-if="previewMedia.alt"
                :title="$t('fields.medias.alt-text', 'Alt Text')"
                :subtitle="previewMedia.alt"
              />
              <v-list-item
                v-if="previewMedia.size"
                :title="$t('fields.medias.file-size', 'File size')"
                :subtitle="previewMedia.size"
              />
              <v-list-item
                v-if="previewMedia.width && previewMedia.height"
                :title="$t('fields.medias.original-dimensions', 'Original dimensions')"
              >
                <template v-slot:subtitle>
                  {{ previewMedia.width }}&nbsp;&times;&nbsp;{{ previewMedia.height }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>

          <v-card-actions v-if="!disabled">
            <v-spacer />
            <v-btn
              variant="text"
              prepend-icon="$edit"
              @click="openPreviewInMediaLibrary"
            >
              {{ $t('fields.medias.replace', 'Replace') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </template>
  </v-input>
</template>

<script>
import { nextTick } from 'vue'
import Draggable from 'vuedraggable'
import { useInput, makeInputProps, makeInputEmits, useMediaLibrary } from '@/hooks'
import { makeImageProps, useImage } from '@/hooks'

export default {
  name: 'v-input-image-gallery',
  emits: [...makeInputEmits],
  components: {
    Draggable
  },
  props: {
    ...makeImageProps(),
    max: {
      type: Number,
      default: 200
    }
  },
  setup (props, context) {
    const imageApi = useImage(props, context)
    const { openMediaLibrary: baseOpen } = useMediaLibrary(props)

    const openMediaLibrary = (max, name, index) => {
      const n = name ?? props.name
      const i = typeof index === 'number' ? index : -1
      const inputVal = imageApi.input?.value ?? imageApi.input ?? []
      baseOpen(max, n, i, Array.isArray(inputVal) ? inputVal : [])
      nextTick(() => {
        imageApi.mediableActive.value = true
      })
    }

    return {
      ...imageApi,
      openMediaLibrary
    }
  },
  data () {
    return {
      previewOpen: false,
      previewIndex: -1,
      deleteDialogOpen: false,
      deletePendingIndex: -1,
      deletePendingCallback: null,
      galleryDragActive: false
    }
  },
  computed: {
    isGalleryDraggable () {
      return !this.disabled && this.input.length > 1
    },
    galleryDragOptions () {
      return {
        disabled: !this.isGalleryDraggable,
        animation: 150,
        handle: '.image-gallery__drag-handle',
        filter: '.image-gallery__actions',
        preventOnFilter: false,
        forceFallback: true,
        fallbackOnBody: true,
        ghostClass: 'image-gallery__ghost',
        chosenClass: 'image-gallery__chosen',
        dragClass: 'image-gallery__drag'
      }
    },
    mediaKey () {
      return this.mediaContext.length > 0 ? this.mediaContext : this.name
    },
    previewMedia () {
      if (this.previewIndex < 0 || !this.input[this.previewIndex]) {
        return null
      }

      return this.input[this.previewIndex]
    },
    previewImageSrc () {
      if (!this.previewMedia) {
        return ''
      }

      return this.previewMedia.original || this.previewMedia.thumbnail || ''
    }
  },
  methods: {
    onGalleryReorder (orderedItems) {
      if (!Array.isArray(orderedItems)) {
        return
      }

      this.input = orderedItems
    },
    onGalleryDragStart () {
      this.galleryDragActive = true
    },
    onGalleryDragEnd () {
      this.galleryDragActive = false
      this.syncPreviewIndexAfterReorder()
    },
    syncPreviewIndexAfterReorder () {
      if (this.previewIndex < 0 || !this.previewMedia) {
        return
      }

      const mediaId = this.previewMedia.id
      const nextIndex = this.input.findIndex((media) => media.id === mediaId)

      if (nextIndex >= 0) {
        this.previewIndex = nextIndex
      }
    },
    onThumbClick (index) {
      if (this.disabled || this.galleryDragActive) {
        return
      }

      this.openMediaLibrary(1, this.mediaKey, index)
    },
    openPreview (index) {
      this.previewIndex = index
      this.previewOpen = true
    },
    openPreviewInMediaLibrary () {
      const index = this.previewIndex
      this.previewOpen = false
      this.$nextTick(() => {
        this.openMediaLibrary(1, this.mediaKey, index)
      })
    },
    deleteMediaClick (index, callback) {
      this.deletePendingIndex = index
      this.deletePendingCallback = callback
      this.deleteDialogOpen = true
    },
    cancelDeleteMedia () {
      this.deleteDialogOpen = false
      this.deletePendingIndex = -1
      this.deletePendingCallback = null
    },
    confirmDeleteMedia () {
      const index = this.deletePendingIndex
      const callback = this.deletePendingCallback

      this.deleteDialogOpen = false
      this.deletePendingIndex = -1
      this.deletePendingCallback = null

      if (index < 0) {
        return
      }

      if (this.previewIndex === index) {
        this.previewOpen = false
        this.previewIndex = -1
      } else if (this.previewIndex > index) {
        this.previewIndex -= 1
      }

      this.deleteMedia(index)
      this.$nextTick(() => {
        if (typeof callback === 'function') {
          callback()
        }
      })
    },
    deleteMedia (index) {
      const newInput = [...this.input]
      newInput.splice(index, 1)
      this.input = newInput
    }
  }
}
</script>

<style lang="scss" scoped>
.v-input-image-gallery {
  padding-top: 8px;
  padding-bottom: 8px;
}

.image-gallery__grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.image-gallery__item {
  flex: 0 0 auto;
}

.image-gallery__chosen .image-gallery__drag-handle {
  cursor: grabbing;
}

.image-gallery__ghost {
  opacity: 0.45;
}

.image-gallery__drag {
  opacity: 0.85;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
}

.image-gallery__thumb {
  position: relative;
  width: 72px;
  height: 72px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
  overflow: hidden;
  background: rgba(var(--v-theme-on-surface), 0.04);
  user-select: none;
}

.image-gallery__thumb-body {
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.image-gallery__thumb-body :deep(img) {
  -webkit-user-drag: none;
  pointer-events: none;
}

.image-gallery__drag-handle {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  padding: 0;
  border: none;
  border-radius: 0 0 4px 0;
  background: rgba(0, 0, 0, 0.55);
  cursor: grab;
  touch-action: none;

  &:active {
    cursor: grabbing;
  }
}

.image-gallery__overlay {
  pointer-events: none;
}

.image-gallery__actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  pointer-events: auto;

  .v-icon {
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.6));
  }
}

.image-gallery__preview-image {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  background: rgba(var(--v-theme-on-surface), 0.04);
  border-radius: 4px;
}
</style>

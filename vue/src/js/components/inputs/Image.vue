<template>
  <v-input
    v-model="input"
    hide-details="auto"
    append-icon="mdi-close"
    :variant="boundProps.variant"
    class="v-input-image ue-input-image"
  >
    <template #default="defaultSlot">
      <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-locale--is-ltr">
        <div class="v-field__field" data-no-activator="">
          <div
            class="ue-input-image__body w-100"
            :class="{ 'ue-input-image--slide': isSlide }"
          >
            <div class="ue-input-image__field">
              <v-row dense no-gutters>
                <v-col
                  v-for="index in totalElementCount"
                  :key="index"
                  v-bind="imageCol"
                  v-fit-grid
                >
                  <v-card
                    class="ue-input-image__card w-100 d-flex flex-column justify-end"
                    variant="text"
                    flat
                  >
                    <v-hover
                      v-if="input[index] !== undefined"
                      v-slot="{ isHovering, props: hoverProps }"
                    >
                      <div
                        class="ue-input-image__item d-flex align-center"
                        v-bind="hoverProps"
                        data-test="imageCard"
                      >
                        <div class="ue-input-image__thumb pa-2">
                          <div
                            class="ue-input-image__thumb-frame"
                            tabindex="0"
                            @click="!disabled && openMediaLibrary(1, mediaKey, index)"
                            @keyup.enter="!disabled && openMediaLibrary(1, mediaKey, index)"
                          >
                            <div
                              class="ue-input-image__thumb-center"
                              :style="cropThumbnailStyle"
                            >
                              <v-img
                                v-if="showImg"
                                :src="input[index].thumbnail"
                                ref="mediaImg"
                                :class="cropThumbnailClass"
                                max-width="240"
                              />
                            </div>
                            <div
                              v-if="!disabled"
                              class="ue-input-image__overlay"
                              :class="{ 'ue-input-image__overlay--active': isHovering }"
                            >
                              <v-btn
                                icon="$edit"
                                variant="flat"
                                size="x-small"
                                density="compact"
                                color="surface"
                                data-test="editButton"
                                :aria-label="$t('fields.medias.edit', 'Edit')"
                                @click.stop="openMediaLibrary(1, mediaKey, index)"
                              />
                            </div>
                          </div>
                        </div>

                        <v-list
                          lines="one"
                          density="compact"
                          class="ue-input-image__meta flex-grow-1 py-1"
                          min-width="0"
                        >
                          <v-tooltip
                            :model-value="overflowTooltip === `name-${index}`"
                            :open-on-hover="false"
                            location="top"
                            max-width="360"
                            :text="input[index].name"
                          >
                            <template #activator="{ props: tooltipProps }">
                              <v-list-item
                                v-bind="tooltipProps"
                                class="ue-input-image__meta-item"
                                :title="input[index].name"
                                @click="openMediaLibrary(1, mediaKey, index)"
                                @mouseenter="showOverflowTooltip($event, `name-${index}`)"
                                @mouseleave="hideOverflowTooltip(`name-${index}`)"
                                @focus="showOverflowTooltip($event, `name-${index}`)"
                                @blur="hideOverflowTooltip(`name-${index}`)"
                              />
                            </template>
                          </v-tooltip>
                          <v-tooltip
                            v-if="input[index].size"
                            :model-value="overflowTooltip === `size-${index}`"
                            :open-on-hover="false"
                            location="top"
                            max-width="360"
                            :text="fileSizeLabel(input[index])"
                          >
                            <template #activator="{ props: tooltipProps }">
                              <v-list-item
                                v-bind="tooltipProps"
                                :subtitle="fileSizeLabel(input[index])"
                                @mouseenter="showOverflowTooltip($event, `size-${index}`)"
                                @mouseleave="hideOverflowTooltip(`size-${index}`)"
                                @focus="showOverflowTooltip($event, `size-${index}`)"
                                @blur="hideOverflowTooltip(`size-${index}`)"
                              />
                            </template>
                          </v-tooltip>
                          <v-tooltip
                            v-if="input[index].width + input[index].height"
                            :model-value="overflowTooltip === `dims-${index}`"
                            :open-on-hover="false"
                            location="top"
                            max-width="360"
                            :text="originalDimensionsLabel(input[index])"
                          >
                            <template #activator="{ props: tooltipProps }">
                              <v-list-item
                                v-bind="tooltipProps"
                                :subtitle="originalDimensionsLabel(input[index])"
                                @mouseenter="showOverflowTooltip($event, `dims-${index}`)"
                                @mouseleave="hideOverflowTooltip(`dims-${index}`)"
                                @focus="showOverflowTooltip($event, `dims-${index}`)"
                                @blur="hideOverflowTooltip(`dims-${index}`)"
                              />
                            </template>
                          </v-tooltip>
                          <v-list-item
                            v-if="!disabled"
                            class="ue-input-image__actions px-2"
                            density="compact"
                            :lines="false"
                            :ripple="false"
                          >
                            <div class="d-flex align-center flex-nowrap">
                              <v-btn
                                icon="$download"
                                variant="text"
                                size="x-small"
                                density="compact"
                                data-test="downloadButton"
                                :href="input[index].original"
                                :aria-label="$t('fields.medias.download', 'Download')"
                                download
                              />
                              <v-btn
                                v-if="activeCrop"
                                icon="mdi-crop"
                                variant="text"
                                size="x-small"
                                density="compact"
                                data-test="cropButton"
                                :aria-label="$t('fields.medias.crop-edit', 'Crop')"
                              />
                              <v-btn
                                icon="$delete"
                                variant="text"
                                size="x-small"
                                density="compact"
                                color="error"
                                data-test="deleteButton"
                                :aria-label="$t('fields.medias.delete', 'Delete')"
                                @click="deleteMediaClick(index, defaultSlot.validate)"
                              />
                            </div>
                          </v-list-item>
                        </v-list>
                      </div>
                    </v-hover>
                    <template v-if="input[index] === undefined" #actions>
                      <v-btn
                        data-test="addButton"
                        append-icon="$add"
                        variant="outlined"
                        block
                        @click="openMediaLibrary(remainingItems)"
                      >
                        {{ addLabel }}
                      </v-btn>
                    </template>
                  </v-card>
                </v-col>
              </v-row>

              <p v-if="$slots.default" class="ue-input-image__note">
                <slot />
              </p>
              <input :name="name" :value="JSON.stringify(media)" type="hidden">
            </div>
          </div>
        </div>

        <div class="v-field__outline">
          <div class="v-field__outline__start"></div>
          <div class="v-field__outline__notch">
            <label class="v-label v-field-label v-field-label--floating" aria-hidden="true">
              <slot name="label" v-bind="{ label }">
                {{ boundProps.label }}
              </slot>
            </label>
          </div>
          <div class="v-field__outline__end"></div>
        </div>
      </div>
    </template>
  </v-input>
</template>

<script>
import {
  mapState
} from 'vuex'

import { MEDIA_LIBRARY } from '@/store/mutations'
import { nextTick } from 'vue'
import { useInput, makeInputProps, makeInputEmits, useMediaLibrary } from '@/hooks'
import { makeImageProps, useImage } from '@/hooks'

import a17VueFilters from '@/utils/filters.js'
import {
  cropConversion
} from '@/utils/cropper'
import { range } from 'lodash-es'
// import smartCrop from 'smartcrop'

const IS_SAFARI = navigator.userAgent.indexOf('Safari') !== -1 && navigator.userAgent.indexOf('Chrome') === -1
// const { t } = useI18n({ useScope: 'global' })

export default {
  name: 'v-input-image',
  emits: [...makeInputEmits],
  components: {
    // 'a17-cropper': a17Cropper,
    // 'a17-mediametadata': a17MediaMetadata
  },
  props: {
    ...makeImageProps(),
    withAddInfo: { type: Boolean, default: true },
    withVideoUrl: { type: Boolean, default: true },
    withCaption: { type: Boolean, default: true },
    altTextMaxLength: { type: Number, default: 0 },
    captionMaxLength: { type: Number, default: 0 },
    cropContext: { type: String, default: '' },
    extraMetadatas: { type: Array, default: () => [] },
    imageCol: {
      type: Object,
      default: () => ({
        cols: 12,
        md: 6,
        lg: 4
      })
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
  data: function () {
    return {
      canvas: null,
      img: null,
      ctx: null,
      imgLoaded: false,
      cropSrc: '',
      showImg: true,
      isDestroyed: false,
      naturalDim: {
        width: null,
        height: null
      },
      originalDim: {
        width: null,
        height: null
      },
      hasMediaChanged: false,
      metadatas: {
        text: this?.$t('fields.medias.edit-info') ?? '',
        textOpen: this?.$t('fields.medias.edit-info') ?? '',
        textClose: this?.$t('fields.medias.edit-close') ?? '',
        active: false
      },
      totalElementCount: range(0, this.max),
      overflowTooltip: null
    }
  },
  filters: a17VueFilters,
  computed: {
    ...mapState({
      useWysiwyg: state => state.mediaLibrary.config.useWysiwyg,
      wysiwygOptions: state => state.mediaLibrary.config.wysiwygOptions
    }),
    cropThumbnailStyle: function () {
      if (this.showImg) return {}
      if (!this.hasMedia) return {}
      if (!this.media.crops) return {}
      if (this.cropSrc.length === 0) return {}

      return {
        backgroundImage: `url(${this.cropSrc})`
      }
    },
    cropThumbnailClass: function () {
      if (!this.hasMedia) return {}
      if (!this.media.crops) return {}
      const crop = this.media.crops[Object.keys(this.media.crops)[0]]
      return {
        'ue-input-image__thumb--landscape': crop.width / crop.height >= 1,
        'ue-input-image__thumb--portrait': crop.width / crop.height < 1
      }
    },
    mediaKey: function () {
      return this.mediaContext.length > 0 ? this.mediaContext : this.name
    },
    inputName: function () {
      let fieldName = this.name
      if (this.name.indexOf('[')) {
        fieldName = this.name.replace(']', '').replace('[', '][')
      }
      return 'medias[' + fieldName + '][' + this.index + ']'
    },
    metadataName: function () {
      return 'mediaMeta[' + this.name + '][' + this.media.id + ']'
    },
    media: function () {
      return this.input[0] || {}
    },
    cropInfos: function () {
      const cropInfos = []
      if (this.media.crops) {
        for (const variant in this.media.crops) {
          if (this.media.crops[variant].width + this.media.crops[variant].height) { // crop is not 0x0
            let cropInfo = ''
            cropInfo += this.media.crops[variant].name + ' ' + this.$trans('fields.medias.crop-list') + ': '
            cropInfo += this.media.crops[variant].width + '&nbsp;&times;&nbsp;' + this.media.crops[variant].height
            cropInfos.push(cropInfo)
          }
        }
      }
      return cropInfos.length > 0 ? cropInfos : null
    },
    hasMedia: function () {
      return this.input.length > 0
      // return Object.keys(this.media).length > 0
    },
    cropperKey: function () {
      return `${this.mediaKey}-${this.index}_${this.cropContext}`
    },
    mediaHasCrop: function () {
      return this.media.crops
    },
    cropModalName: function () {
      return `${name}Modal`
    },
    ...mapState({
      selectedMedias: state => state.mediaLibrary.selected,
      allCrops: state => state.mediaLibrary.crops
    })
  },
  watch: {
    media: function (val, oldVal) {
      this.hasMediaChanged = val !== oldVal

      if (Object.prototype.hasOwnProperty.call(this.selectedMedias, this.mediaKey)) {
        // reset isDestroyed status because we changed the media
        if (this.selectedMedias[this.mediaKey][this.index]) this.isDestroyed = false
      }
    }
  },
  methods: {
    mediaThumbnailSrc () {
      const thumbnail = this.media?.thumbnail
      if (typeof thumbnail !== 'string' || thumbnail.length === 0) {
        return null
      }

      let append = '?'
      if (thumbnail.indexOf('?') > -1) {
        append = '&'
      }

      return thumbnail + append + 'no-cache'
    },
    logImageLoadFailure (src, error) {
      if (process.env.NODE_ENV === 'production') {
        return
      }

      const message = error instanceof Error
        ? error.message
        : (typeof error?.type === 'string' ? `Image ${error.type} event` : 'Image failed to load')

      console.warn(`[v-input-image] ${message}`, src)
    },
    resolveMediaImgElement () {
      const ref = this.$refs.mediaImg
      if (!ref) {
        return null
      }

      if (ref instanceof HTMLImageElement) {
        return ref
      }

      const component = Array.isArray(ref) ? ref[0] : ref
      if (!component) {
        return null
      }

      const el = component.$el ?? component

      if (el instanceof HTMLImageElement) {
        return el
      }

      if (el && typeof el.querySelector === 'function') {
        return el.querySelector('img')
      }

      return null
    },
    // crop
    canvasCrop () {
      const data = this.media.crops[Object.keys(this.media.crops)[0]]
      if (!data) return

      // in case of a 0x0 crop : let's display the full image in the preview
      if (data.width + data.height === 0) {
        this.showDefaultThumbnail()
        return
      }

      // default src
      let src = this.media.thumbnail

      this.$nextTick(() => {
        try {
          const crop = cropConversion(data, this.naturalDim, this.originalDim)
          const cropWidth = crop.width
          const cropHeight = crop.height
          this.canvas.width = cropWidth
          this.canvas.height = cropHeight
          this.ctx.drawImage(this.img, crop.x, crop.y, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight)
          src = this.canvas.toDataURL('image/png')

          // show data url in the background
          if (this.cropSrc !== src) {
            this.showImg = false
            this.cropSrc = src
          }
        } catch (error) {
          console.error(error)

          // fallback on displaying the thumbnail
          if (this.cropSrc !== src) {
            this.showImg = true
            this.cropSrc = src
          }
        }
      })
    },
    setDefaultCrops: function () {
      const defaultCrops = {}
      const smarcrops = []

      if (Object.prototype.hasOwnProperty.call(this.allCrops, this.cropContext)) {
        for (const cropVariant in this.allCrops[this.cropContext]) {
          const ratio = this.allCrops[this.cropContext][cropVariant][0].ratio
          const width = this.media.width
          const height = this.media.height
          const center = {
            x: width / 2,
            y: height / 2
          }

          let cropWidth = width
          let cropHeight = height

          if (ratio > 0 && ratio < 1) { // "portrait" crop
            cropWidth = Math.floor(Math.min(height * ratio, width))
            cropHeight = Math.floor(cropWidth / ratio)
          } else if (ratio >= 1) { // "landscape" or square crop
            cropHeight = Math.floor(Math.min(width / ratio, height))
            cropWidth = Math.floor(cropHeight * ratio)
          }

          let crop = {
            x: 0,
            y: 0,
            width: cropWidth,
            height: cropHeight
          }

          // Convert crop for original img values
          crop = cropConversion(crop, this.naturalDim, this.originalDim)

          smarcrops.push(smartCrop.crop(this.img, {
            width: crop.width,
            height: crop.height,
            minScale: 1.0
          }))

          const x = Math.floor(center.x - cropWidth / 2)
          const y = Math.floor(center.y - cropHeight / 2)

          defaultCrops[cropVariant] = {}
          defaultCrops[cropVariant].name = this.allCrops[this.cropContext][cropVariant][0].name || cropVariant
          defaultCrops[cropVariant].x = x
          defaultCrops[cropVariant].y = y
          defaultCrops[cropVariant].width = cropWidth
          defaultCrops[cropVariant].height = cropHeight
        }

        Promise.all(smarcrops).then((values) => {
          let index = 0
          values.forEach((value) => {
            const topCrop = {
              x: value.topCrop.x,
              y: value.topCrop.y,
              width: value.topCrop.width,
              height: value.topCrop.height
            }
            // Restore crop natural values (aka: value to store)
            const cropVariant = defaultCrops[Object.keys(defaultCrops)[index]]
            const crop = cropConversion(topCrop, this.originalDim, this.naturalDim)
            cropVariant.x = crop.x
            cropVariant.y = crop.y
            cropVariant.width = crop.width
            cropVariant.height = crop.height
            index++
          })
          // this.cropMedia({
          //   values: defaultCrops
          // })
        }, (error) => {
          console.error(error)
          // this.cropMedia({
          //   values: defaultCrops
          // })
        })
      } else {
        // this.cropMedia({
        //   values: defaultCrops
        // })
      }
    },
    cropMedia: function (crop) {
      crop.key = this.mediaKey
      crop.index = this.index
      this.$store.commit(MEDIA_LIBRARY.SET_MEDIA_CROP, crop)
      if (this.img) this.canvasCrop()
    },
    setNaturalDimensions: function () {
      if (this.img) {
        this.naturalDim.width = this.img.naturalWidth
        this.naturalDim.height = this.img.naturalHeight
      }
    },
    setOriginalDimensions: function () {
      if (this.media) {
        this.originalDim.width = this.media.width
        this.originalDim.height = this.media.height
      }
    },
    init: function () {
      // this.showImg = false
      const imgLoaded = () => {
        this.setNaturalDimensions()
        this.setOriginalDimensions()

        if (!this.mediaHasCrop) {
          this.setDefaultCrops()
        } else {
          this.canvasCrop()
        }
      }

      if (this.hasMedia) {
        this.cropSrc = this.media.thumbnail

        if (!this.activeCrop) {
          this.showDefaultThumbnail()
          this.hasMediaChanged = false
          return
        }

        const thumbnailSrc = this.mediaThumbnailSrc()
        if (!thumbnailSrc) {
          this.logImageLoadFailure(null, new Error('Missing media thumbnail'))
          this.showDefaultThumbnail()
          this.hasMediaChanged = false
          return
        }

        this.initImg().then(() => {
          imgLoaded()
        }, (error) => {
          this.showDefaultThumbnail()

          // lets try to load to image tag now
          this.$nextTick(() => {
            const imgTag = this.resolveMediaImgElement()
            if (imgTag && typeof imgTag.addEventListener === 'function') {
              imgTag.addEventListener('load', () => {
                this.img = imgTag
                imgLoaded()
              }, {
                once: true,
                passive: true,
                capture: true
              })

              imgTag.addEventListener('error', (e) => {
                this.logImageLoadFailure(thumbnailSrc, e)
                this.showDefaultThumbnail()
              }, {
                once: true,
                passive: true,
                capture: true
              })
            } else {
              this.logImageLoadFailure(thumbnailSrc, error)
              this.showImg = false
              this.cropSrc = this.media.thumbnail
            }
          })
        })
        this.hasMediaChanged = false
      }
    },
    initImg: function (useCrossOrigin = true) {
      const thumbnailSrc = this.mediaThumbnailSrc()
      if (!thumbnailSrc) {
        return Promise.reject(new Error('Missing media thumbnail'))
      }

      return new Promise((resolve, reject) => {
        this.img = new Image()
        if (useCrossOrigin && !IS_SAFARI) {
          this.img.crossOrigin = 'Anonymous'
        }
        this.canvas = document.createElement('canvas')
        this.ctx = this.canvas.getContext('2d')

        this.img.addEventListener('load', () => {
          resolve()
        }, {
          once: true,
          passive: true,
          capture: true
        })

        // in case of CORS issue or anything else
        this.img.addEventListener('error', () => {
          if (useCrossOrigin && !IS_SAFARI) {
            this.initImg(false).then(resolve, reject)
            return
          }

          reject(new Error(`Failed to load media thumbnail: ${thumbnailSrc}`))
        }, {
          once: true,
          passive: true,
          capture: true
        })

        this.img.src = thumbnailSrc
      })
    },
    showDefaultThumbnail: function () {
      this.showImg = true
      if (this.hasMedia) this.cropSrc = this.media.thumbnail
    },
    openCropMedia: function () {
      this.$refs[this.cropModalName].open()
    },
    deleteMediaClick: function (index, callback) {
      // this.isDestroyed = true
      this.deleteMedia(index)

      this.$nextTick(() => callback())
    },
    // delete the media
    deleteMedia: function (index) {
      let newInput = this.input

      newInput.splice(index, 1)

      this.input = newInput

      // this.$store.commit(MEDIA_LIBRARY.DESTROY_SPECIFIC_MEDIA, {
      //   name: this.mediaKey,
      //   index: this.index
      // })
    },
    // metadatas
    updateMetadata: function (newValue) {
      this.$store.commit(MEDIA_LIBRARY.SET_MEDIA_METADATAS, {
        media: {
          context: this.mediaKey,
          index: this.index
        },
        value: newValue
      })
    },
    metadatasInfos: function () {
      this.metadatas.active = !this.metadatas.active
      this.metadatas.text = this.metadatas.active ? this.metadatas.textClose : this.metadatas.textOpen
    },
    fileSizeLabel (media) {
      return `${this.$t('fields.medias.filesize', 'File size')}: ${media.size}`
    },
    originalDimensionsLabel (media) {
      return `${this.$t('fields.medias.original-dimensions')}: ${media.width} × ${media.height}`
    },
    isMetaTextOverflowing (event) {
      const root = event?.currentTarget
      if (!(root instanceof HTMLElement)) {
        return false
      }

      const el = root.querySelector('.v-list-item-title, .v-list-item-subtitle')
      return !!(el && el.scrollWidth > el.clientWidth)
    },
    showOverflowTooltip (event, key) {
      this.overflowTooltip = this.isMetaTextOverflowing(event) ? key : null
    },
    hideOverflowTooltip (key) {
      if (this.overflowTooltip === key) {
        this.overflowTooltip = null
      }
    }
  },
  beforeMount: function () {
    this.init()
  },
  beforeUpdate: function () {
    if (this.hasMediaChanged) {
      this.init()
    }
  }
}
</script>

<style lang="scss" scoped>
.ue-input-image {
  padding-block: 8px;

  &--slide {
    border: 0 none;

    .ue-input-image__thumb {
      max-width: 120px;
    }
  }

  &__item {
    min-width: 0;
    overflow: visible;
  }

  &__thumb {
    position: relative;
    width: 33.33%;
    max-width: 240px;
    min-width: 100px;
    user-select: none;
  }

  &__thumb-frame {
    position: relative;
    width: 100%;
    padding-bottom: 100%;
    overflow: hidden;
    cursor: pointer;
    outline: none;

    &::after {
      content: '';
      position: absolute;
      inset: 0;
      z-index: 1;
      background: rgb(0 0 0 / 0.4);
      opacity: 0;
      pointer-events: none;
      transition: opacity 150ms ease;
    }

    &:hover::after,
    &:focus-visible::after,
    &:focus-within::after {
      opacity: 1;
    }
  }

  &__thumb-center {
    position: absolute;
    inset: 0;
    display: flex;
    background-color: $color__lighter;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    transition: background-image 350ms cubic-bezier(0.795, 0.125, 0.280, 0.990), background-size 0ms 350ms;

    &::before {
      content: '';
      position: absolute;
      inset: 0;
      border: 1px solid rgb(0 0 0 / 0.05);
    }

    :deep(img) {
      display: block;
      max-width: 100%;
      max-height: 100%;
      margin: auto;
    }

    :deep(.ue-input-image__thumb--landscape img) {
      width: 100%;
      height: auto;
    }

    :deep(.ue-input-image__thumb--portrait img) {
      width: auto;
      height: 100%;
    }
  }

  &__overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    padding: 4px;
    pointer-events: none;

    :deep(.v-btn) {
      pointer-events: auto;
    }

    @media (hover: hover) and (pointer: fine) {
      :deep(.v-btn) {
        opacity: 0;
        transition: opacity 150ms ease;
      }

      &--active :deep(.v-btn),
      .ue-input-image__thumb-frame:hover & :deep(.v-btn),
      .ue-input-image__thumb-frame:focus-within & :deep(.v-btn) {
        opacity: 1;
      }
    }
  }

  &__actions {
    min-height: 0;

    :deep(.v-list-item__content) {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      overflow: visible;
    }
  }

  &__meta {
    min-width: 0;
    overflow: hidden;

    :deep(.v-list-item-title),
    :deep(.v-list-item-subtitle) {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  &__meta-item:hover {
    color: rgb(var(--v-theme-primary));
  }

  &__note {
    display: none;
    position: absolute;
    right: 15px;
    bottom: 18px;
    color: $color__text--light;

    @include breakpoint('small+') {
      display: inline-block;
    }

    @include breakpoint('medium') {
      display: none;
    }
  }
}
</style>

<template>
  <div class="mediasidebar">
    <a17-mediasidebar-upload v-if="mediasLoading.length"/>
    <template v-else>

    </template>

    <!-- <a17-modal class="modal--tiny modal--form modal--withintro" ref="warningDelete" title="Warning Delete">
      <p class="modal--tiny-title"><strong>{{ $trans('media-library.dialogs.delete.title', 'Are you sure ?') }}</strong></p>
      <p>{{ warningDeleteMessage }}</p>
      <a17-inputframe>
        <a17-button variant="validate" @click="deleteSelectedMedias">Delete ({{ mediasIdsToDelete.length }})
        </a17-button>
        <a17-button variant="aslink" @click="$refs.warningDelete.close()"><span>Cancel</span></a17-button>
      </a17-inputframe>
    </a17-modal> -->
  </div>
</template>

<script>
  import { mapState } from 'vuex'
  import api from '@/store/api/media-library'
  // import { NOTIFICATION } from '@/store/mutations'
  import isEqual from 'lodash/isEqual'
  import FormDataAsObj from '@/utils/formDataAsObj.js'
  import a17VueFilters from '@/utils/filters.js'
  import a17MediaSidebarUpload from '@/components/media-library/MediaSidebarUpload'
  // import a17Langswitcher from '@/components/LangSwitcher'

  export default {
    name: 'A17MediaSidebar',
    components: {
      'a17-mediasidebar-upload': a17MediaSidebarUpload,
      // 'a17-langswitcher': a17Langswitcher
    },
    props: {
      medias: {
        default: function () { return [] }
      },
      authorized: {
        type: Boolean,
        default: false
      },
      type: {
        type: Object,
        required: true
      },
      extraMetadatas: {
        type: Array,
        default () {
          return []
        }
      },
      translatableMetadatas: {
        type: Array,
        default () {
          return []
        }
      }
    },
    data: function () {
      return {
        loading: false,
        focused: false,
        previousSavedData: {},
        fieldsRemovedFromBulkEditing: []
      }
    },
    filters: a17VueFilters,
    watch: {
      medias: function () {
        this.fieldsRemovedFromBulkEditing = []
      }
    },
    computed: {
      firstMedia: function () {
        return this.hasMedia ? this.medias[0] : null
      },
      hasMultipleMedias: function () {
        return this.medias.length > 1
      },
      hasSingleMedia: function () {
        return this.medias.length === 1
      },
      hasMedia: function () {
        return this.medias.length > 0
      },
      isImage: function () {
        return this.type.value === 'image'
      },
      sharedTags: function () {
        return this.medias.map((media) => {
          return media.tags
        }).reduce((allTags, currentTags) => allTags.filter(tag => currentTags.includes(tag)))
      },
      sharedMetadata () {
        return (name, type) => {
          if (!this.hasMultipleMedias) {
            return typeof this.firstMedia.metadatas.default[name] === 'object' || type === 'boolean' ? this.firstMedia.metadatas.default[name] : {}
          }

          return this.medias.map((media) => {
            return media.metadatas.default[name]
            // eslint-disable-next-line eqeqeq
          }).every((val, i, arr) => Array.isArray(val) ? (val[0] == arr[0]) : (val == arr[0])) ? this.firstMedia.metadatas.default[name] : (type === 'object' ? {} : type === 'boolean' ? false : '')
        }
      },
      captionValues () {
        return typeof this.firstMedia.metadatas.default.caption === 'object' ? this.firstMedia.metadatas.default.caption : {}
      },
      altValues () {
        return typeof this.firstMedia.metadatas.default.altText === 'object' ? this.firstMedia.metadatas.default.altText : {}
      },
      mediasIds: function () {
        return this.medias.map(function (media) { return media.id }).join(',')
      },
      mediasIdsToDelete: function () {
        return this.medias.filter(media => media.deleteUrl).map(media => media.id)
      },
      mediasIdsToDeleteString: function () {
        return this.mediasIdsToDelete.join(',')
      },
      allowDelete: function () {
        return this.medias.every((media) => {
          return media.deleteUrl
        }) || (this.hasMultipleMedias && !this.medias.every((media) => {
          return !media.deleteUrl
        }))
      },
      warningDeleteMessage: function () {
        if (this.allowDelete) {
          if (this.hasMultipleMedias) {
            return this.$trans('media-library.dialogs.delete.allow-delete-multiple-medias', 'Some files are used and can\'t be deleted. Do you want to delete the others ?')
          } else {
            return this.$trans('media-library.dialogs.delete.allow-delete-one-media', 'This file is used and can\'t be deleted. Do you want to delete the others ?')
          }
        } else {
          if (this.hasMultipleMedias) {
            return this.$trans('media-library.dialogs.delete.dont-allow-delete-multiple-medias', 'This files are used and can\'t be deleted.')
          } else {
            return this.$trans('media-library.dialogs.delete.dont-allow-delete-one-media', 'This file is used and can\'t be deleted.')
          }
        }
      },
      containerClasses: function () {
        return {
          'mediasidebar__inner--multi': this.hasMultipleMedias,
          'mediasidebar__inner--single': this.hasSingleMedia
        }
      },
      singleAndMultipleMetadatas: function () {
        return this.extraMetadatas.filter(m => m.multiple && !this.translatableMetadatas.includes(m.name))
      },
      singleOnlyMetadatas: function () {
        return this.extraMetadatas.filter(m => !m.multiple || (m.multiple && this.translatableMetadatas.includes(m.name)))
      },
      ...mapState({
        mediasLoading: state => state.mediaLibrary.loading,
        useWysiwyg: state => state.mediaLibrary.config.useWysiwyg,
        wysiwygOptions: state => state.mediaLibrary.config.wysiwygOptions
      })
    },
    methods: {
      replaceMedia: function () {
        // Open confirm dialog if any
        if (this.$root.$refs.replaceWarningMediaLibrary) {
          this.$root.$refs.replaceWarningMediaLibrary.open(() => {
            this.triggerMediaReplace()
          })
        } else {
          this.triggerMediaReplace()
        }
      },
      triggerMediaReplace: function () {
        this.$emit('triggerMediaReplace', {
          id: this.getMediaToReplaceId()
        })
      },
      deleteSelectedMediasValidation: function () {
        if (this.loading) return false

        if (this.mediasIdsToDelete.length !== this.medias.length) {
          this.$refs.warningDelete.open()
          return
        }

        // Open confirm dialog if any
        if (this.$root.$refs.deleteWarningMediaLibrary) {
          this.$root.$refs.deleteWarningMediaLibrary.open(() => {
            this.deleteSelectedMedias()
          })
        } else {
          this.deleteSelectedMedias()
        }
      },
      deleteSelectedMedias: function () {
        if (this.loading) return false
        this.loading = true

        if (this.hasMultipleMedias) {
          api.bulkDelete(this.firstMedia.deleteBulkUrl, { ids: this.mediasIdsToDeleteString }, (resp) => {
            this.loading = false
            this.$emit('delete', this.mediasIdsToDelete)
            this.$refs.warningDelete.close()
          }, (error) => {
            // this.$store.commit(NOTIFICATION.SET_NOTIF, {
            //   message: error.data.message,
            //   variant: 'error'
            // })
          })
        } else {
          api.delete(this.firstMedia.deleteUrl, (resp) => {
            this.loading = false
            this.$emit('delete', this.mediasIdsToDelete)
            this.$refs.warningDelete.close()
          }, (error) => {
            // this.$store.commit(NOTIFICATION.SET_NOTIF, {
            //   message: error.data.message,
            //   variant: 'error'
            // })
          })
        }
      },
      clear: function () {
        this.$emit('clear')
      },
      getFormData: function (form) {
        return FormDataAsObj(form)
      },
      getMediaToReplaceId: function () {
        return this.firstMedia.id
      },
      removeFieldFromBulkEditing: function (name) {
        this.fieldsRemovedFromBulkEditing.push(name)
      },
      focus: function () {
        this.focused = true
      },
      blur: function () {
        this.focused = false
        this.save()

        const form = this.$refs.form
        const data = this.getFormData(form)

        if (this.hasSingleMedia) {
          if (data.hasOwnProperty('alt_text')) this.firstMedia.metadatas.default.altText = data.alt_text
          else this.firstMedia.metadatas.default.altText = ''

          if (data.hasOwnProperty('caption')) this.firstMedia.metadatas.default.caption = data.caption
          else this.firstMedia.metadatas.default.caption = ''

          this.extraMetadatas.forEach((metadata) => {
            if (data.hasOwnProperty(metadata.name)) {
              this.firstMedia.metadatas.default[metadata.name] = data[metadata.name]
            } else {
              this.firstMedia.metadatas.default[metadata.name] = ''
            }
          })
        } else {
          this.singleAndMultipleMetadatas.forEach((metadata) => {
            if (data.hasOwnProperty(metadata.name)) {
              this.medias.forEach((media) => {
                media.metadatas.default[metadata.name] = data[metadata.name]
              })
            }
          })
        }
      },
      save: function () {
        const form = this.$refs.form
        if (!form) return

        const formData = this.getFormData(form)

        if (!isEqual(formData, this.previousSavedData) && !this.loading) {
          this.previousSavedData = formData
          this.update(form)
        }
      },
      submit: function (event) {
        event.preventDefault()
        this.save()
      },
      update: function (form) {
        if (this.loading) return

        this.loading = true

        const data = this.getFormData(form)
        data.fieldsRemovedFromBulkEditing = this.fieldsRemovedFromBulkEditing

        const url = this.hasMultipleMedias ? this.firstMedia.updateBulkUrl : this.firstMedia.updateUrl // single or multi updates

        api.update(url, data, (resp) => {
          this.loading = false

          // Refresh the select filter displaying all tags
          if (resp.data.tags) this.$emit('tagUpdated', resp.data.tags)

          // Bulk update : Refresh tags
          if (this.hasMultipleMedias && resp.data.items) {
            // Update the tags of all the selected medias
            this.medias.forEach(function (media) {
              resp.data.items.some(function (mediaFromResp) {
                if (mediaFromResp.id === media.id) media.tags = mediaFromResp.tags // replace tags with the one from the response
                return mediaFromResp.id === media.id
              })
            })
          }
        }, (error) => {
          this.loading = false

          if (error.data.message) {
            // this.$store.commit(NOTIFICATION.SET_NOTIF, {
            //   message: error.data.message,
            //   variant: 'error'
            // })
          }
        })
      }
    }
  }
</script>

<style lang="scss" scoped>

  .mediasidebar {
    a {
      color: $color__link;
      text-decoration: none;

      &:focus,
      &:hover {
        text-decoration: underline;
      }
    }
  }

  .mediasidebar__info {
    margin-bottom: 30px;

    a {
      margin-left: 15px;
    }
  }

  .mediasidebar__inner {
    padding: 20px;
    // overflow: hidden;
  }

  .mediasidebar__img {
    max-width: 135px;
    max-height: 135px;
    height: auto;
    display: block;
    margin-bottom: 17px;
  }

  .mediasidebar__name {
    margin-bottom: 6px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .mediasidebar__metadatas {
    color: $color__text--light;
    margin-bottom: 16px;
  }

  .mediasidebar .mediasidebar__buttonbar {
    display: inline-block;
  }

  .mediasidebar__form {
    border-top: 1px solid $color__border;
    position: relative;

    button {
      margin-top: 16px;
    }

    &.mediasidebar__form--loading {
      opacity: 0.5;
    }
  }

  .mediasidebar__loader {
    position: absolute;
    top: 20px;
    right: 20px + 8px + 8px;
  }

  .mediasidebar__checkbox {
    margin-top: 16px;
  }

  .mediasidebar__langswitcher {
    margin-top: 32px;
    margin-bottom: 32px;
  }
</style>

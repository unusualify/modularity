<template>
  <ue-modal
    v-model="show"
    id="modalMedia"
    ref="modalMedia"

    fullscreen
    content-class="bg-primary"

    >
    <template v-slot:activator="{props}">
      <slot
          name="activator"
          :props="{...props}"
          >
      </slot>
    </template>
    <template
        v-slot:body="{props}"
        v-bind="props"
        >

        <div class="medialibrary">
          <div class="medialibrary__frame">

            <v-toolbar
              dark
              color="primary"
            >
              <v-toolbar-title>{{ modalTitle }}</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-toolbar-items>
                <v-btn
                  icon
                  dark
                  @click="closeModal"
                >
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </v-toolbar-items>
            </v-toolbar>
            <div class="medialibrary__header" ref="form">
              <ue-filter @submit="submitFilter" :clearOption="true" @clear="clearFilters">
                <ul class="secondarynav secondarynav--desktop py" slot="navigation" v-if="types.length">

                  <v-chip

                    v-for="navType in types"
                    :key="navType.value"
                    class="ma-2"
                    @click.prevent="updateType(navType.value)"
                    >
                      {{  navType.text }}
                      <span
                        v-if="navType.total > 0" class="secondarynav__number"
                        >
                        ({{ navType.total }})
                      </span>
                  </v-chip>
                  <!-- <li
                    class="secondarynav__item"
                    v-for="navType in types"
                    :key="navType.value"
                    :class="{ 's--on': type === navType.value, 's--disabled' : type !== navType.value && strict }"
                    >
                    <a href="#" @click.prevent="updateType(navType.value)">
                      <span class="secondarynav__link">{{ navType.text }}</span>
                      <span
                        v-if="navType.total > 0" class="secondarynav__number"
                        >
                        ({{ navType.total }})
                      </span>
                    </a>
                  </li> -->
                </ul>

                <!-- <div class="secondarynav secondarynav--mobile secondarynav--dropdown" slot="navigation">
                  <a17-dropdown ref="secondaryNavDropdown" position="bottom-left" width="full" :offset="0">
                    <a17-button class="secondarynav__button" variant="dropdown-transparent" size="small"
                                @click="$refs.secondaryNavDropdown.toggle()" v-if="selectedType">
                      <span class="secondarynav__link">{{ selectedType.text }}</span><span class="secondarynav__number">{{ selectedType.total }}</span>
                    </a17-button>
                    <div slot="dropdown__content">
                      <ul>
                        <li v-for="navType in types" :key="navType.value" class="secondarynav__item">
                          <a href="#" v-on:click.prevent="updateType(navType.value)"><span class="secondarynav__link">{{ navType.text }}</span><span
                            class="secondarynav__number">{{ navType.total }}</span></a>
                        </li>
                      </ul>
                    </div>
                  </a17-dropdown>
                </div> -->

                <div slot="hidden-filters">
                  <!-- <a17-vselect class="medialibrary__filter-item" ref="filter" name="tag" :options="tags"
                              :placeholder="$trans('media-library.filter-select-label', 'Filter by tag')" :searchable="true" maxHeight="175px"/>
                  <a17-checkbox class="medialibrary__filter-item" ref="unused" name="unused" :initial-value="0" :value="1" :label="$trans('media-library.unused-filter-label', 'Show unused only')"/> -->
                </div>
              </ue-filter>
            </div>

            <div class="medialibrary__inner">
              <div class="medialibrary__grid">
                <aside class="medialibrary__sidebar">
                  Side
                </aside>
                <footer class="medialibrary__footer" v-if="selectedMedias.length && showInsert && connector">
                  <!-- <a17-button v-if="canInsert" variant="action" @click="saveAndClose">{{ btnLabel }}</a17-button>
                  <a17-button v-else variant="action" :disabled="true">{{ btnLabel }}</a17-button> -->
                  <ue-btn
                      v-if="canInsert" @click="saveAndClose"
                      >
                      {{ btnLabel }}
                  </ue-btn>
                  <ue-btn
                      v-else
                      :disabled="true"
                      >
                      {{ btnLabel }}
                  </ue-btn>
                </footer>

                <div class="medialibrary__list" ref="list">
                  <ue-uploader
                    ref="uploader"
                    v-if="authorized"
                    @loaded="addMedia"
                    @clear="clearSelectedMedias"
                    :type="currentTypeObject"
                    />
                </div>
              </div>
            </div>
          </div>
        </div>
    </template>
  </ue-modal>
</template>

<script>
import { getCurrentInstance } from 'vue'
import { mapState } from 'vuex'
import { MEDIA_LIBRARY } from '@/store/mutations'

import api from '../../store/api/media-library'

import ACTIONS from '@/store/actions'

import UEModal from './Modal.vue'

import scrollToY from '@/utils/scrollToY.js'

import FormDataAsObj from '@/utils/formDataAsObj.js'

import { ModalMixin } from '@/mixins'

export default {
  mixins: [ModalMixin],
  components: {
    'ue-modal': UEModal
  },
  setup (props, { attrs, slots, emit }) {
    // __log(props, attrs, slots, emit)
  },
  props: {
    modalTitlePrefix: {
      type: String,
      default: function (props) {
        return getCurrentInstance().appContext.config.globalProperties.$trans('media-library.title', 'Media Library')
      }
    },
    btnLabelSingle: {
      type: String,
      default: function () {
        return getCurrentInstance().appContext.config.globalProperties.$trans('media-library.insert', 'Insert')
      }
    },
    btnLabelUpdate: {
      type: String,
      default: function () {
        return getCurrentInstance().appContext.config.globalProperties.$trans('media-library.update', 'Update')
      }
    },
    btnLabelMulti: {
      type: String,
      default: function () {
        return getCurrentInstance().appContext.config.globalProperties.$trans('media-library.insert', 'Insert')
      }
    },
    initialPage: {
      type: Number,
      default: 1
    },
    authorized: {
      type: Boolean,
      default: true
    },
    showInsert: {
      type: Boolean,
      default: true
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
      maxPage: 20,
      mediaItems: [],
      selectedMedias: [],
      gridHeight: 0,
      page: this.initialPage,
      tags: [],
      lastScrollTop: 0,
      gridLoaded: false,

      full: true
    //   show: false
    }
  },
  computed: {
    renderedMediaItems: function () {
      return this.mediaItems.map((item) => {
        item.disabled = (this.filesizeMax > 0 && item.filesizeInMb > this.filesizeMax) ||
            (this.widthMin > 0 && item.width < this.widthMin) ||
            (this.heightMin > 0 && item.height < this.heightMin)
        return item
      })
    },
    currentTypeObject: function () {
      return this.types.find((type) => {
        return type.value === this.type
      })
    },
    endpoint: function () {
      return this.currentTypeObject.endpoint
    },
    modalTitle: function () {
      if (this.connector) {
        if (this.indexToReplace > -1) return this.modalTitlePrefix + ' – ' + this.btnLabelUpdate
        return this.selectedMedias.length > 1 ? this.modalTitlePrefix + ' – ' + this.btnLabelMulti : this.modalTitlePrefix + ' – ' + this.btnLabelSingle
      }
      return this.modalTitlePrefix
    },
    btnLabel: function () {
      let type = getCurrentInstance().appContext.config.globalProperties.$trans('media-library.types.single.' + this.type, this.type)

      if (this.indexToReplace > -1) {
        return this.btnLabelUpdate + ' ' + type
      } else {
        if (this.selectedMedias.length > 1) {
          type = getCurrentInstance().appContext.config.globalProperties.$trans('media-library.types.multiple.' + this.type, this.type)
        }

        return this.btnLabelSingle + ' ' + type
      }
    },
    usedMedias: function () {
      return this.selected[this.connector] || []
    },
    selectedType: function () {
      const self = this
      const navItem = self.types.filter(function (t) {
        return t.value === self.type
      })
      return navItem[0]
    },
    canInsert: function () {
      return !this.selectedMedias.some(sMedia => !!this.usedMedias.find(uMedia => uMedia.id === sMedia.id))
    },
    ...mapState({
      connector: state => state.mediaLibrary.connector,
      max: state => state.mediaLibrary.max,
      filesizeMax: state => state.mediaLibrary.filesizeMax,
      widthMin: state => state.mediaLibrary.widthMin,
      heightMin: state => state.mediaLibrary.heightMin,
      type: state => state.mediaLibrary.type, // image, video, file
      types: state => state.mediaLibrary.types,
      strict: state => state.mediaLibrary.strict,
      selected: state => state.mediaLibrary.selected,
      indexToReplace: state => state.mediaLibrary.indexToReplace

      // showModal: state => state.mediaLibrary.showModal,

      // show: state => state.mediaLibrary.showModal,
    })

    // show: {
    //     get () {
    //         return this.$store.state.mediaLibrary.showModal;
    //     },
    //     set (value) {
    //         // this.$store.dispatch(ACTIONS.TOGGLE_MEDIA_MODAL, value)
    //     }
    // },
  },
  watch: {
    type: function () {
      this.clearMediaItems()
      this.gridLoaded = false
    }
  },
  methods: {

    // for ue-uploader
    addMedia: function (media) {
      const index = this.mediaItems.findIndex(function (item) {
        return item.id === media.id
      })

      // Check of the media item exists i.e replacement
      if (index > -1) {
        for (const mediaRole in this.selected) {
          this.selected[mediaRole].forEach((mediaCrop, index) => {
            if (media.id === mediaCrop.id) {
              const crops = []

              for (const crop in mediaCrop.crops) {
                crops[crop] = {
                  height: media.height === mediaCrop.height ? mediaCrop.crops[crop].height : media.height,
                  name: crop,
                  width: media.width === mediaCrop.width ? mediaCrop.crops[crop].width : media.width,
                  x: media.width === mediaCrop.width ? mediaCrop.crops[crop].x : 0,
                  y: media.height === mediaCrop.height ? mediaCrop.crops[crop].y : 0
                }
              }

              this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIAS, {
                index,
                media: {
                  ...media,
                  width: media.width === mediaCrop.width ? mediaCrop.width : media.width,
                  height: media.height === mediaCrop.height ? mediaCrop.height : media.height,
                  crops
                },
                mediaRole
              })
            }
          })
        }

        this.$set(this.mediaItems, index, media)
        this.selectedMedias.unshift(media)
      } else {
        // add media in first position of the available media
        this.mediaItems.unshift(media)
        this.$store.commit(MEDIA_LIBRARY.INCREMENT_MEDIA_TYPE_TOTAL, this.type)
        // select it
        this.updateSelectedMedias(media.id)
      }
    },
    updateSelectedMedias: function (item, shift = false) {
      const id = item.id
      const alreadySelectedMedia = this.selectedMedias.filter(function (media) {
        return media.id === id
      })

      // not already selected
      if (alreadySelectedMedia.length === 0) {
        if (this.max === 1) this.clearSelectedMedias()
        if (this.selectedMedias.length >= this.max && this.max > 0) return

        if (shift && this.selectedMedias.length > 0) {
          const lastSelectedMedia = this.selectedMedias[this.selectedMedias.length - 1]
          const lastSelectedMediaIndex = this.mediaItems.findIndex((media) => media.id === lastSelectedMedia.id)
          const selectedMediaIndex = this.mediaItems.findIndex((media) => media.id === id)
          if (selectedMediaIndex === -1 && lastSelectedMediaIndex === -1) return

          let start = null
          let end = null
          if (lastSelectedMediaIndex < selectedMediaIndex) {
            start = lastSelectedMediaIndex + 1
            end = selectedMediaIndex + 1
          } else {
            start = selectedMediaIndex
            end = lastSelectedMediaIndex
          }

          const selectedMedias = this.mediaItems.slice(start, end)

          selectedMedias.forEach((media) => {
            if (this.selectedMedias.length >= this.max && this.max > 0) return
            const index = this.selectedMedias.findIndex((m) => m.id === media.id)
            if (index === -1) {
              this.selectedMedias.push(media)
            }
          })
        } else {
          const mediaToSelect = this.mediaItems.filter(function (media) {
            return media.id === id
          })

          // Add one media to the selected media
          if (mediaToSelect.length) this.selectedMedias.push(mediaToSelect[0])
        }
      } else {
        // Remove one item from the selected media
        this.selectedMedias = this.selectedMedias.filter(function (media) {
          return media.id !== id
        })
      }
    },
    clearSelectedMedias: function () {
      this.selectedMedias.splice(0)
    },

    // for ue-filter
    clearFilters: function () {
      const self = this
      // reset tags
      if (this.$refs.filter) this.$refs.filter.value = null
      // reset unused field
      if (this.$refs.unused) {
        const input = this.$refs.unused.$el.querySelector('input')
        input && input.checked && input.click()
      }

      this.$nextTick(function () {
        self.submitFilter()
      })
    },
    submitFilter: function (formData) {
      const self = this
      const el = this.$refs.list
      // when changing filters, reset the page to 1
      this.page = 1

      this.clearMediaItems()
      this.clearSelectedMedias()

      if (el.scrollTop === 0) {
        self.reloadGrid()
        return
      }

      scrollToY({
        el,
        offset: 0,
        easing: 'easeOut',
        onComplete: function () {
          self.reloadGrid()
        }
      })
    },
    clearMediaItems: function () {
      this.mediaItems.splice(0)
    },
    clearSelectedMedias: function () {
      this.selectedMedias.splice(0)
    },
    reloadGrid: function () {
      this.loading = true

      const form = this.$refs.form
      const formdata = this.getFormData(form)

      // if (this.selected[this.connector]) {
      //   formdata.except = this.selected[this.connector].map((media) => {
      //     return media.id
      //   })
      //   console.log(formdata.except)
      // }

      // see api/media-library for actual ajax
      api.get(this.endpoint, formdata, (resp) => {
        // add medias here
        resp.data.items.forEach(item => {
          if (!this.mediaItems.find(media => media.id === item.id)) {
            this.mediaItems.push(item)
          }
        })
        this.maxPage = resp.data.maxPage || 1
        this.tags = resp.data.tags || []
        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE_TOTAL, { type: this.type, total: resp.data.total })
        this.loading = false
        this.listenScrollPosition()
        this.gridLoaded = true
      }, (error) => {
        // this.$store.commit(NOTIFICATION.SET_NOTIF, {
        //   message: error.data.message,
        //   variant: 'error'
        // })
      })
    },
    updateType: function (newType) {
      if (this.loading) return
      if (this.strict) return
      if (this.type === newType) return

      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE, newType)
      this.submitFilter()
    },

    getFormData: function (form) {
      let data = FormDataAsObj(form)

      if (data) data.page = this.page
      else data = { page: this.page }

      data.type = this.type

      if (Array.isArray(data.unused) && data.unused.length) {
        data.unused = data.unused[0]
      }

      return data
    }

  },

  created () {

  }
}
</script>

<style lang="scss" scoped>

  $width_sidebar: (default: 290px, small: 250px, xsmall: 200px);

  .medialibrary {
    display: block;
    width: 100%;
    min-height: 100%;
    padding: 0;
    position: relative;
  }

  .medialibrary__header {
    background: $color__border--light;
    border-bottom: 1px solid $color__border;
    padding: 0 20px;

    @include breakpoint(small-) {
      .secondarynav {
        padding-bottom: 10px;
      }
    }
  }

  .medialibrary__frame {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-flow: column nowrap;
  }

  .medialibrary__inner {
    position: relative;
    width: 100%;
    overflow: hidden;
    flex-grow: 1;
  }

  .medialibrary__footer {
    position: absolute;
    right: 0;
    z-index: 76;
    bottom: 0;
    width: map-get($width_sidebar, default); // fixed arbitrary width
    color: $color__text--light;
    padding: 10px;
    overflow: hidden;
    background: $color__border--light;
    border-top: 1px solid $color__border;

    > button {
      display: block;
      width: 100%;
    }

    @include breakpoint(small) {
      width: map-get($width_sidebar, small);
    }

    @include breakpoint(xsmall) {
      width: map-get($width_sidebar, xsmall);
    }

    @media screen and (max-width: 550px) {
      width: 100%;
    }
  }

  .medialibrary__sidebar {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: map-get($width_sidebar, default); // fixed arbitrary width
    padding: 0 0 80px 0; // 80px so we have some room to display the tags dropdown menu under the field
    z-index: 75;
    background: $color__border--light;
    overflow: auto;

    @include breakpoint(small) {
      width: map-get($width_sidebar, small);
    }

    @include breakpoint(xsmall) {
      width: map-get($width_sidebar, xsmall);
    }

    @media screen and (max-width: 550px) {
      display: none;
    }
  }

  .medialibrary__list {
    margin: 0;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: auto;
    padding: 10px;
  }

  .medialibrary__list-items {
    position: relative;
    display: block;
    width: 100%;
    min-height: 100%;
  }

  /* with a sidebar visible */
  .medialibrary__list {
    right: map-get($width_sidebar, default);

    @include breakpoint(small) {
      right: map-get($width_sidebar, small);
    }

    @include breakpoint(xsmall) {
      right: map-get($width_sidebar, xsmall);
    }

    @media screen and (max-width: 550px) {
      right: 0;
    }
  }
</style>

<style lang="scss">

  .medialibrary__filter-item {
    .vselect {
      min-width: 200px;
    }
  }

  .medialibrary__filter-item.checkbox {
    margin-top: 8px;
    margin-right: 45px !important;
  }

  .medialibrary__header {
    @include breakpoint(small-) {
      .filter__inner {
        flex-direction: column;
      }

      .filter__search, .filter__navigation {
        padding-top: 10px;
        display: flex;
      }

      .filter__search input {
        flex-grow: 1;
      }
    }
  }
</style>

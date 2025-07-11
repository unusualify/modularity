<template>
  <ue-modal
    ref="modalMedia"
    id="modalMedia"
    v-model="show"
    fullscreen
    content-class=""
    width=""
    @opened="opened"
    eager
  >
    <template v-slot:activator="{props}">
      <slot name="activator" :props="{...props}"></slot>
    </template>
    <template v-slot:body="{props}" v-bind="props">

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
            <ue-filter
              @submit="submitFilter"
              :clearOption="true"
              @clear="clearFilters"
              v-model:filterState="sharedFilterState"
              >
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
                            :placeholder="$trans('media-library.filter-select-label', 'Filter by tag')" :searchable="true" maxHeight="175px"
                      />
                <a17-checkbox class="medialibrary__filter-item" ref="unused" name="unused" :initial-value="0" :value="1" :label="$trans('media-library.unused-filter-label', 'Show unused only')"/> -->
              </div>

            </ue-filter>
            <ue-dropdown-filter
              @submit="submitFilter"
              @clear="clearDropdownFilters"
              :loading="loading"
              :filter-ref="$refs.filter"

              v-model:filterState="sharedFilterState"
              :schema="filterSchema"
            >

            </ue-dropdown-filter>
          </div>

          <div class="medialibrary__inner">
            <div class="medialibrary__grid">
              <aside class="medialibrary__sidebar">
                <MediaSidebar :medias="selectedMedias"
                  :authorized="authorized"
                  :extraMetadatas="extraMetadatas"
                  @clear="clearSelectedMedias"
                  @delete="deleteSelectedMedias"
                  @tagUpdated="reloadTags"
                  :type="currentTypeObject"
                  :translatableMetadatas="translatableMetadatas"
                  @triggerMediaReplace="replaceMedia"
                >
                </MediaSidebar>
              </aside>
              <footer class="medialibrary__footer" v-if="selectedMedias.length && showInsert && connector">
                <v-btn  v-if="canInsert" @click="saveAndClose">{{ btnLabel }} </v-btn>
                <v-btn v-else :disabled="true" > {{ btnLabel }} </v-btn>
              </footer>

              <div class="medialibrary__list" ref="list">
                <ue-uploader
                  ref="uploader"
                  v-if="authorized"
                  @loaded="addMedia"
                  @clear="clearSelectedMedias"
                  :type="currentTypeObject"
                />
                <!-- TEST START -->
                <div class="medialibrary__list-items">
                  <ItemList v-if="type === 'file'" :items="renderedMediaItems" :selected-items="selectedMedias"
                                :used-items="usedMedias" @change="updateSelectedMedias"
                                @shiftChange="updateSelectedMedias"/>
                  <MediaGrid v-else :items="renderedMediaItems" :selected-items="selectedMedias" :used-items="usedMedias"
                                @change="updateSelectedMedias" @shiftChange="updateSelectedMedias"/>
                  <!-- <a17-spinner v-if="loading" class="medialibrary__spinner">Loading&hellip;</a17-spinner> -->
                </div>
                <!-- TEST END -->
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

import { ModalMixin } from '@/mixins'

import api from '@/store/api/media-library'

import scrollToY from '@/utils/scrollToY.js'
import FormDataAsObj from '@/utils/formDataAsObj.js'

// TEST START
import MediaGrid from './media-library/MediaGrid.vue'
import ItemList from './media-library/ItemList.vue'
import MediaSidebar from './media-library/MediaSidebar.vue'
import { makeModalMediaProps } from '@/hooks/useModal'
// import a17Checkbox from '@/components/Checkbox.vue'

// TEST END
export default {
  mixins: [ModalMixin],
  components: {
    MediaGrid,
    ItemList,
    MediaSidebar
    // 'a17-spinner': a17Spinner
    // 'a17-checkbox': a17Checkbox
  },
  setup (props, { attrs, slots, emit }) {

  },
  props: {
    ...makeModalMediaProps(),
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
      full: true,
      sharedFilterState: {
        search: '',
        tag: null,
        type: this.type ?? "image",
        page: this.page ?? this.initialPage
      },



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
    }),
    filterSchema: function(){
      return {
        tag: {
          type: "select",
          name: "tag",
          label: "Filter by tags",
          items: this.tags,
          variant: "outlined",
          clearable: "true",
          chips: "true",
          col: "12"
        },
        // clearBtn: {
        //   type: "btn",
        //   label: "Clear Filters",
        //   loading: this.loading,
        //   click: "handleClear"
        // }
      }
    }

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
      this.sharedFilterState.type = this.type;
    },
    page: function (newPage) {
      this.sharedFilterState.page = newPage;
    }
  },
  methods: {
    deleteSelectedMedias: function (mediasIds) {
      let keepSelectedMedias = []
      if (mediasIds && mediasIds.length !== this.selectedMedias.length) {
        keepSelectedMedias = this.selectedMedias.filter((media) => !media.deleteUrl)
      }
      mediasIds.forEach(() => {
        this.$store.commit(MEDIA_LIBRARY.DECREMENT_MEDIA_TYPE_TOTAL, this.type)
      })
      this.mediaItems = this.mediaItems.filter((media) => {
        return !this.selectedMedias.includes(media) || keepSelectedMedias.includes(media)
      })
      this.selectedMedias = keepSelectedMedias
      if (this.mediaItems.length <= 40) {
        this.reloadGrid()
      }
    },
    replaceMedia: function ({ id }) {
      this.$refs.uploader.replaceMedia(id)
    },
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
    open: function () {
      this.$refs.modal.open()
    },
    opened: function () {
      if (!this.gridLoaded) {
        this.reloadGrid()
      }

      // this.listenScrollPosition()

      // empty selected medias (to avoid gs when adding)
      this.selectedMedias = []

      // in replace mode : select the media to replace when opening
      if (this.connector && this.indexToReplace > -1) {
        const mediaInitSelect = this.selected[this.connector][this.indexToReplace]
        if (mediaInitSelect) {
          this.selectedMedias.push(mediaInitSelect)
        }
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
      this.sharedFilterState = {
        tag: null, // Set tag to null when clearing
        type: this.type,
        page: this.page
      };
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
    clearDropdownFilters: function () {
      const self = this
      // reset tags
      this.sharedFilterState = {
        tag: null, // Set tag to null when clearing
        type: this.type,
        page: this.page
      };
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
    reloadGrid: function () {
      const self = this;
      this.loading = true
      // let formdata = null;
      const form = this.$refs.form
      const formdata = self.cleanEmptyFilters(this.sharedFilterState);

      // see api/media-library for actual ajax
      api.get(this.endpoint, formdata, (resp) => {
        // add medias here
        resp.data.items.forEach(item => {
          if (!this.mediaItems.find(media => media.id === item.id)) {
            this.mediaItems.push(item)
          }
        })
        this.maxPage = resp.data.maxPage || 1
        let regularArray = resp.data.tags.map(({label, ...rest}) => ({
          title: label,
          ...rest
        }));
        this.tags = regularArray || []

        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE_TOTAL, { type: this.type, total: resp.data.total })
        this.loading = false
        // this.listenScrollPosition()
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
    },
    listenScrollPosition: function () {
      // re-listen for scroll position
      this.$nextTick(function () {
        if (!this.gridLoaded) return

        const list = this.$refs.list
        if (this.gridHeight !== list.scrollHeight) {
          list.addEventListener('scroll', this.scrollToPaginate)
        }
      })
    },
    scrollToPaginate: function () {
      if (!this.gridLoaded) return

      const list = this.$refs.list
      const offset = 10

      if (list.scrollTop > this.lastScrollTop && list.scrollTop + list.offsetHeight > list.scrollHeight - offset) {
        list.removeEventListener('scroll', this.scrollToPaginate)

        if (this.maxPage > this.page) {
          this.page = this.page + 1
          this.reloadGrid()
        } else {
          this.gridHeight = list.scrollHeight
        }
      }

      this.lastScrollTop = list.scrollTop
    },
    saveAndClose: function () {
      this.$store.commit(MEDIA_LIBRARY.UPDATE_IS_INSERTED, true)
      this.$store.commit(MEDIA_LIBRARY.SAVE_MEDIAS, this.selectedMedias)
      this.$nextTick(() => { this.closeModal() })

      // this.closeModal()
    },

    reloadTags: function (tags = []) {
      this.tags = tags
    },
    cleanEmptyFilters: function(obj) {
      return Object.entries(obj).reduce((acc, [key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          if (typeof value === 'object' && !Array.isArray(value)) {
            acc[key] = this.cleanEmptyFilters(value); // Recursively clean nested objects
          } else {
            acc[key] = value;
          }
        }
        return acc;
      }, {});
    }
  },

  created () {
    if (!this.gridLoaded) {
      this.reloadGrid()
    }

    // empty selected medias (to avoid gs when adding)
    this.selectedMedias = []

    // in replace mode : select the media to replace when opening
    if (this.connector && this.indexToReplace > -1) {
      const mediaInitSelect = this.selected[this.connector][this.indexToReplace]
      if (mediaInitSelect) {
        this.selectedMedias.push(mediaInitSelect)
      }
    }
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
    display:flex;
    flex-flow:row;
    align-items: center;
    justify-content: space-between;

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
    background:$color__border--light;
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

<template>
  <ue-modal
    ref="modalMedia"
    id="modalMedia"
    v-model="dialog"
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
          <div class="medialibrary__header pa-4" ref="form">
            <div class="medialibrary__header-inner">
              <ul class="secondarynav secondarynav--desktop py medialibrary__nav" v-if="types.length">
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

              <div class="medialibrary__filters medialibrary__filters--desktop">
                <v-text-field
                  v-model="sharedFilterState.search"
                  type="search"
                  class="medialibrary__search"
                  :placeholder="$trans('filter.search-placeholder', 'Search')"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                  clearable
                  @keyup.enter="submitFilter"
                />
                <v-autocomplete
                  v-model="sharedFilterState.tag"
                  :items="normalizedTags"
                  item-title="title"
                  item-value="value"
                  class="medialibrary__tag-filter"
                  :label="$trans('media-library.filter-select-label', 'Filter by tag')"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                  clearable
                />
                <v-btn
                  color="primary"
                  variant="flat"
                  :loading="loading"
                  @click="submitFilter"
                >
                  {{ $trans('filter.apply-btn', 'Apply Filters') }}
                </v-btn>
                <v-btn
                  color="secondary"
                  variant="outlined"
                  :loading="loading"
                  @click="clearDropdownFilters"
                >
                  {{ $trans('filter.clear-btn', 'Clear Filters') }}
                </v-btn>
              </div>

              <div class="medialibrary__filters medialibrary__filters--mobile">
                <v-menu v-model="mobileFiltersOpen" :close-on-content-click="false" location="bottom end">
                  <template #activator="{ props }">
                    <v-btn v-bind="props" variant="outlined" append-icon="mdi-chevron-down">
                      {{ $trans('filter.toggle-label', 'Filters') }}
                    </v-btn>
                  </template>
                  <v-card min-width="320" class="medialibrary__filters-card">
                    <v-card-text class="d-flex flex-column ga-3">
                      <v-text-field
                        v-model="sharedFilterState.search"
                        type="search"
                        :placeholder="$trans('filter.search-placeholder', 'Search')"
                        variant="outlined"
                        density="compact"
                        hide-details="auto"
                        clearable
                        @keyup.enter="applyMobileFilters"
                      />
                      <v-autocomplete
                        v-model="sharedFilterState.tag"
                        :items="normalizedTags"
                        item-title="title"
                        item-value="value"
                        :label="$trans('media-library.filter-select-label', 'Filter by tag')"
                        variant="outlined"
                        density="compact"
                        hide-details="auto"
                        clearable
                      />
                      <div class="d-flex flex-wrap ga-2">
                        <v-btn
                          color="primary"
                          variant="flat"
                          :loading="loading"
                          @click="applyMobileFilters"
                        >
                          {{ $trans('filter.apply-btn', 'Apply Filters') }}
                        </v-btn>
                        <v-btn
                          color="secondary"
                          variant="outlined"
                          :loading="loading"
                          @click="clearMobileFilters"
                        >
                          {{ $trans('filter.clear-btn', 'Clear Filters') }}
                        </v-btn>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-menu>
              </div>
            </div>
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
                  <div v-if="loading" class="medialibrary__spinner text-center py-4">
                    <v-progress-circular indeterminate color="primary" size="32" />
                  </div>
                  <p
                    v-else-if="gridLoaded && maxPage > 1 && page >= maxPage"
                    class="text-center text-medium-emphasis py-3 text-caption"
                  >
                    {{ $t('media-library.end-of-list', 'All items loaded') }}
                  </p>
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

import api from '@/store/api/media-library'

import scrollToY from '@/utils/scrollToY.js'
import FormDataAsObj from '@/utils/formDataAsObj.js'

// TEST START
import MediaGrid from './media-library/MediaGrid.vue'
import ItemList from './media-library/ItemList.vue'
import MediaSidebar from './media-library/MediaSidebar.vue'
import useModal, { makeModalProps, makeModalMediaProps } from '@/hooks/useModal'

import { useUser } from '@/hooks'
// import a17Checkbox from '@/components/Checkbox.vue'

// TEST END
export default {
  components: {
    MediaGrid,
    ItemList,
    MediaSidebar
    // 'a17-spinner': a17Spinner
    // 'a17-checkbox': a17Checkbox
  },
  setup (props, context) {
    const { isGuest } = useUser()
    const modal = useModal(props, context)

    return {
      isGuest,
      ...modal
    }
  },
  props: {
    ...makeModalProps(),
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
      maxPage: 1,
      /** Matches MediaLibraryController::$perPage / FileLibraryController::$perPage */
      itemsPerPage: 40,
      mediaItems: [],
      selectedMedias: [],
      gridHeight: 0,
      page: this.initialPage,
      minPage: this.initialPage,
      tags: [],
      lastScrollTop: 0,
      gridLoaded: false,
      scrollListenerAttached: false,
      full: true,
      sharedFilterState: {
        search: '',
        tag: null,
        type: this.type ?? "image",
        page: this.page ?? this.initialPage
      },
      mobileFiltersOpen: false,


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
      // `types` is hydrated from window.<APP_NAME>.STORE.medias.types which may
      // not be populated yet when this modal is created (Inertia v3 changed the
      // page bootstrap timing). Return undefined and let reloadGrid bail; the
      // watcher below re-runs the load once the store is ready.
      return this.currentTypeObject?.endpoint
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
    normalizedTags: function () {
      return (this.tags || []).map(({ label, title, value }) => ({
        title: title || label,
        value
      }))
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
  },
  watch: {
    type: function () {
      this.clearMediaItems()
      this.gridLoaded = false
      this.sharedFilterState.type = this.type;
    },
    page: function (newPage) {
      this.sharedFilterState.page = newPage;
    },
    // If `types` was not hydrated yet during created(), reloadGrid bailed out.
    // Re-run it as soon as a real endpoint becomes available.
    endpoint: function (newEndpoint, oldEndpoint) {
      if (newEndpoint && !oldEndpoint && !this.gridLoaded) {
        this.reloadGrid({ reset: true })
      }
    }
  },
  methods: {
    getFocusMediaId () {
      if (!this.connector || this.indexToReplace < 0) {
        return null
      }

      const media = this.selected[this.connector]?.[this.indexToReplace]

      return media?.id ?? null
    },
    buildGridRequestParams ({ includeFocusId = false, page = null } = {}) {
      const params = {
        ...this.cleanEmptyFilters(this.sharedFilterState),
        page: page ?? this.page,
        itemsPerPage: this.itemsPerPage,
        type: this.type
      }

      if (includeFocusId) {
        const focusId = this.getFocusMediaId()
        if (focusId) {
          params.id = focusId
        }
      }

      return params
    },
    resetPagination () {
      this.page = 1
      this.minPage = 1
      this.sharedFilterState.page = 1
      this.maxPage = 1
      this.gridHeight = 0
      this.lastScrollTop = 0
    },
    detachScrollPagination () {
      const list = this.$refs.list
      if (list && this.scrollListenerAttached) {
        list.removeEventListener('scroll', this.scrollToPaginate)
        this.scrollListenerAttached = false
      }
    },
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
      const focusId = this.getFocusMediaId()

      if (focusId) {
        this.resetPagination()
        this.clearMediaItems()
        this.gridLoaded = false
        this.reloadGrid({ reset: false, includeFocusId: true })
      } else if (!this.gridLoaded) {
        this.reloadGrid({ reset: true })
      } else {
        this.listenScrollPosition()
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
      this.sharedFilterState = {
        search: '',
        tag: null,
        type: this.type,
        page: 1
      }

      this.$nextTick(() => {
        this.submitFilter()
      })
    },
    applyMobileFilters: function () {
      this.mobileFiltersOpen = false
      this.submitFilter()
    },
    clearMobileFilters: function () {
      this.mobileFiltersOpen = false
      this.clearDropdownFilters()
    },
    submitFilter: function (formData) {
      const self = this
      const el = this.$refs.list
      // when changing filters, reset the page to 1
      this.resetPagination()
      this.gridLoaded = false
      this.detachScrollPagination()

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
    reloadGrid: function ({ reset = false, includeFocusId = false, direction = 'initial' } = {}) {
      if (this.isGuest) {
        return
      }
      // Guard: types may not be hydrated yet on first created() tick. The
      // `endpoint` watcher re-invokes this once the store fills in.
      if (!this.endpoint) {
        return
      }

      if (reset) {
        this.clearMediaItems()
      }

      if (direction === 'next' && this.page >= this.maxPage) {
        return
      }

      if (direction === 'prev' && this.minPage <= 1) {
        return
      }

      const list = this.$refs.list
      const previousScrollHeight = list?.scrollHeight ?? 0
      const previousScrollTop = list?.scrollTop ?? 0

      let requestPage = this.page
      if (direction === 'next') {
        requestPage = this.page + 1
      } else if (direction === 'prev') {
        requestPage = this.minPage - 1
      }

      const self = this
      this.loading = true
      const formdata = self.buildGridRequestParams({
        includeFocusId: includeFocusId && direction === 'initial',
        page: requestPage
      })

      // see api/media-library for actual ajax
      api.get(this.endpoint, formdata, (resp) => {
        const items = resp.data?.items ?? []
        const loadedPage = resp.data.page || requestPage

        if (direction === 'prev') {
          const newItems = items.filter(item => !this.mediaItems.find(media => media.id === item.id))
          this.mediaItems = [...newItems, ...this.mediaItems]
          this.minPage = loadedPage
        } else {
          items.forEach(item => {
            if (!this.mediaItems.find(media => media.id === item.id)) {
              this.mediaItems.push(item)
            }
          })
          this.page = loadedPage
          this.sharedFilterState.page = loadedPage

          if (direction === 'initial') {
            this.minPage = loadedPage
          }
        }

        this.maxPage = resp.data.maxPage || 1

        const tagList = resp.data.tags ?? []
        this.tags = tagList.map(({ label, ...rest }) => ({
          title: label,
          ...rest
        }))

        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE_TOTAL, { type: this.type, total: resp.data.total })
        this.loading = false
        this.gridLoaded = true

        if (direction === 'prev') {
          self.$nextTick(() => {
            if (list) {
              list.scrollTop = previousScrollTop + (list.scrollHeight - previousScrollHeight)
            }
            self.listenScrollPosition()
          })
        } else {
          self.listenScrollPosition()
        }
      }, () => {
        this.loading = false
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
      this.$nextTick(() => {
        if (!this.gridLoaded || this.loading) {
          return
        }

        const list = this.$refs.list
        if (!list) {
          return
        }

        if (this.gridHeight !== list.scrollHeight) {
          this.detachScrollPagination()
          list.addEventListener('scroll', this.scrollToPaginate, { passive: true })
          this.scrollListenerAttached = true
          this.gridHeight = list.scrollHeight
        }
      })
    },
    scrollToPaginate: function () {
      if (!this.gridLoaded || this.loading) {
        return
      }

      const list = this.$refs.list
      if (!list) {
        return
      }

      const offset = 120
      const scrollingDown = list.scrollTop > this.lastScrollTop
      const scrollingUp = list.scrollTop < this.lastScrollTop

      if (scrollingDown && list.scrollTop + list.clientHeight >= list.scrollHeight - offset) {
        this.detachScrollPagination()

        if (this.maxPage > this.page) {
          this.reloadGrid({ direction: 'next' })
        } else {
          this.gridHeight = list.scrollHeight
        }
      } else if (scrollingUp && list.scrollTop <= offset && this.minPage > 1) {
        this.detachScrollPagination()
        this.reloadGrid({ direction: 'prev' })
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
      this.tags = (tags || []).map(({ label, title, ...rest }) => ({
        title: title || label,
        ...rest
      }))
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
    this.selectedMedias = []
  },
  beforeUnmount () {
    this.detachScrollPagination()
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
  }

  .medialibrary__header-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
  }

  .medialibrary__nav {
    flex: 1 1 auto;
    margin: 0;
    padding-left: 0;
  }

  .medialibrary__filters {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .medialibrary__filters--desktop {
    @include breakpoint(small-) {
      display: none;
    }
  }

  .medialibrary__filters--mobile {
    display: none;

    @include breakpoint(small-) {
      display: flex;
      width: 100%;
      justify-content: flex-end;
    }
  }

  .medialibrary__search {
    min-width: 180px;
    max-width: 260px;
  }

  .medialibrary__tag-filter {
    min-width: 200px;
    max-width: 260px;
  }

  .medialibrary__filters-card {
    padding-top: 4px;
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

  .medialibrary__header {
    @include breakpoint(small-) {
      .secondarynav {
        padding-bottom: 10px;
      }
    }
  }
</style>

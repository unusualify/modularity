<template>
  <!-- <div v-if="false">
    <a17-inputframe :error="error" :label="label" :locale="locale" @localize="updateLocale" :size="size" :name="name" :note="fieldNote" v-if="false">
      <div class="fileField">
        <div class="fileField__trigger" v-if="buttonOnTop && remainingItems">
          <input type="hidden" :name="name" :value="itemsIds"/>
          <a17-button type="button" variant="ghost" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</a17-button>
          <span class="fileField__note f--small">{{ note }}</span>
        </div>
        <table class="fileField__list" v-if="items.length">
          <draggable :tag="'tbody'" v-model="items">
            <ue-fileitem v-for="(item, index) in items" :key="item.id" class="item__content" :name="`${name}_${item.id}`" :draggable="isDraggable" :item="item" @delete="deleteItem(index)"></ue-fileitem>
          </draggable>
        </table>
        <div class="fileField__trigger" v-if="!buttonOnTop && remainingItems">
          <input type="hidden" :name="name" :value="itemsIds"/>
          <a17-button type="button" variant="ghost" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</a17-button>
          <span class="fileField__note f--small">{{ note }}</span>
        </div>
      </div>
    </a17-inputframe>
  </div> -->
  <v-input
    hideDetails="auto"
    :messages="['Messages']"
    appendIcon="mdi-close"
    prependIcon="mdi-phone"
  >
    <div class="fileField">
      <div class="fileField__trigger" v-if="buttonOnTop && remainingItems">
        <input type="hidden" :name="name" :value="itemsIds"/>
        <v-btn type="button" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</v-btn>
        <span class="fileField__note f--small">{{ note }}</span>
      </div>
      <table class="fileField__list" v-if="input.length">
        <!-- <draggable :tag="'tbody'" v-model="items" itemKey="id"> -->
        <draggable :tag="'tbody'" v-model="input" itemKey="id">
          <!-- <FileItem
            v-for="(item, index) in items"
            :key="item.id"
            class="item__content"
            :name="`${name}_${item.id}`"
            :draggable="isDraggable"
            :item="item"
            @delete="deleteItem(index)">
          </FileItem> -->
          <template #item="itemSlot">
            <FileItem
              class="item__content"
              :name="`${name}_${itemSlot.index}`"
              :item-label="$t('form-labels.File')"
              :item="input[itemSlot.index]"
              @delete="deleteItem(itemSlot.index)"
              >
            </FileItem>
          </template>
        </draggable>
      </table>
      <div class="fileField__trigger" v-if="!buttonOnTop && remainingItems">
        <input type="hidden" :name="name" :value="itemsIds"/>
        <v-btn type="button" @click="openMediaLibrary(remainingItems)">{{ addLabel }}</v-btn>
        <span class="fileField__note f--small">{{ note }}</span>
      </div>
    </div>
  </v-input>

</template>
<script>
import { MEDIA_LIBRARY } from '@/store/mutations'
import draggable from 'vuedraggable'
import { makeFileProps, useFile } from '@/hooks'

import { InputMixin } from '@/mixins'

import localeMixin from '@/mixins/locale'
// import draggableMixin from '@/mixins/draggable'
// import mediaLibraryMixin from '@/mixins/mediaLibrary/mediaLibrary'
// import inputframeMixin from '@/mixins/inputFrame'

import FileItem from '@/components/files/FileItem.vue'

export default {
  name: 'ue-custom-input-file',
  components: {
    FileItem,
    draggable
  },
  mixins: [
    InputMixin,
    localeMixin
    // mediaLibraryMixin,
    // draggableMixin,
    // inputframeMixin
  ],
  props: {
    ...makeFileProps()
  },
  setup (props, context) {
    return {
      ...useFile(props, context)
    }
  },
  created () {
    // __log(this.$root)
  },
  methods: {
    openMediaLibrary: function (max = 1, name = this.name, index = -1) {
      // if (__isset(this.$store.state.mediaLibrary.selected[name])) {
      //   this.$store.state.mediaLibrary.selected[name] = []
      //   this.$store.state.mediaLibrary.selected[name] = this.input
      // }
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, name)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_TYPE, this.mediaType)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, index)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, max)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, true)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, this.filesizeMax || 0)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, this.widthMin || 0)
      this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, this.heightMin || 0)
      if (this.$main() && this.$main().$refs.mediaLibrary) {
        if (__isset(this.$store.state.mediaLibrary.selected[name])) {
          this.$store.state.mediaLibrary.selected[name] = []
        }
        this.$store.state.mediaLibrary.selected[name] = this.input

        // this.mediableActive = true
        this.$main().$refs.mediaLibrary.openModal()
        this.$nextTick(() => { this.mediableActive = true })
      }
    }
  }
}
</script>

<style lang="scss" scoped>

  .fileField {
    // width: 100%;
    display: block;
    border-radius: 2px;
    border: 1px solid $color__border;
    overflow-x: hidden;
  }

  .fileField__trigger {
    padding: 10px;
    position: relative;
    border-top: 1px solid $color__border--light;

    &:first-child {
      border-top:0 none
    }
  }

  .fileField__note {
    color: $color__text--light;
    float: right;
    position: absolute;
    bottom: 18px;
    right: 15px;
    display: none;

    @include breakpoint('small+') {
      display: inline-block;
    }

    @include breakpoint('medium') {
      display: none;
    }
  }

  .fileField__list {
    overflow: hidden;
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
  }
</style>

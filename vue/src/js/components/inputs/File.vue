<template>
  <v-input
    hideDetails="auto"
    appendIcon="mdi-close"
    :variant="boundProps.variant"
  >
    <template v-slot:default="defaultSlot">
      <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-theme--jakomeet v-locale--is-ltr">
        <div class="v-field__field" data-no-activator="">
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
                    :item="input[`${itemSlot.index}`]"
                    @delete='deleteItem(itemSlot.index)'
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
        </div>
        <div class="v-field__outline">
          <div class="v-field__outline__start"></div>
          <div class="v-field__outline__notch">
            <label class="v-label v-field-label v-field-label--floating" aria-hidden="true" for="input-29">
              {{ boundProps.label }}
            </label>
          </div>
          <div class="v-field__outline__end"></div>
        </div>
      </div>
    </template>
  </v-input>
</template>
<script>
import { MEDIA_LIBRARY } from '@/store/mutations'
import draggable from 'vuedraggable'
import { makeFileProps, useFile } from '@/hooks'
import { makeInputEmits } from '@/hooks'

import localeMixin from '@/mixins/locale'
// import draggableMixin from '@/mixins/draggable'
// import mediaLibraryMixin from '@/mixins/mediaLibrary/mediaLibrary'
// import inputframeMixin from '@/mixins/inputFrame'

import FileItem from '@/components/files/FileItem.vue'

export default {
  name: 'v-custom-input-file',
  emits: [...makeInputEmits],

  components: {
    FileItem,
    draggable
  },
  mixins: [
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
    width: 100%;
    display: block;
    border-radius: 2px;
    // border: 1px solid $color__border;
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

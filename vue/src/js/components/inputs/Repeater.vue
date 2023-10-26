<template>
  <div>
    <ue-title v-if="label" :classes="['pl-0 pt-0']">
      {{ label }}
    </ue-title>
    <div class="repeater__block">
      <!-- <template v-for="(model, index) in repeaterInputs" :key="`ue-repeater-form-${index}`">
        <v-custom-form-base
          :id="`ue-repeater-form-${index}`"
          v-model="repeaterInputs[index]"
          :schema="repeaterSchemas[index]"
          :row="rowAttribute"
        >
        </v-custom-form-base>
      </template> -->

      <draggable
        class="content__content"
        v-model="repeaterInputs"
        item-key="id"
        v-bind="dragOptions"

        >
        <template #item="itemSlot">
          <div>
            <v-hover>
              <template v-slot:default="{ isHovering, props }">
                <div :class="['content__item', isHovering ? 'active' :'']" v-bind="props">
                  <div class="content__item--body">
                    <div class="content__item--toolbar">
                      <v-btn @click="addRepeaterBlock()" color="success" variant="text" density="compact" class="" icon="">
                        <v-icon size="x-small" icon="mdi-plus" />
                      </v-btn>
                      <v-btn @click="duplicateRepeaterBlock(itemSlot.index)" variant="text" density="compact" class="" icon="">
                        <v-icon size="x-small" icon="mdi-content-copy" />
                      </v-btn>
                      <v-btn @click="deleteRepeaterBlock(itemSlot.index)" color="red" variant="text" density="compact" class="" icon="">
                        <v-icon size="x-small" icon="$delete" />
                      </v-btn>
                    </div>
                    <div class="content__item--navigation d-none">
                      <!-- <button
                        v-if="!isFirst"
                        v-on:click.stop="$emit('up')"
                        type="button"
                        class="nav-button up">
                        <svg
                          width="18"
                          height="18"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 18 18"
                          role="img"
                          aria-hidden="true"
                          focusable="false">
                          <polygon points="9,4.5 3.3,10.1 4.8,11.5 9,7.3 13.2,11.5 14.7,10.1 "></polygon>
                        </svg>
                      </button> -->
                      <!-- <button type="button" class="nav-anchor">
                        <svg
                          width="18"
                          height="18"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 18 18"
                          role="img"
                          aria-hidden="true"
                          focusable="false">
                          <path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path>
                        </svg>
                      </button> -->
                      <!-- <button
                        v-if="!isLast"
                        v-on:click.stop="$emit('down')"
                        type="button"
                        class="nav-button down">
                        <svg
                          width="18"
                          height="18"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 18 18"
                          role="img"
                          aria-hidden="true"
                          focusable="false">
                          <polygon points="9,13.5 14.7,7.9 13.2,6.5 9,10.7 4.8,6.5 3.3,7.9 "></polygon>
                        </svg>
                      </button> -->
                    </div>
                    <v-custom-form-base
                      :id="`ue-repeater-form-${itemSlot.index}`"
                      v-model="repeaterInputs[itemSlot.index]"
                      :schema="repeaterSchemas[itemSlot.index]"
                      :row="rowAttribute"
                    >
                      <!-- <template v-slot:[`slot-top-ue-repeater-form-${itemSlot.index}`]>
                        <h4 class="slot">
                          Top Slot of 'Form'
                        </h4>
                      </template> -->
                      <!-- <template
                        v-for="(input, key) in repeaterSchemas[itemSlot.index]"
                        :key="key"
                        v-slot:[`slot-inject-item-key-ue-repeater-form-${itemSlot.index}-${key}`]="selectItemSlot"
                        >
                        <v-list-item v-bind="selectItemSlot.props" :subtitle="selectItemSlot.title"></v-list-item>
                      </template> -->
                      <template
                        v-for="(_slot, key) in selectFieldSlots[itemSlot.index]"
                        :key="key"
                        v-slot:[`slot-inject-${_slot.name}-key-ue-repeater-form-${itemSlot.index}-${_slot.inputName}`]="_slotData"
                        >
                        <ue-recursive-stuff
                          v-for="(context, i) in _slot.context.elements"
                          :key="i"
                          :configuration="context"
                          :bindData="_slotData">
                        </ue-recursive-stuff>
                        <!-- <v-list-item v-bind="_slotData.props" :subtitle="_slotData.title"></v-list-item> -->
                        <!-- <v-list-item v-bind="_slotData.props" subtitle="text"></v-list-item> -->
                      </template>

                    </v-custom-form-base>
                  </div>
                </div>
              </template>
            </v-hover>
          </div>
        </template>
      </draggable>
    </div>
    <div class="repeater__bottom">
      <div class="text-right py-theme">
          <v-btn-secondary
            class=""
            @click="addRepeaterBlock"
            >
            Ekle
            <!-- <v-icon
              size="small"
              icon="$arrowLeft"
            /> -->
          </v-btn-secondary>

          <!-- <v-pagination
            v-model="options.page"
            :length="pageCount"
          ></v-pagination> -->
        </div>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable'

import { InputMixin } from '@/mixins' // for props
import {
  useDraggable,
  makeDraggableProps,
  useRepeater,
  makeRepeaterProps
} from '@/hooks'

export default {
  components: {
    draggable
  },
  setup (props, context) {
    return {
      ...useDraggable(props, context),
      ...useRepeater(props, context)
    }
  },
  mixins: [InputMixin],
  name: 'v-custom-input-repeater',
  props: {
    ...makeDraggableProps(),
    ...makeRepeaterProps()
  },

  data () {
    return {

    }
  },

  computed: {

  },

  methods: {

  },

  watch: {

  },

  created () {
    // __log(
    //   // this.schema
    //   this.repeaterSchemas
    // )
  }
}
</script>

<style lang="sass" scoped>

  $border-color: $primary-color
  $border-width: 2px
  $toolbar-height: 36px

  .repeater__block
    margin-left: -1 * $theme-space
    .content__content
      margin-bottom: 20px
      + .dropdown
        display: inline-block

      .content__item
        z-index: 1
        padding-left: $theme-space
        cursor: all-scroll
        // border: 1px solid $color__border
        // border-top: 0 none
        position: relative
        box-sizing: border-box
        &.sortable-ghost
          opacity: 0.5

        &.active
          z-index: 3
          .content__item--body
            border: $border-width solid $border-color
            // border-color: red
          .content__item--navigation
            display: block
          .content__item--toolbar
            display: flex
            border-top: $border-width solid $border-color
            border-left: $border-width solid $border-color
            border-right: $border-width solid $border-color

        .content__item--body
          position: relative
          border-radius: 4px
          transition: border 200ms ease

        .content__item--toolbar
          background: $tertiary-color or #fff
          border-top-left-radius: 4px
          border-top-right-radius: 4px
          z-index: 2
          position: absolute
          top: -1 * $toolbar-height
          left: -1 * $border-width
          transition: border 200ms ease
          height: $toolbar-height
          box-sizing: border-box
          display: none
          align-items: center
          padding: 0
          overflow: hidden

</style>

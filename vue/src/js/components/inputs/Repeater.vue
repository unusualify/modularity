<template>
  <v-input
    hideDetails="auto"
  >
    <div class="w-100">
      <div class="d-flex">
        <ue-title v-if="label" :classes="['pl-0 pt-0']">
          {{ label }}
        </ue-title>
        <slot name="append"></slot>
      </div>
      <div :class="['repeater__block', {gutter: withGutter}]">
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
                      <v-custom-form-base
                        :id="`ue-repeater-form-${itemSlot.index}`"
                        :modelValue="itemSlot.element"
                        @update:modelValue="onUpdateRepeaterInput($event, itemSlot.index)"
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
      <div class="repeater__bottom mb-theme">
        <div class="d-flex">
          <slot name="addButton" v-bind="{text: addButtonText, addRepeaterBlock}">
            <v-btn-secondary
              class=""
              @click="addRepeaterBlock"
              appendIcon="$add"
              >
              {{ addButtonText }}
            </v-btn-secondary>
          </slot>
          <div class="ml-auto">
            <slot name="addButtonRight" v-bind="{}"></slot>
          </div>
        </div>
      </div>
    </div>
  </v-input>
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
  computed: {},
  methods: {},
  watch: {},
  created () {
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
    &.gutter
      .content__content
        .content__item
          .content__item--body
            padding: 1.3vw !important
</style>

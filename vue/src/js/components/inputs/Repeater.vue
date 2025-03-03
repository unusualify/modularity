<template>
  <v-input
    class="v-input-repeater"
  >
    <div class="w-100">
      <ue-collapsible
        v-model="isOpen"
        :no-collapse="!collapsible"
        no-header-background
        :horizontal-padding="0"
        :vertical-padding="0"
      >
        <!-- Title -->
        <template v-slot:title v-if="label || subtitle || $slots.title || $slots.append">
          <div class="d-flex" v-if="(label || subtitle) && (schema.length || Object.keys(schema).length) || $slots.append">
            <div class="d-flex flex-column">
              <ue-title v-if="label && (schema.length || Object.keys(schema).length) " :classes="['pl-0 pt-0']" color="grey-darken-5" transform="none" weight="medium">
                {{ label }}
              </ue-title>
              <ue-title v-if="subtitle && (schema.length || Object.keys(schema).length) " :classes="['pl-0 pt-0']" color="grey-darken-5" transform="none" weight="regular">
                {{ subtitle }}
              </ue-title>
            </div>
            <slot name="append"></slot>
          </div>
        </template>
        <template v-slot:default>
          <div class="mt-4">
            <!-- Headers -->
            <v-row v-if="hasHeaders" class="mb-4" no-gutters>
              <v-col v-for="header in headers" :key="header.title" v-bind="header.col">
                {{ header.title }}
              </v-col>
            </v-row>
            <!-- Draggable -->
            <div v-if="draggable" :class="['v-input-repeater__block v-input-repeater__block--draggable']">
              <Draggable
                class="v-input-repeater__content"
                v-model="repeaterInputs"
                item-key="id"
                v-bind="dragOptions"
                >
                <template #item="itemSlot">
                  <div>
                    <v-hover>
                      <template v-slot:default="{ isHovering, props }">
                        <div :class="['v-input-repeater__item', isHovering ? 'active' :'', draggable ? 'draggable': '']" v-bind="props">
                          <div :class="['v-input-repeater__item--body', {gutter: withGutter}]">
                            <div v-if="!noToolbar" class="v-input-repeater__item--toolbar">
                              <v-btn v-if="isAddible" @click="addRepeaterBlock()" color="success" variant="text" density="compact" class="" icon="">
                                <v-icon size="x-small" icon="mdi-plus" />
                              </v-btn>
                              <v-btn v-if="!isUnique" @click="duplicateRepeaterBlock(itemSlot.index)" variant="text" density="compact" class="" icon="">
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
                              @update:schema="console.log(repeaterSchemas[itemSlot.index])"
                              :row="formRowAttribute"
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
              </Draggable>
            </div>

            <!-- Static -->
            <v-row v-else class="v-input-repeater__block" v-bind="rowAttribute">
              <v-col v-for="(item, index) in repeaterInputs" :key="item.id" v-bind="$lodash.pick(formCol, ['cols', 'xs', 'sm', 'md', 'lg', 'xl'])">
                <v-hover>
                  <template v-slot:default="{ isHovering, props }">
                    <div :class="['v-input-repeater__item', isHovering ? 'active' :'', draggable ? 'draggable': '']" v-bind="props">
                      <div :class="['v-input-repeater__item--body', {gutter: withGutter}]">
                        <div v-if="!noToolbar" class="v-input-repeater__item--toolbar">
                          <v-btn v-if="isAddible" @click="addRepeaterBlock()" color="success" variant="text" density="compact" class="" icon="">
                            <v-icon size="x-small" icon="mdi-plus" />
                          </v-btn>
                          <v-btn v-if="!isUnique" @click="duplicateRepeaterBlock(index)" variant="text" density="compact" class="" icon="">
                            <v-icon size="x-small" icon="mdi-content-copy" />
                          </v-btn>
                          <v-btn @click="deleteRepeaterBlock(index)" color="red" variant="text" density="compact" class="" icon="">
                            <v-icon size="x-small" icon="$delete" />
                          </v-btn>
                        </div>
                        <v-custom-form-base
                          :id="`ue-repeater-form-${index}`"
                          class="w-100 h-100"
                          :modelValue="item"
                          @update:modelValue="onUpdateRepeaterInput($event, index)"
                          :schema="repeaterSchemas[index]"
                          @update:schema="console.log(repeaterSchemas[index])"
                          :row="formRowAttribute"
                        >

                          <template
                            v-for="(_slot, key) in selectFieldSlots[index]"
                            :key="key"
                            v-slot:[`slot-inject-${_slot.name}-key-ue-repeater-form-${index}-${_slot.inputName}`]="_slotData"
                            >
                            <ue-recursive-stuff
                              v-for="(context, i) in _slot.context.elements"
                              :key="i"
                              :configuration="context"
                              :bindData="_slotData">
                            </ue-recursive-stuff>
                          </template>

                        </v-custom-form-base>
                      </div>
                    </div>
                  </template>
                </v-hover>
              </v-col>
            </v-row>

            <!-- Bottom Actions -->
            <div
              v-if="!noAddButton || $slots.addButton || $slots.addButtonRight"
              class="v-input-repeater__bottom mb-12"
            >
              <div class="d-flex">
                <slot name="addButton" v-bind="{text: addButtonContent, addRepeaterBlock, isActive: addButtonIsActive}">
                  <v-btn
                    v-if="!noAddButton && (schema.length || Object.keys(schema).length)"
                    variant="outlined"
                    class=""
                    :disabled="!addButtonIsActive"
                    @click="addRepeaterBlock"
                    appendIcon="$add"
                    >
                    {{ addButtonContent }}
                  </v-btn>
                </slot>
                <div class="ml-auto">
                  <slot name="addButtonRight" v-bind="{}"></slot>
                </div>
              </div>
            </div>
          </div>
        </template>
      </ue-collapsible>


    </div>
  </v-input>
</template>

<script>
  import Draggable from 'vuedraggable'

  import {
    useDraggable,
    makeDraggableProps,
    useRepeater,
    makeRepeaterProps,
    makeInputEmits
  } from '@/hooks'

  export default {
    name: 'v-input-repeater',
    emits: [...makeInputEmits],
    components: {
      Draggable
    },
    props: {
      ...makeDraggableProps(),
      ...makeRepeaterProps(),
      noToolbar: {
        type: Boolean,
        default: false
      },
      collapsible: {
        type: Boolean,
        default: false
      }
    },
    setup (props, context) {
      return {
        ...useDraggable(props, context),
        ...useRepeater(props, context)
      }
    },

    data () {
      return {
        isOpen: true
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
  .v-input-repeater
    $border-color: $primary-color
    $border-width: 2px
    $toolbar-height: 36px

    .v-input-repeater__block
      // margin-left: -1 * 12 * $spacer
      .v-input-repeater__content
        margin-bottom: 20px
      + .dropdown
        display: inline-block

      .v-input-repeater__item
        height: 100%
        z-index: 1
        // padding-left: 12 * $spacer
        // border: 1px solid $color__border
        // border-top: 0 none
        position: relative
        box-sizing: border-box
        &.draggable
          cursor: all-scroll
        &.sortable-ghost
          opacity: 0.5

        &.active
          z-index: 3
          >.v-input-repeater__item--body
            // border: $border-width solid $border-color
            >.v-input-repeater__item--toolbar
              display: flex
              border-top: $border-width solid $border-color
              border-left: $border-width solid $border-color
              border-right: $border-width solid $border-color
            // border-color: red
          .v-input-repeater__item--navigation
            display: block

        .v-input-repeater__item--body
          height: 100%
          position: relative
          border-radius: 4px
          transition: border 200ms ease

        .v-input-repeater__item--toolbar
          background: $tertiary-color or #fff
          border-top-left-radius: 4px
          border-top-right-radius: 4px
          z-index: 2
          position: absolute
          top: -1 * $toolbar-height
          // left: -1 * $border-width
          transition: border 200ms ease
          height: $toolbar-height
          box-sizing: border-box
          display: none
          align-items: center
          padding: 0
          overflow: hidden

      &--draggable
        margin-left: -1 * 12 * $spacer
        .v-input-repeater__item
          padding-left: 12 * $spacer


    .v-input-repeater__item--body
      &.gutter
        padding: 1.3vw !important
</style>

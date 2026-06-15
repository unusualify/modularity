<!--
  FormBase - Refactored form base component.
  Split from CustomFormBase: logic in useFormBaseLogic, field rendering in FormBaseField.
  Same API as CustomFormBase (modelValue, schema, id, row, col, etc.).
-->
<template>
  <v-row
    :id="id"
    v-bind="getRow"
    v-resize.quiet="onResize"
  >
    <slot :name="getFormTopSlot()" :id="id" />

    <template v-for="(obj, index) in flatCombinedArraySorted" :key="index">
      <v-tooltip
        :disabled="!obj.schema.tooltip"
        v-bind="getShorthandTooltip(obj.schema.tooltip)"
      >
        <template v-slot:activator="{ props: tooltipProps }">
          <v-col
            v-show="!obj.schema.hidden"
            :key="index"
            v-bind="{
              ...getGridAttributes(obj),
              ...tooltipProps
            }"
            v-intersect="(isIntersecting, entries, observer) => onIntersect(isIntersecting, entries, observer, obj)"
            v-touch="{
              left: () => onSwipe('left', obj),
              right: () => onSwipe('right', obj),
              up: () => onSwipe('up', obj),
              down: () => onSwipe('down', obj)
            }"
            v-click-outside="(event) => onClickOutside(event, obj)"
            :class="getClassName(obj)"
            :draggable="obj.schema.drag"
            @mouseenter="onEvent($event, obj)"
            @mouseleave="onEvent($event, obj)"
            @dragstart="dragstart($event, obj)"
            @dragover="dragover($event, obj)"
            @drop="drop($event, obj)"
          >
            <slot :name="getTypeTopSlot(obj)" v-bind="{ obj, index, id: id }" />
            <slot :name="getKeyTopSlot(obj)" v-bind="{ obj, index, id: id }" />
            <slot :name="getTypeItemSlot(obj)" v-bind="{ obj, index, id: id }">
              <slot :name="getKeyItemSlot(obj)" v-bind="{ obj, index, id: id }">
                <FormBaseField
                  :obj="obj"
                  :ctx="ctx"
                  :index="index"
                  :form-item="formItem"
                >
                  <template v-for="(_, name) in $slots" #[name]="slotData">
                    <slot :name="name" v-bind="{ obj, index, id: id, ...slotData }" />
                  </template>
                </FormBaseField>
              </slot>
            </slot>
            <slot :name="getTypeBottomSlot(obj)" v-bind="{ obj, index, id: id }" />
            <slot :name="getKeyBottomSlot(obj)" v-bind="{ obj, index, id: id }" />
          </v-col>
        </template>
        <slot :name="getTooltipSlot()" v-bind="{ obj, index, id: id }">
          <span>{{ getShorthandTooltipLabel(obj.schema.tooltip) }}</span>
        </slot>
        <slot :name="getKeyTooltipSlot(obj)" v-bind="{ obj, index, id: id }" />
      </v-tooltip>
      <v-spacer v-if="obj.schema.spacer" :key="`s-${index}`" />
    </template>
    <slot :name="getFormBottomSlot()" :id="id" />
  </v-row>
</template>

<script>
import { useSlots, onBeforeMount, watch, provide } from 'vue'
import useFormBaseLogic from '@/hooks/form/useFormBaseLogic'
import FormBaseField from './FormBaseField.vue'

const defaultID = 'form-base'

export default {
  name: 'VFormBase',
  components: { FormBaseField },
  emits: ['update:modelValue', 'update:schema', 'input', 'update', 'resize', 'blur', 'click'],
  props: {
    id: { type: String, default: defaultID },
    rootId: { type: String, default: defaultID },
    row: { type: [Object] },
    rowGroup: { type: [Object] },
    col: { type: [Object, Number, String] },
    colGroup: { type: [Object, Number, String] },
    flex: { type: [Object, Number, String] },
    modelValue: { type: [Object, Array], default: () => null },
    model: { type: [Object, Array] },
    schema: { type: [Object, Array], default: () => ({}) },
    formItem: { type: Object, default: () => ({}) },
    noAutoGenerateSchema: { type: Boolean, default: false }
  },
  setup (props, { emit }) {
    const slots = useSlots()
    const ctx = useFormBaseLogic(props, { emit }, slots)

    provide('ueFormPayload', ctx.valueIntern)

    onBeforeMount(() => {
      ctx.rebuildArrays(ctx.valueIntern.value ?? {}, ctx.formSchema.value ?? {})
    })

    watch(
      () => props.modelValue,
      (value, oldValue) => {
        if (typeof window.__dot === 'function' && value && oldValue) {
          const newKeys = JSON.stringify(Object.keys(window.__dot(value)))
          const oldKeys = JSON.stringify(Object.keys(window.__dot(oldValue)))
          if (newKeys !== oldKeys) {
            ctx.rebuildArrays(ctx.valueIntern.value, ctx.formSchema.value)
          }
        }
      },
      { deep: true }
    )

    return {
        ...ctx,
        ctx
    }
  }
}
</script>

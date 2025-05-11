// hooks/useTable.js
import { reactive, toRefs, computed, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useProxiedModel } from 'vuetify/lib/composables/proxiedModel.mjs'

import { makeModelValueProps } from '__hooks/useModelValue'

export const makeActiveTableItemProps = propsFactory({
  ...makeModelValueProps(),
  tableHeaders: {
    type: Array,
    default: []
  },
  itemData: {
    type: Object,
    default () {
      return {}
    }
  }
})

// by convention, composable function names start with "use"
export default function useActiveTableItem (props, context) {
  const model = useProxiedModel(props, 'modelValue')
  const item = computed({
    get: () => model.value,
    set: v => {
      model.value = v
    }
  })

  const state = reactive({
    modalOpened: false,
    modalActive: false,
    modalStatus: computed(() => {
      return state.modalOpened && !!item.value
    }),

    activeKey: null,
    activeBlock: computed(() => state.activeKey ? props.itemData[state.activeKey] : null),

    items: computed(() => item.value ? [item.value] : [])
  })

  const methods = reactive({
    selectNested: function (key) {
      state.activeKey = key
      // state.modalActive = false
      context.emit('toggle', true)
    },
    clickOutside: function (event) {
      item.value = null
      state.activeKey = null
    },
    closeItemDetails: function (key = '') {
      state.modalActive = true
      state.activeKey = null

      // item.value = null
      context.emit('toggle', false)
    }
  })

  watch(() => item.value, (newValue, oldValue) => {
    state.modalActive = (!!newValue && newValue !== oldValue)
  })
  watch(() => state.activeKey, (newValue, oldValue) => {
    if (newValue) {
      state.modalActive = false
    }
  })

  watch(() => props.itemData, (newValue, oldValue) => {

  })

  // expose managed state as return value
  return {
    item,
    ...toRefs(state),
    ...toRefs(methods)
  }
}

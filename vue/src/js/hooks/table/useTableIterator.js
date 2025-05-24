// hooks/table/useTableIterator.js
import { watch, computed, nextTick, reactive, toRefs, ref, watchEffect } from 'vue'
import { propsFactory } from "vuetify/lib/util/propsFactory.mjs";
import { useFormatter, useRoot } from '@/hooks'

import { useTableItemActions } from '@/hooks/table'

export const makeTableIteratorProps = propsFactory({
  name: {
    type: String,
    default: ''
  },
  titlePrefix: {
    type: String,
    default: ''
  },
  titleKey: {
    type: String,
    default: 'name'
  },


  item: {
    type: Object,
    default: {}
  },
  headers:{
    type: Object,
    default: {}
  },
  iteratorOptions: {
    type: Object,
    default: {}
  },
  rowActions:{
    type: Array,
    default: [],
  }
})

export const tableIterableEmits = [
  'click-action',
  'edit-item'
]

export default function useTableIterator(props, context){
  const { itemHasAction } = useTableItemActions(props, context)

  const state = reactive({
    id: Math.ceil(Math.random() * 1000000 ) + ' -iterator',
    headersWithKeys: computed(() => {

      let collection = {};
      Object.values(props.headers).forEach((header, index) => {
        collection[header['key']] = header
      })
      return collection
    })
  })

  const formatter = useFormatter(props, context, props.headers)

  const methods = reactive({
    canItemAction: function (action) {
      if (__isset(action.can) && action.can) {

      }

      return true
    },
    isSoftDeletable (item) {
      return !!(__isset(item.deleted_at) && item.deleted_at)
    },
    itemAction: (item, action) => {
      return context.emit('click-action', item, action)
    },
    editItem: (item) => {
      context.emit('edit-item', item)
    }
  })

  return {
    ...toRefs(methods),
    ...toRefs(state),
    ...formatter,
    itemHasAction
  }

}

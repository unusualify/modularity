import { watch, computed, nextTick, reactive, toRefs, ref, watchEffect } from 'vue'
import { propsFactory } from "vuetify/lib/util/propsFactory.mjs";
import { useFormatter, useRoot } from '@/hooks'



export const makeIteratorProps = propsFactory({
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

export const iterableEmits = [
  'click-action'
]

export default function useIterators(props, context){



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

  const methods = reactive({
    canItemAction: function (action) {
      if (__isset(action.can) && action.can) {

      }

      return true
    },
    isSoftDeletable (item) {
      return !!(__isset(item.deleted_at) && item.deleted_at)
    },
    itemHasAction: function (item, action) {
      let hasAction = true
      switch (action.name) {
        case 'edit':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          } else {
            hasAction = hasAction = methods.canItemAction(action)
          }
          break
        case 'delete':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          } else {
            hasAction = methods.canItemAction(action)
          }
          break
        case 'forceDelete':
          if (methods.isSoftDeletable(item)) {
            hasAction = methods.canItemAction(action)
          } else {
            hasAction = false
          }
          break
        case 'restore':
          if (methods.isSoftDeletable(item)) {
            hasAction = methods.canItemAction(action)
          } else {
            hasAction = false
          }
          break
        case 'duplicate':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        case 'switch':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        case 'activate':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        default:
          break
      }

      return hasAction
    },
    itemAction: (item, action) => {
      return context.emit('click-action',item,action)
    }

  })



  return {
    ...toRefs(methods),
    ...toRefs(state),
  }

}

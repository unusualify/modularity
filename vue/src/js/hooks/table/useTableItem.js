// hooks/table/useTableItem.js
import { computed, ref, nextTick, watch } from 'vue'
import { useStore } from 'vuex'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { getSubmitFormData, getModel } from '@/utils/getFormData.js'


export const makeTableItemProps = propsFactory({

})

export default function useTableItem(props, context) {
  const store = useStore()

  // const editedItem = computed(() =>
  //   store.state.form.editedItem ?? {}
  // )

  const editedItem = ref(props.modelValue ? props.modelValue : getModel(props.formSchema))

  // computed
  const isSoftDeletableItem = computed(() =>
    isSoftDeletable(editedItem.value)
  )

  const itemIsDeleted = computed(() =>
    isDeleted(editedItem.value)
  )

  // Methods
  const setEditedItem = (item) => {
    // store.commit(FORM.SET_EDITED_ITEM, item)
    editedItem.value = Object.assign({}, item)
  }

  const resetEditedItem = () => {
    nextTick(() => {
      editedItem.value = getModel(props.formSchema)
      // store.commit(FORM.RESET_EDITED_ITEM)
    })
  }

  const isSoftDeletable = (item) => {
    return !!(__isset(item.deleted_at) && item.deleted_at)
  }

  const isDeleted = (item) => {
    return !!(__isset(item.deleted_at) && item.deleted_at)
  }


  return {
    // refs
    editedItem,
    isSoftDeletableItem,
    itemIsDeleted,

    // methods
    setEditedItem,
    resetEditedItem,
    isSoftDeletable,
    isDeleted
  }
}

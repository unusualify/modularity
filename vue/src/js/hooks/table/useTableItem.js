// hooks/useTableItem.js
import { computed, ref, nextTick, watch } from 'vue'
import { useStore } from 'vuex'
import { FORM } from '@/store/mutations'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableItemProps = propsFactory({

})

export default function useTableItem(props, context) {
  const store = useStore()

  const editedItem = computed(() =>
    store.state.form.editedItem ?? {}
  )

  // computed
  const isSoftDeletableItem = computed(() =>
    isSoftDeletable(editedItem.value)
  )

  // Methods
  const setEditedItem = (item) => {
    store.commit(FORM.SET_EDITED_ITEM, item)
  }

  const resetEditedItem = () => {
    nextTick(() => {
      store.commit(FORM.RESET_EDITED_ITEM)
    })
  }

  const isSoftDeletable = (item) => {
    return !!(__isset(item.deleted_at) && item.deleted_at)
  }


  return {
    // refs
    editedItem,
    isSoftDeletableItem,

    // methods
    setEditedItem,
    resetEditedItem,
    isSoftDeletable
  }
}

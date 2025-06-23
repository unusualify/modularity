// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useStore } from 'vuex'
import { mapGetters } from '@/utils/mapStore'

import { MEDIA_LIBRARY } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { useRoot, makeInputProps, makeDraggableProps, useInput } from '@/hooks/'
import { useI18n } from 'vue-i18n'

export const makeFileProps = propsFactory({
  ...makeInputProps(),
  ...makeDraggableProps(),
  mediaType: {
    type: String,
    default: 'file'
  },
  name: {
    type: String,
    required: true
  },
  itemLabel: {
    type: String,
    default () {
      return useI18n().t('File')
    }
  },
  endpoint: {
    type: String,
    default: ''
  },
  // draggable: {
  //   type: Boolean,
  //   default: true
  // },
  max: {
    type: Number,
    default: 1
  },
  note: {
    type: String,
    default: ''
  },
  fieldNote: {
    type: String,
    default: ''
  },
  filesizeMax: {
    type: Number,
    default: 0
  },
  buttonOnTop: {
    type: Boolean,
    default: false
  }
})

// by convention, composable function names start with "use"
export default function useFile (props, context) {

  const store = useStore()
  const inputHook = useInput(props, context)
  const { modelValue, obj } = toRefs(props)
  const getters = mapGetters()
  const { t } = useI18n({ useScope: 'global' })

  const states = reactive({
    mediableActive: false,

    handle: '.item__handle',
    addLabel: computed(() => t('ADD') + ' ' + props.itemLabel),
    items: computed({
      get: () => {
        if (store.state.mediaLibrary.selected.hasOwnProperty(props.name)) {
          return store.state.mediaLibrary.selected[props.name] || []
        } else {
          return []
        }
      },
      set: (value, old) => {
        // store.commit(MEDIA_LIBRARY.REORDER_MEDIAS, {
        //   name: props.name,
        //   medias: value
        // })
      }
    }),
    input_: computed({
      get: () => {
        return modelValue.value ?? []
      },
      set: (value, old) => {
        inputHook.updateModelValue.value(value)
        // store.commit(MEDIA_LIBRARY.REORDER_MEDIAS, {
        //   name: props.name,
        //   medias: value
        // })
      }
    }),
    input: modelValue.value ?? [],
    isDraggable: computed(() => props.draggable && states.input.length > 1),
    remainingItems: computed(() => {
      return props.max - states.input.length
    }),
    itemsIds: computed(() => {
      // const arrayOfIds = []

      // for (const name in state.selected) {
      //   arrayOfIds[name] = state.selected[name].map((item) => `${item.endpointType}_${item.id}`)
      // }

      // return arrayOfIds
      if (getters.selectedItemsByIds.value[props.name]) {
        return getters.selectedItemsByIds.value[props.name].join()
      } else {
        return ''
      }
    })
  })

  const methods = reactive({
    deleteAll: function (index) {
      states.input = []
      // store.commit(MEDIA_LIBRARY.DESTROY_MEDIAS, {
      //   name: props.name
      // })
    },
    deleteItem: function (index) {
      states.input.splice(index, 1)
      // if (states.input.length === 0) delete state.selected[media.name]

      // store.commit(MEDIA_LIBRARY.DESTROY_SPECIFIC_MEDIA, {
      //   name: props.name,
      //   index
      // })
    }
  })

  watch(() => store.state.mediaLibrary.selected[props.name], (newValue, oldValue) => {
    if (store.state.mediaLibrary.isInserted && states.mediableActive) {
      states.mediableActive = false
      store.commit(MEDIA_LIBRARY.UPDATE_IS_INSERTED, false)
      states.input = newValue
    }
  }, { deep: true })
  watch(() => states.input, (value, oldValue) => {
    inputHook.updateModelValue.value(value)
  }, { deep: true })
  watch(() => modelValue.value, (value, oldValue) => {
    states.input = value
  }, { deep: true })
  // expose managed state as return value
  return {
    ...inputHook,
    ...toRefs(methods),
    ...toRefs(states)
    // ...useInput(props, context)
    // ...inputHook
  }
}

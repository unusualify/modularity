// hooks/useImage.js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useStore } from 'vuex'
import cloneDeep from 'lodash-es/cloneDeep'
import isEqual from 'lodash-es/isEqual'
import { isset } from '@/utils/helpers'

export function normalizeImageModelValue (value) {
  if (value == null || value === '') {
    return []
  }

  if (Array.isArray(value)) {
    return value.filter(item => item != null && typeof item === 'object')
  }

  if (typeof value === 'object' && isset(value.id) && isset(value.thumbnail)) {
    return [value]
  }

  return []
}
import { mapGetters } from '@/utils/mapStore'

import { MEDIA_LIBRARY } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { useRoot, makeInputProps, makeDraggableProps, useInput } from '@/hooks/'
import { useI18n } from 'vue-i18n'

export const makeImageProps = propsFactory({
  ...makeInputProps(),
  ...makeDraggableProps(),
  mediaType: {
    type: String,
    default: 'image'
  },
  itemLabel: {
    type: String,
    default () {
      return useI18n().t('Image')
    }
  },
  name: {
    type: String,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  btnLabel: {
    type: String,
    default () {
      return useI18n().t('fields.medias.btn-label', 'Attach image')
    }
  },
  hover: {
    type: Boolean,
    default: false
  },
  isSlide: {
    type: Boolean,
    default: false
  },
  // Index of media in selected context
  index: {
    type: Number,
    default: 0
  },
  // current media context put in store. eg: slideshow, cover...
  mediaContext: {
    type: String,
    default: ''
  },
  activeCrop: {
    type: Boolean,
    default: true
  },
  widthMin: {
    type: Number,
    default: 0
  },
  heightMin: {
    type: Number,
    default: 0
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
export default function useImage (props, context) {
  const store = useStore()
  const inputHook = useInput(props, context)
  const { modelValue, obj } = toRefs(props)
  const getters = mapGetters()
  const { t } = useI18n({ useScope: 'global' })

  /** Matches UPDATE_MEDIA_CONNECTOR / openMediaLibrary(name) — medias are stored under this key */
  const mediaStoreKey = computed(() =>
    (props.mediaContext && String(props.mediaContext).length > 0) ? props.mediaContext : props.name
  )

  const states = reactive({
    mediableActive: true,

    handle: '.item__handle',
    addLabel: computed(() => t('ADD', {item: props.itemLabel})),
    items: computed({
      get: () => {
        const key = mediaStoreKey.value
        if (store.state.mediaLibrary.selected.hasOwnProperty(key)) {
          return store.state.mediaLibrary.selected[key] || []
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
        return normalizeImageModelValue(modelValue.value)
      },
      set: (value, old) => {
        inputHook.updateModelValue.value(value)
        // store.commit(MEDIA_LIBRARY.REORDER_MEDIAS, {
        //   name: props.name,
        //   medias: value
        // })
      }
    }),
    input: normalizeImageModelValue(modelValue.value),
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
  watch(() => store.state.mediaLibrary.selected[mediaStoreKey.value], (newValue, oldValue) => {
    if (isset(store.state.mediaLibrary.selected[mediaStoreKey.value]) && store.state.mediaLibrary.isInserted && states.mediableActive) {
      states.mediableActive = false
      store.commit(MEDIA_LIBRARY.UPDATE_IS_INSERTED, false)
      const next = Array.isArray(newValue) ? cloneDeep(newValue) : (newValue ?? [])
      if (!isEqual(next, states.input)) {
        states.input = next
      }
    }
  }, { deep: true })
  watch(() => states.input, (value) => {
    const mv = modelValue.value ?? []
    const v = value ?? []
    if (isEqual(v, Array.isArray(mv) ? mv : [])) return
    inputHook.updateModelValue.value(value)
  }, { deep: true })
  watch(() => modelValue.value, (value) => {
    const next = normalizeImageModelValue(value)
    if (isEqual(states.input, next)) return
    states.input = cloneDeep(next)
  }, { deep: true })

  watch(() => states.error, (newValue, oldValue) => {

  })
  // expose managed state as return value
  return {
    ...inputHook,
    ...toRefs(methods),
    ...toRefs(states)
    // ...useInput(props, context)
    // ...inputHook
  }
}

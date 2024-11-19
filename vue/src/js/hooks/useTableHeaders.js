// hooks/useTable.js
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableHeadersProps = propsFactory({
  hideHeaders: {
    type: Boolean,
    default: false
  },
  columns: {
    type: Array
  },
  headerOptions: {
    type: [Array, Object],
    default: {}
  },
  cellOptions: {
    type: [Array, Object],
    default: {}
  },
  customRowComponent: {
    type: Object,
    default: {}
  },
})

export default function useTableHeaders(props) {
  const store = useStore()

  // Initialize headers
  const headers = ref(props.columns ?? store.state.datatable.headers ?? [])
  const headersModel = ref(_.cloneDeep(headers.value))

  // Computed properties
  const selectedHeaders = computed(() =>
    headers.value.filter(header => !!header.visible && header.visible === true)
  )

  const hideHeaders = computed(() =>
    props.hideHeaders || enableIterators.value
  )

  const enableIterators = computed(() =>
    Object.keys(props.customRowComponent || {}).length
  )

  // Methods
  const applyHeaders = () => {
    headers.value = _.cloneDeep(headersModel.value)

    __pushQueryParams({
      columns: headersModel.value.reduce((acc, h) => {
        if(h.visible) {
          acc.push(h.key)
        }
        return acc
      }, [])
    })
  }

  return {
    // Refs
    headers,
    headersModel,

    // Computed
    selectedHeaders,
    hideHeaders,
    enableIterators,

    // Methods
    applyHeaders
  }
}

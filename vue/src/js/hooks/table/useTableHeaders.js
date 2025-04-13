// hooks/table/useTableHeaders.js
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableHeadersProps = propsFactory({
  columns: {
    type: Array
  },
  hideHeaders: {
    type: Boolean,
    default: false
  },
  headerOptions: {
    type: [Array, Object],
    default: {}
  },
  cellOptions: {
    type: [Array, Object],
    default: {}
  },
  customRow: {
    type: Object,
    default: {}
  },
})

export default function useTableHeaders(props) {
  const store = useStore()

  const getStorageKey = () => {
    // Get the current route path as the unique identifier
    const path = window.location.pathname

    return `table_unvisible_columns_${path}`
  }

  const getUnvisibleHeaders = () => {
    const unvisibleHeaders = localStorage.getItem(getStorageKey())
    return unvisibleHeaders ? unvisibleHeaders.split(',') : []
  }

  let unvisibleHeaders = getUnvisibleHeaders()
  // Initialize headers
  const rawHeaders = props.columns ?? []
  const headers = ref(rawHeaders.filter(h => !unvisibleHeaders.includes(h.key)))
  const headersModel = ref(_.cloneDeep(rawHeaders.map(h => ({...h, visible: !unvisibleHeaders.includes(h.key) ? true : false}))))

  const hasSearchableHeader = ref(!!_.find(headers.value, header => window.__isset(header.searchable) && header.searchable === true))

  // Computed properties
  const selectedHeaders = computed(() =>
    headers.value.filter(header => !!header.visible && header.visible === true)
  )

  const hasCustomRow = computed(() =>
    Object.keys(props.customRow || {}).length > 0
  )

  const hideHeaders = computed(() =>
    props.hideHeaders || hasCustomRow.value
  )

  const editableColumns = computed(() => {
    return rawHeaders.filter(h => h.hasOwnProperty('isColumnEditable') && h.isColumnEditable === true)
  })

  // Methods
  const applyHeaders = () => {
    headers.value = _.cloneDeep(headersModel.value)

    const unvisibleHeaders = headersModel.value.filter((h, index) => !h.visible)

    localStorage.setItem(getStorageKey(), unvisibleHeaders.map(h => h.key).join(','))
  }

  return {
    // Refs
    headers,
    headersModel,
    hasSearchableHeader,

    // Computed
    selectedHeaders,
    hideHeaders,
    hasCustomRow,
    editableColumns,

    // Methods
    applyHeaders
  }
}

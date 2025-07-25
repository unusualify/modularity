// hooks/table/useTableHeaders.js
import { computed, ref } from 'vue'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableHeadersProps = propsFactory({
  columns: {
    type: Array,
    default: () => []
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
  const getStorageKey = () => {
    // Get the current route path as the unique identifier
    const path = window.location.pathname

    return `table_unvisible_columns_${path}`
  }

  const getUnvisibleHeaders = () => {
    const unvisibleHeaders = localStorage.getItem(getStorageKey())

    return unvisibleHeaders ? unvisibleHeaders.split(',') : []
  }

  const removeHeader = (key) => {
    const index = headersModel.value.findIndex(h => h.key === key)
    headersModel.value[index].visible = false

    const unvisibleHeaders = headersModel.value.filter((h, index) => !h.visible).map(h => h.key)
    localStorage.setItem(getStorageKey(), unvisibleHeaders.join(','))
    headers.value = _.cloneDeep(headersModel.value.filter(h => h.visible))
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

  const formattableHeaders = computed(() => {
    return rawHeaders.filter(h => {
      return (h.hasOwnProperty('columnEditable') && h.columnEditable === true)
      || (h.hasOwnProperty('removable') && h.removable === true)
      || (h.hasOwnProperty('searchable') && h.searchable === true)
    })
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
    formattableHeaders,

    // Methods
    removeHeader,
    applyHeaders
  }
}

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
  const rawHeaders = props.columns ?? store.state.datatable.headers ?? []
  const headers = ref(rawHeaders.filter(h => !unvisibleHeaders.includes(h.key)))
  const headersModel = ref(_.cloneDeep(rawHeaders.map(h => ({...h, visible: !unvisibleHeaders.includes(h.key) ? true : false}))))

  const hasSearchableHeader = ref(!!_.find(headers.value, header => window.__isset(header.searchable) && header.searchable === true))

  // Computed properties
  const selectedHeaders = computed(() =>
    headers.value.filter(header => !!header.visible && header.visible === true)
  )

  const hideHeaders = computed(() =>
    props.hideHeaders || hasCustomRow.value
  )

  const hasCustomRow = computed(() =>
    Object.keys(props.customRow || {}).length
  )

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

    // Methods
    applyHeaders
  }
}

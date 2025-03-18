// hooks/table/useTableFilters.js
import { computed } from 'vue'
import { useStore } from 'vuex'
import { DATATABLE } from '@/store/mutations'
import ACTIONS from '@/store/actions'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableFiltersProps = propsFactory({
  hideSearchField: {
    type: Boolean,
    default: false,
  },
  navActive: {
    type: String,
    default: 'all'
  },
  filterBtnOptions:{
    type:Object,
    default: {},
  },
})

export default function useTableFilters(props) {
  const store = useStore()

  // Filter Status
  const filterActiveStatus = computed(() =>
    store.state.datatable.filter.status ?? 'all'
  )

  const filterActive = computed(() =>
    _.find(store.state.datatable.mainFilters, { slug: filterActiveStatus.value })
  )

  const navActive = computed(() =>
    filterActive.value?.slug ?? 'all'
  )

  // Search
  const search = computed({
    get() {
      return store.state.datatable.search
    },
    set(val) {
      store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, val)
      store.dispatch(ACTIONS.GET_DATATABLE)
    }
  })

  // Advanced Filters
  const advancedFilters = computed(() =>
    store.state.datatable.advancedFilters ?? null
  )

  const mainFilters = computed(() =>
    store.state.datatable.mainFilters ?? null
  )

  // Methods
  const filterStatus = (slug) => {
    if (navActive.value === slug) return

    store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
    store.commit(DATATABLE.UPDATE_DATATABLE_FILTER_STATUS, slug)
    // Reset selected items when changing filter
    store.commit(DATATABLE.REPLACE_DATATABLE_BULK, [])
    store.dispatch(ACTIONS.GET_DATATABLE)
  }

  const submitAdvancedFilter = () => {
    store.commit(DATATABLE.UPDATE_DATATABLE_ADVANCED_FILTER, advancedFilters.value)
    store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
    store.dispatch(ACTIONS.GET_DATATABLE)
  }

  const clearAdvancedFilter = () => {
    store.commit(DATATABLE.RESET_DATATABLE_ADVANCED_FILTER)
    store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
    store.dispatch(ACTIONS.GET_DATATABLE)
  }

  // Filter Button Options
  const filterBtnTitle = computed(() => ({
    text: `${filterActive.value?.name} (${filterActive.value?.number})`
  }))

  return {
    // Status
    filterActiveStatus,
    filterActive,
    navActive,
    mainFilters,

    // Search
    search,

    // Advanced Filters
    advancedFilters,
    filterBtnTitle,

    // Methods
    filterStatus,
    submitAdvancedFilter,
    clearAdvancedFilter
  }
}

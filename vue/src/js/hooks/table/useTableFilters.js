// hooks/table/useTableFilters.js
import { computed, ref } from 'vue'
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
  searchInitialValue: {
    type: String,
    default: '',
  },
  filterList: {
    type: Array,
    default: [],
  },
  filterListAdvanced: {
    type: Object,
    default: {},
  },
})

export default function useTableFilters(props, context) {
  const store = useStore()
  const { isStoreTable } = context

  // Search
  const search = ref(props.searchInitialValue ?? '')
  const searchModel = ref(search.value)

  // Filter Status
  const mainFilters = ref( props.filterList ?? [])
  const activeFilterSlug = ref(props.navActive ?? 'all')
  const activeFilter = computed(() => _.find(mainFilters.value, { slug: activeFilterSlug.value }) )

  // Filter Button Options
  const filterBtnTitle = computed(() => ({
    text: `${activeFilter.value?.name} (${activeFilter.value?.number})`
  }))

  // Methods

  const setSearchValue = (newSearchValue) => {
    let newValue = newSearchValue ?? searchModel.value

    if(search.value !== newValue){
      search.value = newValue

      return true
    }

    return false
  }

  const setFilterSlug = (slug) => {
    if(activeFilterSlug.value !== slug){
      activeFilterSlug.value = slug

      return true
    }

    return false
  }

  const setMainFilters = (newMainFilters) => {
    mainFilters.value = newMainFilters
  }

  const setAdvancedFilters = (newAdvancedFilters) => {
    advancedFilters.value = newAdvancedFilters
  }



  // Advanced Filters
  // const advancedFilters = computed(() => isStoreTable.value
  //   ? (store.state.datatable.advancedFilters ?? {})
  //   : (props.filterListAdvanced ?? {})
  // )
  const advancedFilters = computed(() => props.filterListAdvanced ?? {})
  const submitAdvancedFilter = () => {
    if(isStoreTable.value){
      store.commit(DATATABLE.UPDATE_DATATABLE_ADVANCED_FILTER, advancedFilters.value)
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.dispatch(ACTIONS.GET_DATATABLE)
    } else {
      context.emit('submitAdvancedFilter', advancedFilters.value)
    }
  }

  const clearAdvancedFilter = () => {
    if(isStoreTable.value){
      store.commit(DATATABLE.RESET_DATATABLE_ADVANCED_FILTER)
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.dispatch(ACTIONS.GET_DATATABLE)
    } else {
      context.emit('clearAdvancedFilter')
    }
  }

  return {
    // Status
    // filterActiveStatus,
    activeFilterSlug,
    activeFilter,
    mainFilters,

    // Search
    search,
    searchModel,

    // Advanced Filters
    advancedFilters,

    filterBtnTitle,

    // Methods
    // changeFilterSlug,
    submitAdvancedFilter,
    clearAdvancedFilter,

    setSearchValue,
    setFilterSlug,
    setMainFilters,
    setAdvancedFilters,
  }
}

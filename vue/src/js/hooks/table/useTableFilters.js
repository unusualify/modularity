// hooks/table/useTableFilters.js
import { computed, ref } from 'vue'
import _ from 'lodash-es'
import { useTableState } from '@/hooks/table'
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
  hideFilters: {
    type: Boolean,
    default: false,
  },
  hideAdvancedFilters: {
    type: Boolean,
    default: false,
  },
})

export default function useTableFilters(props) {
  const { lastParameters, queryParameters } = useTableState()

  // Search
  const search = ref(lastParameters.search ?? props.searchInitialValue ?? '')
  const searchModel = ref(search.value)

  // Filter Status
  const mainFilters = ref( props.filterList ?? [])
  let initialFilterSlug = lastParameters.filter?.status ?? props.navActive ?? 'all'
  if(mainFilters.value.length > 0 && !_.find(mainFilters.value, { slug: initialFilterSlug })){
    initialFilterSlug = 'all'
  }
  const activeFilterSlug = ref(initialFilterSlug)
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
    if(!_.isEqual(advancedFilters.value, newAdvancedFilters)){
      advancedFilters.value = newAdvancedFilters
    }
  }

  // Advanced Filters
  const advancedFilters = ref(props.filterListAdvanced ?? {})
  const activeAdvancedFilters = computed(() => {
    return Object.keys(advancedFilters.value).reduce((collection,key,index) => {
      advancedFilters.value[key].reduce((acc, filter) => {
        if(filter.selecteds?.length > 0){
          if(!acc[key]){
            acc[key] = {}
          }
          acc[key][filter.slug] = filter.selecteds
        }
        return acc
      }, collection)

      return collection
    }, {})
  })

  const clearAdvancedFilter = () => {
    advancedFilters.value = Object.fromEntries(Object.entries(advancedFilters.value).map(([key, val]) => {
      advancedFilters.value[key] = []
      val.map((filter) => filter.selecteds = [])
      return [key, val]
    }))
  }

  return {
    // Status
    activeFilterSlug,
    activeFilter,
    mainFilters,

    // Search
    search,
    searchModel,

    // Advanced Filters
    activeAdvancedFilters,
    advancedFilters,

    filterBtnTitle,

    // Methods
    setSearchValue,
    setFilterSlug,
    setMainFilters,
    setAdvancedFilters,
    clearAdvancedFilter,
  }
}

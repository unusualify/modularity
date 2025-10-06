// hooks/utils/usePagination.js
import { ref, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { getParameters, getURLWithoutQuery, getOrigin, getPath } from '@/utils/pushState'

export const makePaginationProps = propsFactory({
  endpoint: {
    type: String,
  },
  page: {
    type: Number,
    default: 1
  },
  lastPage: {
    type: Number,
    default: -1
  },
  itemsPerPage: {
    type: Number,
    default: 20
  },
  sourceLoading: {
    type: Boolean,
    default: false
  },
  with: {
    type: [Array],
    default: () => []
  },
  scopes: {
    type: [ Array],
    default: () => []
  },
  orders: {
    type: [Array],
    default: () => []
  },
  appends: {
    type: [Array],
    default: () => []
  },
  column: {
    type: [Array],
    default: () => []
  },
  searchKeys: {
    type: [Array],
    default: () => ['name']
  },
  paginationPageKey: {
    type: String,
    default: 'page'
  }
})

export function usePagination(props, context) {
  const rawUrl = ref(getURLWithoutQuery(props.endpoint))
  const activePage = ref(props.page || 1)
  const activeLastPage = ref(props.lastPage || -1)
  const nextPage = ref(activeLastPage.value > 0 ? activeLastPage.value + 1 : props.page || 1)

  const searchModel = ref('')
  const search = ref('')

  const itemsLoading = ref(false)

  const elements = ref(props.items || [])

  const defaultQueryParameters = ref(getParameters(props.endpoint))

  const searchFilterObject = computed(() => {
    return {
      ...(!!searchModel.value ? {search: searchModel.value} : {}),
    }
  })

  const searchFieldsFilter = computed(() => {
    return {
      ...(!!searchModel.value ? {search: props.searchKeys.reduce((acc, key) => {
        acc[key] = searchModel.value
        return acc
      }, {})} : {}),
    }
  })

  const queryParameters = computed(() => {
    let query = new URLSearchParams({
      ...defaultQueryParameters.value,
      ...(!!nextPage.value ? {[props.paginationPageKey]: nextPage.value} : {}),
      itemsPerPage: props.itemsPerPage,
      ...searchFilterObject.value,
      ...(props.with ? {with: props.with.join(',')} : {})
    })

    return query.toString()
  })

  const fullUrl = computed(() => {
    return getURLWithoutQuery(rawUrl.value) + '?' + queryParameters.value
  })

  const setItemsLoading = (value) => {
    itemsLoading.value = value
  }

  const setActivePage = (value) => {
    activePage.value = value
    nextPage.value = activePage.value + 1
  }

  const setActiveLastPage = (value) => {
    if(value != activeLastPage.value) {
      activeLastPage.value = value
    }
  }

  const setElements = (value) => {
    elements.value = value
  }

  const appendElements = (value) => {
    elements.value = elements.value.concat(value)
  }

  const prependElements = (value) => {
    elements.value = value.concat(elements.value)
  }

  const setSearchValue = (value) => {
    search.value = value ?? searchModel.value
  }

  const getSearchData = () => {
    // Placeholder for future implementation
  }

  return {
    rawUrl,
    defaultQueryParameters,
    searchFilterObject,
    searchFieldsFilter,
    queryParameters,
    fullUrl,

    activePage,
    nextPage,
    activeLastPage,

    searchModel,
    search,

    itemsLoading,
    elements,

    setItemsLoading,
    setActivePage,
    setActiveLastPage,
    setElements,
    appendElements,
    prependElements,
    getSearchData
  }
}

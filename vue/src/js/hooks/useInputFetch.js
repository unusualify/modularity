// hooks/useInputFetch.js
import { ref, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { getParameters, getURLWithoutQuery, getOrigin, getPath } from '@/utils/pushState'


export const makeInputFetchProps = propsFactory({
  itemValue: {
    type: String,
    default: 'id'
  },
  itemTitle: {
    type: String,
    default: 'name'
  },
  multiple: {
    type: Boolean,
    default: false
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
  endpoint: {
    type: String,
  },
  items: {
    type: Array,
    default: () => []
  },
  sourceLoading: {
    type: Boolean,
    default: false
  }
})

export default function useInputFetch(props, context) {
  const loading = ref(false)

  const rawEndpoint = ref(getURLWithoutQuery(props.endpoint))
  const activePage = ref(props.page || 1)
  const activeLastPage = ref(props.lastPage || -1)
  const nextPage = ref(activeLastPage.value > 0 ? activeLastPage.value + 1 : props.page || 1)
  const search = ref('')

  const elements = ref(props.items || [])

  const defaultQueryParameters = ref(getParameters(props.endpoint))
  const queryParameters = computed(() => {
    let query = new URLSearchParams({
      ...defaultQueryParameters.value,
      ...(!!nextPage.value ? {page: nextPage.value} : {}),
      itemsPerPage: props.itemsPerPage,
      ...(!!search.value ? {search: search.value} : {})
    })

    return query.toString()
  })

  const fullUrl = computed(() => {
    return getURLWithoutQuery(rawEndpoint.value) + '?' + queryParameters.value
  })

  const getItemsFromApi = async () => {

    if( !(nextPage.value > activeLastPage.value) || activeLastPage.value < 0){
      loading.value = true;

      context.emit('update:input', [
        {
          key: 'sourceLoading',
          value: true
        }
      ])

      return new Promise(() => {
        axios.get(fullUrl.value)
          .then(response => {
            if(response.status == 200){

              if(activeLastPage.value < 0)
                activeLastPage.value = response.data.resource.last_page

              if(search.value == ''){
                elements.value = elements.value.concat(response.data.resource.data ?? []);
              }else{
                elements.value = response.data.resource.data ?? []
              }
              // page.value++;

              activePage.value = response.data.resource.current_page
              nextPage.value = response.data.resource.current_page + 1

              if(!!context.input.value){
                let searchContinue = false;

                if(context.input.value){

                  if(props.multiple){
                    context.input.value.forEach(function(id){
                      if(!elements.value.find((o) => o[props.itemValue] == id)){
                        searchContinue = true
                        return false
                      }
                    })
                  }else {
                    searchContinue = !elements.value.find((o) => o[props.itemValue] == context.input.value)
                  }
                }

                if(searchContinue){
                  getItemsFromApi()
                } else {
                  loading.value = false;
                  context.emit('update:input', [
                    {
                      key: 'sourceLoading',
                      value: false
                    },
                    {
                      key: 'items',
                      value: elements.value
                    },
                    {
                      key: 'lastPage',
                      value: activeLastPage.value
                    },
                    {
                      key: 'page',
                      value: activePage.value
                    }
                  ])
                }
              }else{
                loading.value = false;
                context.emit('update:input', [
                  {
                    key: 'sourceLoading',
                    value: false
                  },
                ])
              }

            }
          })
      })
    }
  }

  const searchOnInputFetch = (searchVal) => {
    if( !!context.input ){
      if(searchVal == ''){
        elements.value = []
        getItemsFromApi()
      }else{
        search.value = searchVal
      }

      return
    }

    search.value = searchVal
    activePage.value = 1
    nextPage.value = 1
    lastPage.value = -1

    if(search.value == ''){
      elements.value = []
    }

    getItemsFromApi()
  }

  return {
    loading,
    elements,
    getItemsFromApi,
    activePage,
    activeLastPage,
    nextPage,
    searchOnInputFetch
  }

}
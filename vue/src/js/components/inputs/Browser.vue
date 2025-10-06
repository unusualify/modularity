<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import { makeSelectProps } from '@/hooks/utils/useSelect'
import { makePaginationProps, usePagination } from '@/hooks/utils/usePagination'
import axios from 'axios'
import { cloneDeep, pick, omit, isEmpty } from 'lodash-es'

defineOptions({
  name: 'v-input-browser',
})

const emit = defineEmits([
  ...makeInputEmits,
  'search'
])

const props = defineProps({
  ...makeInputProps(),
  ...makeSelectProps(),
  ...makePaginationProps(),
  rules: {
    type: [String, Array],
    default: () => []
  },
  useFullUrl: {
    type: Boolean,
    default: false
  },
  preserveInitialValues: {
    type: Boolean,
    default: true
  }
})

const dialog = ref(false)
// const initialValue = ref(props.modelValue)
const selectedItems = ref([])

const {
  rawUrl,
  defaultQueryParameters,
  searchFilterObject,
  searchFieldsFilter,
  queryParameters,
  fullUrl,

  searchModel,
  search,
  itemsLoading,
  elements,

  activePage,
  nextPage,
  activeLastPage,

  setActiveLastPage,
  setItemsLoading,
  setElements,
  appendElements,
  setActivePage,
} = usePagination(props, { emit })

const {
  VInput: VInputRef,
  id,
  boundProps,
  initialValue,
  input,
} = useInput(props, { emit })

const initialExceptIds = ref([])
const initialObject = ref(null)

const comboboxModel = ref(input.value)

// Fetch initial items if modelValue is provided
const fetchInitialItems = async () => {
  if (input.value) {
    let ids = props.multiple ? initialValue.value : [initialValue.value]

    if (props.returnObject) {
      ids = ids.map(value => {
        return value[props.objectIdDefiner ?? props.itemValue]
      })
    }

    initialExceptIds.value = ids

    try {
      const response = await axios.get(rawUrl.value, {
        params: {
          ...defaultQueryParameters.value,
          ids: ids,
        }
      })

      const initialData = response.data

      if (props.returnObject) {
        if (props.multiple) {
          initialValue.value = initialData.map(responseItem => {
            const modelItem = initialValue.value.find(modelItem =>
              modelItem[props.objectIdDefiner ?? props.itemValue] === responseItem[props.itemValue]
            )
            return {
              ...modelItem,
              ...responseItem,
              [props.itemValue]: modelItem[props.itemValue],
            }
          })
        } else {
          initialValue.value = {
            ...input.value,
            ...response.data[0],
            [props.itemValue]: input.value[props.itemValue],
          }
        }

        selectedItems.value = initialValue.value

        // Set initial items if preserveInitialValues is true
        if (props.preserveInitialValues) {
          initialValue.value = cloneDeep(initialValue.value)
        }
      } else {
        selectedItems.value = initialData
        initialObject.value = initialData.length > 0 ? initialData[0] : null
      }

      setElements(response.data || [])
    } catch (error) {
      console.error('Error fetching initial items:', error)
    }
  }
}

const searchUrl = computed(() => {
  return props.useFullUrl ? fullUrl.value : rawUrl.value
})

const canSelectable = computed(() => {
  return props.max ? selectedItems.value.length < props.max : true
})

// Perform search
const performSearch = async () => {
  if (!searchModel.value.trim()) return

  setItemsLoading(true)

  try {
    const exceptIds = cloneDeep(initialExceptIds.value)

    selectedItems.value.forEach(selectedItem => {
      const selectedItemId = props.returnObject ? selectedItem[props.objectIdDefiner ?? props.itemValue] : selectedItem
      if(!exceptIds.includes(selectedItemId)) {
        exceptIds.push(selectedItemId)
      }
    })

    const params = {
      // Exclude initial and selected items from search results
      exceptIds: exceptIds,
      ...searchFilterObject.value,
      ...(activePage.value > 1 ? {[props.paginationPageKey]: activePage.value} : {}),
    }

    if (!props.with) {
      params.eager = Array.isArray(props.with) ? props.with.join(',') : props.with
    }
    if (!isEmpty(props.appends)) {
      params.appends = Array.isArray(props.appends) ? props.appends.join(',') : props.appends
    }

    let queryString = new URLSearchParams(params).toString()
    const response = await axios.get(searchUrl.value + '?' + queryString)
    setActiveLastPage(response.data.resource.last_page || -1)

    // Combine initial items with search results
    const searchResults = response.data.resource.data || []
    const combinedElements = [
      ...searchResults
    ]

    const objectDefinerKey = props.objectIdDefiner ?? props.itemValue
    // protect initial items and selected items from being removed  by the search results if not in the search results
    if(props.multiple) {
      initialValue.value.forEach(initialItem => {
        const foundIndex = combinedElements.findIndex(searchResult => props.returnObject
          ? searchResult[props.itemValue] === initialItem[objectDefinerKey]
          : searchResult[props.itemValue] === initialItem)

        if(foundIndex === -1) {
          combinedElements.unshift({
            ...(props.returnObject ? initialItem : {}),
            [props.itemValue]: props.returnObject ? initialItem[objectDefinerKey] : initialItem,
          })
        }else{
          let sourceItem = combinedElements[foundIndex]
          combinedElements[foundIndex] = {
            ...sourceItem,
            ...(props.returnObject ? initialItem : {}),
            [props.itemValue]: sourceItem[props.itemValue],
          }
        }
      })

      selectedItems.value.forEach(selectedItem => {
        console.log('selectedItem', selectedItem, initialExceptIds.value)
        if(props.returnObject && initialExceptIds.value.includes(selectedItem[objectDefinerKey]))
          return
        else if(initialExceptIds.value.includes(selectedItem))
          return

        const foundIndex = combinedElements.findIndex(searchResult => props.returnObject
          ? searchResult[props.itemValue] === selectedItem[objectDefinerKey]
          : searchResult[props.itemValue] === selectedItem)

        console.log('foundIndex', foundIndex, selectedItem)

        if(foundIndex === -1) {
          combinedElements.unshift({
            ...(props.returnObject ? selectedItem : {}),
            [props.itemValue]: props.returnObject ? selectedItem[objectDefinerKey] : selectedItem,
          })
        }else{
          let sourceItem = combinedElements[foundIndex]
          combinedElements[foundIndex] = {
            ...sourceItem,
            ...(props.returnObject ? selectedItem : {}),
            [props.itemValue]: sourceItem[props.itemValue],
          }
        }
      })
    } else {
      const foundIndex = combinedElements.findIndex(searchResult => props.returnObject
        ? searchResult[props.itemValue] === initialValue.value[objectDefinerKey]
        : searchResult[props.itemValue] === initialValue.value)

      if(foundIndex === -1) {
        combinedElements.unshift({
          ...(props.returnObject ? initialValue.value : initialObject.value),
          [props.itemValue]: props.returnObject ? initialValue.value[objectDefinerKey] : initialValue.value,
        })
      }else{
        let sourceItem = combinedElements[foundIndex]
        combinedElements[foundIndex] = {
          ...sourceItem,
          ...(props.returnObject ? initialValue.value : initialObject.value),
          [props.itemValue]: sourceItem[props.itemValue],
        }
      }
    }

    setElements(combinedElements)
  } catch (error) {
    console.error('Search error:', error)
  } finally {
    setItemsLoading(false)
  }
}

// Open dialog and prepare items
const openDialog = () => {
  dialog.value = true

  // If no items and modelValue exists, fetch initial items
  if (!elements.value.length) {
    fetchInitialItems()
  }
}

const itemIsSelected = (item) => {
  if (props.multiple) {
    if (props.returnObject) {
      return selectedItems.value.some(inputItem =>
        inputItem[props.objectIdDefiner ?? props.itemValue] === item[props.itemValue]
      )
    } else {
      return selectedItems.value.includes(item[props.itemValue])
    }
  } else {
    if (props.returnObject) {
      return comboboxModel.value[props.objectIdDefiner ?? props.itemValue] === item[props.itemValue]
    } else {
      return comboboxModel.value === item[props.itemValue]
    }
  }
}

// Determine if an item is an initial item
const isInitialItem = (item) => {
  return initialExceptIds.value.includes(item[props.itemValue])
}

const isNewSelectedItem = (item) => {
  if(props.multiple) {
    return selectedItems.value.some(selectedItem => selectedItem[props.objectIdDefiner ?? props.itemValue] === item[props.itemValue]) && !isInitialItem(item)
  } else {
    return selectedItems.value === item[props.itemValue] && !isInitialItem(item)
  }
}

const itemIsClickable = (item) => {
  return canSelectable.value || itemIsSelected(item)
}

const maxDescription = computed(() => {
  return props.max ? `(Max. ${props.max} items)` : ''
})

// Toggle item selection
const toggleItemSelection = (item) => {
  // if(props.max && selectedItems.value.length >= props.max) {
  //   return
  // }

  const objectDefinerKey = props.objectIdDefiner ?? props.itemValue

  if (props.multiple) {
    const index = selectedItems.value.findIndex(
      selectedId => props.returnObject
        ? selectedId[objectDefinerKey] === item[props.itemValue]
        : selectedId === item[props.itemValue]
    )
    let newSelectedItems = cloneDeep(selectedItems.value)

    if (index > -1) {
      newSelectedItems.splice(index, 1)
    } else {
      let initialItem = {}
      if(props.returnObject && isInitialItem(item)) {
        initialItem = initialValue.value.find(initialItem => {
          return initialItem[objectDefinerKey] === item[props.itemValue]
        })
      }
      newSelectedItems.push(props.returnObject
        ? {
          ...omit(item, props.itemValue),
          ...(pick(initialItem, props.objectModelValues)),
          [objectDefinerKey]: item[props.itemValue],
        }
        : item[props.itemValue])
    }
    selectedItems.value = newSelectedItems
  } else {
    const index = selectedItems.value.findIndex(selectedItem => selectedItem[props.itemValue] === item[props.itemValue])

    if(index > -1) {
      selectedItems.value = []
    }else{
      selectedItems.value = [item]
    }
  }
}

const removeSelection = (index) => {
  if (props.multiple) {
    input.value.splice(index, 1)
  } else {
    input.value = null
  }
}

watch(selectedItems, (newVal) => {
  if (props.multiple) {
    input.value = newVal.map(item =>
      props.returnObject
        ? props.objectModelValues.includes('*') ? item : pick(item, props.objectModelValues)
        : item
    ).sort((a, b) => {
      // if id key exists on a, and sort by id ascending, if not exist on object, dont sort
      return a.id ? a.id - b.id : 0
    })
    comboboxModel.value = newVal
  } else {
    input.value = props.returnObject
      ? props.objectModelValues.includes('*')
        ? newVal[0]
        : pick(newVal[0], props.objectModelValues)
      : (newVal.length > 0 ? newVal[0][props.itemValue] : null)

    comboboxModel.value = (newVal.length > 0 ? newVal[0][props.itemValue] : null)
  }
})

watch(activePage, (newVal, oldVal) => {
  if(newVal !== oldVal) {
    performSearch()
  }
})

onMounted(() => {
  fetchInitialItems()
})
</script>

<template>
  <v-input
    :ref="VInputRef"
    v-model="input"
    :class="['v-input-browser']"
  >
    <template v-slot:default="defaultSlot">
      <v-combobox
        v-model="comboboxModel"
        v-bind="$lodash.pick(boundProps, [
          'variant', 'density', 'color', 'disabled', 'error', 'errorMessages',
          'prependIcon', 'appendIcon', 'prependInnerIcon', 'appendInnerIcon',
          'itemTitle', 'itemValue'
        ])"
        :return-object="returnObject"
        :clearable="false"
        :label="label"
        :items="elements"
        append-inner-icon="mdi-magnify"
        readonly

        chips

        @click:append-inner="performSearch"
        @click="openDialog"
        @keyup.enter="performSearch"
      >
        <template v-slot:selection="{ item, index }">
          {{ $log(item, initialValue, modelValue, input, ) }}
          <v-chip v-if="item === Object(item)"
            :color="isInitialItem(item)
              ? 'green-lighten-3'
              : 'blue-lighten-3'"
            :text="item.title"
            size="small"
            variant="flat"
            label
            />
            <!-- closable
            @click:close="removeSelection(index)" -->
        </template>
      </v-combobox>

      <ue-modal
        v-model="dialog"
        max-width="600px"
        :title="`Browse ${maxDescription}`"
        descriptionBodyClass="flex-column"
        :cancelText="$t('Cancel')"
        :confirmText="$t('Select')"
        has-close-button
        no-actions
      >
        <template v-slot:body.description>
          <div style="height: 500px !important;">
            <v-text-field
              v-model="searchModel"
              v-bind="$lodash.pick(boundProps, ['variant', 'density', 'color'])"
              @keyup.enter="performSearch"
              @click:append-inner="performSearch"
              append-inner-icon="mdi-magnify"
            />

            <div class="overflow-y-auto" style="height: 360px;">
              <v-progress-linear v-if="itemsLoading"
                indeterminate
                color="primary"
              />
              <v-list v-if="!itemsLoading"
                :multiple="multiple"
                select-strategy="classic"
              >
                <v-list-item v-for="item in elements"
                  :key="`${item[props.itemValue]}-${itemIsClickable(item)}`"
                  @click="toggleItemSelection(item)"

                  :disabled="!itemIsClickable(item)"
                >
                  <template v-slot:prepend>
                    <v-checkbox-btn
                      :model-value="itemIsSelected(item)"
                      @click.stop="toggleItemSelection(item)"

                      :disabled="!itemIsClickable(item)"

                      :color="isInitialItem(item) ? 'red-lighten-3' : 'green-lighten-3'"
                    />
                  </template>

                  <v-list-item-title>
                    {{ item[props.itemTitle] }}
                    <v-chip
                      v-if="isInitialItem(item)"
                      color='red-lighten-3'
                      size="x-small"
                      class="ml-2"
                    >
                      Initial
                    </v-chip>
                    <v-chip
                      v-if="isNewSelectedItem(item)"
                      color="green-lighten-3"
                      size="x-small"
                      class="ml-2"
                    >
                      New
                    </v-chip>
                  </v-list-item-title>
                </v-list-item>
              </v-list>

              <div v-if="!itemsLoading && elements.length === 0" class="text-center pa-4">
                No items found
              </div>
            </div>

            <div class="d-flex justify-center pa-2">
              <v-pagination
                v-model="activePage"
                :length="activeLastPage"
              />
            </div>
          </div>
        </template>
      </ue-modal>
    </template>
  </v-input>
</template>

<style scoped>
.v-input-browser {
  cursor: pointer;
}
</style>

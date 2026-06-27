<template>
  <v-select
    v-bind="$attrs"
    v-model="input"
    class="v-input-remote-api"
    :items="catalogItems"
    :item-value="itemValue"
    :item-title="itemTitle"
    :label="label"
    :loading="itemsLoading"
    :rules="rules"
    :multiple="multiple"
    :return-object="returnObject"
    hide-details="auto"
  >
    <template v-for="(context, slotName) in $slots" v-slot:[slotName]="slotScope"
      :key="`customSlot-${slotName}`"
      >
      <slot :name="slotName" v-bind="slotScope" />
    </template>
  </v-select>
</template>

<script setup>
  import { computed, onMounted, ref } from 'vue'
  import axios from 'axios'
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
  import { makeSelectProps } from '@/hooks/utils/useSelect.js'

  defineOptions({
    name: 'v-input-remote-api',
    inheritAttrs: false,
  })

  const props = defineProps({
    ...makeInputProps(),
    ...makeSelectProps(),
    rules: {
      type: Array,
      default: () => [],
    },
    catalogEndpoint: {
      type: String,
      default: null,
    },
    catalogTotal: {
      type: Number,
      default: null,
    },
  })

  const emit = defineEmits([...makeInputEmits])

  const itemsLoading = ref(false)
  const asyncCatalogItems = ref(null)

  const { input, boundProps } = useInput(props, { emit })

  const itemValue = computed(() => props.itemValue ?? boundProps.value?.itemValue ?? 'id')
  const itemTitle = computed(() => props.itemTitle ?? boundProps.value?.itemTitle ?? 'name')
  const label = computed(() => props.label ?? boundProps.value?.label ?? '')
  const catalogEndpoint = computed(() => props.catalogEndpoint ?? boundProps.value?.catalogEndpoint ?? null)
  const catalogTotal = computed(() => props.catalogTotal ?? boundProps.value?.catalogTotal ?? null)

  const hydratedItems = computed(() => {
    const items = props.items?.length ? props.items : (boundProps.value?.items ?? [])

    return Array.isArray(items) ? items : []
  })

  const catalogItems = computed(() => {
    if (asyncCatalogItems.value !== null) {
      return asyncCatalogItems.value
    }

    return hydratedItems.value
  })

  const countDataRows = (items) => items.filter((row) => row?.[itemValue.value]).length

  const needsRemoteFetch = computed(() => {
    if (!catalogEndpoint.value) {
      return false
    }

    if (hydratedItems.value.length === 0) {
      return true
    }

    if (catalogTotal.value === null) {
      return false
    }

    return countDataRows(hydratedItems.value) < catalogTotal.value
  })

  const prependPleaseSelect = (rows) => {
    if (!rows.length) {
      return rows
    }

    const valueKey = itemValue.value
    const titleKey = itemTitle.value
    const firstItem = rows[0]

    if (!firstItem?.[valueKey]) {
      return rows
    }

    const valueType = typeof firstItem[valueKey]

    return [
      {
        id: 0,
        [valueKey]: valueType === 'number' ? 0 : '',
        [titleKey]: 'Please Select',
      },
      ...rows,
    ]
  }

  const loadCatalog = async () => {
    if (!catalogEndpoint.value || itemsLoading.value) {
      return
    }

    itemsLoading.value = true

    try {
      const { data, status } = await axios.get(catalogEndpoint.value)

      if (status !== 200) {
        return
      }

      const rows = Array.isArray(data?.data) ? data.data : []
      const remoteTotal = data?.meta?.total ?? rows.length

      if (catalogTotal.value !== null && rows.length < catalogTotal.value) {
        console.warn('Remote API catalog endpoint returned incomplete rows.', {
          expected: catalogTotal.value,
          received: rows.length,
        })

        return
      }

      if (remoteTotal > 0 && rows.length < remoteTotal) {
        console.warn('Remote API catalog endpoint returned incomplete rows.', {
          expected: remoteTotal,
          received: rows.length,
        })

        return
      }

      asyncCatalogItems.value = prependPleaseSelect(rows)
    } catch (error) {
      console.error('Error loading remote API catalog:', error)
    } finally {
      itemsLoading.value = false
    }
  }

  onMounted(() => {
    if (needsRemoteFetch.value) {
      void loadCatalog()
    }
  })
</script>

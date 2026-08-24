<template>
  <component :is="component"
    v-bind="$attrs"
    v-model="input"
    class="v-input-remote-api"
    :items="catalogItems"
    :item-value="itemValue"
    :item-title="itemTitle"
    :label="label"
    :loading="itemsLoading"
    :disabled="isDependsDisabled || itemsLoading"
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
  </component>
</template>

<script setup>
  import { computed, inject, onMounted, ref, watch } from 'vue'
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
    component: {
      type: String,
      default: 'v-combobox',
    },
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
    catalogDependsOn: {
      type: Object,
      default: null,
    },
  })

  const emit = defineEmits([...makeInputEmits])

  const itemsLoading = ref(false)
  const asyncCatalogItems = ref(null)
  const activeCatalogKey = ref(null)

  const { input, boundProps } = useInput(props, { emit })

  const formPayload = inject('ueFormPayload', computed(() => ({})))

  const itemValue = computed(() => props.itemValue ?? boundProps.value?.itemValue ?? 'id')
  const itemTitle = computed(() => props.itemTitle ?? boundProps.value?.itemTitle ?? 'name')
  const label = computed(() => props.label ?? boundProps.value?.label ?? '')
  const catalogEndpoint = computed(() => props.catalogEndpoint ?? boundProps.value?.catalogEndpoint ?? null)
  const catalogTotal = computed(() => props.catalogTotal ?? boundProps.value?.catalogTotal ?? null)
  const catalogDependsOn = computed(() => props.catalogDependsOn ?? boundProps.value?.catalogDependsOn ?? null)

  const hasCatalogDependsOn = computed(() => {
    const depends = catalogDependsOn.value

    return !!(depends && typeof depends === 'object' && depends.field && depends.map)
  })

  const siblingValue = computed(() => {
    if (!hasCatalogDependsOn.value) {
      return null
    }

    const field = catalogDependsOn.value.field
    const payload = formPayload?.value ?? formPayload ?? {}

    return payload?.[field] ?? null
  })

  const resolvedCatalogKey = computed(() => {
    if (!hasCatalogDependsOn.value) {
      return null
    }

    const value = siblingValue.value

    if (value === null || value === undefined || value === '') {
      return null
    }

    const mapped = catalogDependsOn.value.map?.[String(value)]

    return typeof mapped === 'string' && mapped !== '' ? mapped : null
  })

  const isDependsDisabled = computed(() => hasCatalogDependsOn.value && !resolvedCatalogKey.value)

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

  const loadCatalog = async (catalogKey = null) => {
    if (!catalogEndpoint.value || itemsLoading.value) {
      return
    }

    itemsLoading.value = true

    try {
      const params = catalogKey ? { catalog: catalogKey } : undefined
      const { data, status } = await axios.get(catalogEndpoint.value, { params })

      if (status !== 200) {
        return
      }

      const rows = Array.isArray(data?.data) ? data.data : []
      const remoteTotal = data?.meta?.total ?? rows.length

      if (catalogTotal.value !== null && !catalogKey && rows.length < catalogTotal.value) {
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
      activeCatalogKey.value = catalogKey
    } catch (error) {
      console.error('Error loading remote API catalog:', error)
    } finally {
      itemsLoading.value = false
    }
  }

  watch(
    resolvedCatalogKey,
    (newKey, oldKey) => {
      if (!hasCatalogDependsOn.value) {
        return
      }

      if (!newKey) {
        asyncCatalogItems.value = []
        activeCatalogKey.value = null

        return
      }

      if (oldKey !== undefined && oldKey !== null && String(oldKey) !== String(newKey)) {
        input.value = null
      }

      if (newKey === activeCatalogKey.value && asyncCatalogItems.value !== null) {
        return
      }

      void loadCatalog(newKey)
    },
    { immediate: true },
  )

  onMounted(() => {
    if (hasCatalogDependsOn.value) {
      return
    }

    if (needsRemoteFetch.value) {
      void loadCatalog()
    }
  })
</script>

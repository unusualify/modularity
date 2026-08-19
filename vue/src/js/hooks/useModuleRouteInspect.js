import { computed, ref } from 'vue'
import axios from 'axios'

/**
 * Module Route Inspect panel API: load report + optional status toggle / heal.
 *
 * @param {{ inspect: string, setStatus: string, heal?: string }} endpoints
 */
export default function useModuleRouteInspect (endpoints) {
  const entries = ref([])
  const findingCount = ref(0)
  const featureKeys = ref([])
  const highlightFeatureKeys = ref([])
  const loading = ref(false)
  const togglingKey = ref(null)
  const healingKey = ref(null)
  const errorMessage = ref(null)
  const allowHeal = ref(false)

  const moduleFilter = ref(null)
  const routeFilter = ref('')
  const findingsOnly = ref(false)
  const featureFilter = ref([])
  const knownModules = ref([])

  const moduleOptions = computed(() => {
    if (knownModules.value.length) {
      return knownModules.value
    }
    const names = new Set(entries.value.map((entry) => entry.module).filter(Boolean))
    return Array.from(names).sort()
  })

  async function loadInspect (overrides = {}) {
    if (!endpoints?.inspect) {
      return
    }

    loading.value = true
    errorMessage.value = null

    const params = {
      module: overrides.module ?? moduleFilter.value ?? undefined,
      route: overrides.route ?? (routeFilter.value?.trim() || undefined),
      findings_only: overrides.findings_only ?? (findingsOnly.value || undefined),
      feature: Array.isArray(overrides.feature)
        ? overrides.feature.join(',')
        : (featureFilter.value?.length ? featureFilter.value.join(',') : undefined),
    }

    Object.keys(params).forEach((key) => {
      if (params[key] === undefined || params[key] === null || params[key] === '' || params[key] === false) {
        delete params[key]
      }
    })

    try {
      const { data } = await axios.get(endpoints.inspect, { params })
      entries.value = Array.isArray(data?.entries) ? data.entries : []
      findingCount.value = Number(data?.finding_count ?? 0)
      if (Array.isArray(data?.feature_keys)) {
        featureKeys.value = data.feature_keys
      }
      if (Array.isArray(data?.highlight_feature_keys)) {
        highlightFeatureKeys.value = data.highlight_feature_keys
      }
      if (typeof data?.allow_heal === 'boolean') {
        allowHeal.value = data.allow_heal
      }

      const seen = new Set(knownModules.value)
      for (const entry of entries.value) {
        if (entry?.module) {
          seen.add(entry.module)
        }
      }
      knownModules.value = Array.from(seen).sort()
    } catch (e) {
      errorMessage.value = e.response?.data?.message ?? e.message ?? 'Failed to load inspect report'
      entries.value = []
      findingCount.value = 0
    } finally {
      loading.value = false
    }
  }

  async function setEnabled (moduleName, routeName, enabled) {
    if (!endpoints?.setStatus) {
      throw new Error('Status endpoint is not available')
    }

    const key = `${moduleName}::${routeName}`
    togglingKey.value = key
    errorMessage.value = null

    try {
      const { data } = await axios.patch(endpoints.setStatus, {
        module: moduleName,
        route: routeName,
        enabled: !!enabled,
      })

      const updated = data?.entry
      if (updated) {
        const index = entries.value.findIndex(
          (entry) => entry.module === moduleName && entry.route === routeName
        )
        if (index >= 0) {
          entries.value.splice(index, 1, updated)
        }
      } else {
        await loadInspect()
      }

      return updated
    } catch (e) {
      errorMessage.value = e.response?.data?.message ?? e.message ?? 'Failed to update route status'
      throw e
    } finally {
      togglingKey.value = null
    }
  }

  async function healFinding (moduleName, routeName, code, { feature = null, dryRun = true, force = false } = {}) {
    if (!endpoints?.heal) {
      throw new Error('Heal endpoint is not available')
    }

    const key = `${moduleName}::${routeName}::${code}`
    healingKey.value = key
    errorMessage.value = null

    try {
      const { data } = await axios.post(endpoints.heal, {
        module: moduleName,
        route: routeName,
        code,
        feature: feature || undefined,
        dry_run: !!dryRun,
        force: !!force,
      })

      const updated = data?.entry
      if (updated) {
        const index = entries.value.findIndex(
          (entry) => entry.module === moduleName && entry.route === routeName
        )
        if (index >= 0) {
          entries.value.splice(index, 1, updated)
        }
      }

      return data
    } catch (e) {
      errorMessage.value = e.response?.data?.message ?? e.message ?? 'Failed to heal finding'
      throw e
    } finally {
      healingKey.value = null
    }
  }

  function presentFeatures (entry) {
    const features = entry?.features || {}
    return Object.keys(features).filter((key) => features[key]?.present)
  }

  return {
    entries,
    findingCount,
    featureKeys,
    highlightFeatureKeys,
    loading,
    togglingKey,
    healingKey,
    errorMessage,
    allowHeal,
    moduleFilter,
    routeFilter,
    findingsOnly,
    featureFilter,
    moduleOptions,
    loadInspect,
    setEnabled,
    healFinding,
    presentFeatures,
  }
}

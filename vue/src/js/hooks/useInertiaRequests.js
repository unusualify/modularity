// hooks/useInertiaRequests.js
import { computed } from 'vue'
import { useStore } from 'vuex'
import { getActiveInertiaRequestCount, hasActiveInertiaRequests } from '@/setup/inertia-interceptors'

/**
 * Composable for working with Inertia request states
 * Similar to useAxiosRequests but for Inertia.js
 */
export default function useInertiaRequests() {
  const Store = useStore()

  // Computed properties for reactive request state
  const activeRequestCount = computed(() =>
    Store.state.config?.axiosRequestCount || 0
  )

  const hasActiveRequests = computed(() =>
    activeRequestCount.value > 0
  )

  const isLoading = computed(() =>
    hasActiveRequests.value
  )

  // Methods
  const getRequestCount = () => getActiveInertiaRequestCount()
  const hasRequests = () => hasActiveInertiaRequests()

  return {
    // Reactive state
    activeRequestCount,
    hasActiveRequests,
    isLoading,

    // Methods
    getRequestCount,
    hasRequests,
  }
}

/**
 * Composable for showing loading indicators based on Inertia requests
 */
export function useInertiaLoading() {
  const { isLoading, activeRequestCount } = useInertiaRequests()

  const loadingText = computed(() => {
    const count = activeRequestCount.value
    if (count === 0) return ''
    if (count === 1) return 'Loading...'
    return `Loading... (${count} requests)`
  })

  return {
    isLoading,
    loadingText,
    activeRequestCount,
  }
}

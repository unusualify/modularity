import { router } from '@inertiajs/vue3'
import store from '@/store'
import { CONFIG } from '@/store/mutations'
import { USER } from '@/store/mutations'

/**
 * Setup Inertia request interceptors
 * Similar to Axios interceptors but for Inertia.js requests
 */
export function setupInertiaInterceptors() {
  // Request start interceptor
  router.on('start', (event) => {
    // console.log('Inertia request started:', event.detail.visit)
    store.commit(CONFIG.INCREASE_AXIOS_REQUEST)
  })

  // Request finish interceptor
  router.on('finish', (event) => {
    // console.log('Inertia request finished:', event.detail.visit)
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)

    // Handle successful responses
    if (event.detail.visit.completed) {
      handleInertiaSuccessResponse(event.detail.visit)
    }
  })

  // Request error interceptor
  router.on('error', (event) => {
    // console.log('Inertia request error:', event.detail)
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)
    handleInertiaErrorResponse(event.detail)
  })

  // Request exception interceptor
  router.on('exception', (event) => {
    // console.log('Inertia request exception:', event.detail)
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)
    handleInertiaErrorResponse(event.detail)
  })

  // Optional: Progress interceptor for loading indicators
  router.on('progress', (event) => {
    // Handle progress updates if needed
    // console.log('Inertia request progress:', event.detail.progress)
  })
}

/**
 * Handle successful Inertia responses
 * Similar to handleSuccessResponse for Axios
 */
function handleInertiaSuccessResponse(visit) {
  // console.log('Inertia success response:', visit)

  // Handle authentication redirects
  if (visit.response?.status === 401) {
    store.commit(USER.OPEN_LOGIN_MODAL)
  }

  // Handle CSRF token mismatch
  if (visit.response?.status === 419) {
    // Handle CSRF token mismatch
    console.warn('CSRF token mismatch detected in Inertia response')
    // store.commit(USER.OPEN_LOGIN_MODAL)
  }

  // Add any other success response handling here
  // You can access visit.data for response data
  // You can access visit.response for response details
}

/**
 * Handle Inertia request errors
 * Similar to handleErrorResponse for Axios
 */
function handleInertiaErrorResponse(errorDetail) {
  // console.log('Inertia error response:', errorDetail)

  // Handle authentication errors
  if (errorDetail.response?.status === 401) {
    // store.commit(USER.OPEN_LOGIN_MODAL)
  }

  // Handle validation errors
  if (errorDetail.response?.status === 422) {
    console.warn('Validation errors:', errorDetail.errors)
    // Handle validation errors if needed
  }

  // Handle server errors
  if (errorDetail.response?.status >= 500) {
    console.error('Server error:', errorDetail)
    // Handle server errors if needed
  }

  // Add any other error response handling here
}

/**
 * Get current number of active Inertia requests
 */
export function getActiveInertiaRequestCount() {
  return store.state.config?.axiosRequestCount || 0
}

/**
 * Check if any Inertia requests are currently active
 */
export function hasActiveInertiaRequests() {
  return getActiveInertiaRequestCount() > 0
}

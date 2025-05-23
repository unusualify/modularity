import { reactive, inject } from 'vue'
import { isObject, omit } from 'lodash-es'
import { removeParameterFromHistory } from '@/utils/pushState'

/**
 * Manages a single, global dialog state.
 */
class ModalService {
  constructor() {
    this.state = reactive({
      visible: false,
      component: null,
      props: {},
      emits: {},
      slots: {},
      data: undefined,
      onClose: undefined,
      modalProps: {}
    })
  }

  /**
   * Open a dialog with:
   *  - component: the Vue component (or async component)
   *  - options: { props, emits, data, onClose }
   */
  open(component, options = {}) {
    this.state.component = component
    this.state.props     = options.props || {}
    this.state.emits     = options.emits || {}
    this.state.data      = options.data
    this.state.onClose   = options.onClose
    this.state.visible   = true
    this.state.modalProps = options.modalProps || {}
    this.state.slots     = options.slots || {}
  }

  /**
   * Close the dialog, optionally passing returnData to onClose callback.
   */
  close(returnData) {
    this.state.visible = false
    const cb = this.state.onClose

    // reset all state
    this.state.component = null
    this.state.props     = {}
    this.state.emits     = {}
    this.state.data      = undefined
    this.state.onClose   = undefined
    this.state.modalProps = {}
    this.state.slots     = {}
    if (cb) {
      cb(returnData)
    }
  }
}

/**
 * Vue plugin installer
 */
export default {
  install(app) {
    const service = new ModalService()
    // provide/inject key
    app.provide('modalService', service)
    // for Options API: this.$dialog
    app.config.globalProperties.$modalService = service
    window.$modalService = service

    // Handle URL query parameters to open modals
    const handleUrlQueryParameters = () => {
      const urlParams = new URLSearchParams(window.location.search)
      const modalParam = urlParams.get('modalService')

      const modalData = JSON.parse(modalParam)

      if (isObject(modalData)) {
        // Get all other parameters to pass as data

        // Dispatch modal opening based on the modal parameter
        // This needs to be adapted based on how your modals are registered/loaded
        try {
          // You'll need to implement this function to map modal names to components
          // const modalComponent = app.config.globalProperties.$modalComponents?.[modalParam]

          service.open(modalData.component ?? null, omit(modalData, ['component']))

          removeParameterFromHistory('modalService')

        } catch (error) {
          console.error('Failed to open modal from URL parameters:', error)
        }
      }
    }

    // Execute once the application is mounted
    setTimeout(handleUrlQueryParameters, 0)

  }
}


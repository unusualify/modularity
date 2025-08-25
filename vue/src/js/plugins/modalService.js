import { reactive, inject } from 'vue'
import { isObject, omit } from 'lodash-es'
import { removeParameterFromHistory, getParameters } from '@/utils/pushState'

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

  getModalServiceData(parameters) {
    if(parameters.modalService) {
      return parameters.modalService
    }

    return null
  }

  getModalServiceKey(parameters) {

    if(parameters.modalServiceKey) {
      return parameters.modalServiceKey
    }

    return null
  }

  async handleObject(parameters) {
    const modalServiceKey = this.getModalServiceKey(parameters)
    const modalServiceData = this.getModalServiceData(parameters)
    let modalData = null
    let openedModal = null

    if(modalServiceKey) {
      // Handle modalServiceKey parameter (session-based)
      try {
        const response = await fetch(`/api/modal-service/${modalServiceKey}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        })

        if (response.ok) {
          const result = await response.json()
          modalData = result.modalService
        } else {
          console.error('Failed to fetch modal service data from session')
          return
        }
        removeParameterFromHistory('modalServiceKey')
      } catch (error) {
        console.error('Failed to fetch modal service data:', error)
        return
      }
    }

    if(!modalData && modalServiceData) {
      try {
        modalData = JSON.parse(modalServiceData)
      } catch (error) {
        console.error('Failed to parse modalService parameter:', error)
        return
      }
    }

    if (isObject(modalData)) {
      // Get all other parameters to pass as data

      // Dispatch modal opening based on the modal parameter
      // This needs to be adapted based on how your modals are registered/loaded
      try {
        // You'll need to implement this function to map modal names to components
        // const modalComponent = app.config.globalProperties.$modalComponents?.[modalParam]
        this.open(modalData.component ?? null, omit(modalData, ['component']))

        removeParameterFromHistory('modalService')

      } catch (error) {
        console.error('Failed to open modal from URL parameters:', error)
      }
    }

    return null
  }

  async handleUrlQueryParameters(url = null) {
    const urlParams = getParameters(url ?? window.location)

    await this.handleObject(urlParams)
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

    // Execute once the application is mounted
    setTimeout(async () => {
      await service.handleUrlQueryParameters()
    }, 0)

  }
}


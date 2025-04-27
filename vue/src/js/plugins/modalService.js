import { reactive, inject } from 'vue'

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
  }
}


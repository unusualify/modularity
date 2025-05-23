import api from '@/store/api/form'
import { FORM, ALERT, MEDIA_LIBRARY } from '@/store/mutations'
import ACTIONS from '@/store/actions'

import { getSubmitFormData, getModel } from '@/utils/getFormData.js'

const getFieldIndex = (stateKey, field) => {
  return stateKey.findIndex(f => f.name === field.name)
}

const state = {
  baseUrl: window[import.meta.env.VUE_APP_NAME].STORE.form.baseUrl || '',
  inputs: window[import.meta.env.VUE_APP_NAME].STORE.form.inputs || {},
  // saveUrl: window[import.meta.env.VUE_APP_NAME].STORE.form.saveUrl || '',

  serverValid: true,
  /**
   * Form errors after submitting
   * @type {Object}
   */
  errors: {},

  // fields: window[import.meta.env.VUE_APP_NAME].STORE.form.inputs.forEach(function(el ){
  //     return {
  //       name: el.name,
  //       value: null
  //     };
  // }),

  // editedItem: window[import.meta.env.VUE_APP_NAME].STORE.form.inputs.reduce( (a,c) => (a[c.name] = c.default ?? '', a), {}),
  // editedItem: Object.keys(window[import.meta.env.VUE_APP_NAME].STORE.form.inputs).reduce( (a,c) => (a[window[import.meta.env.VUE_APP_NAME].STORE.form.inputs[c].name] = window[import.meta.env.VUE_APP_NAME].STORE.form.inputs[c].hasOwnProperty('default') ? window[import.meta.env.VUE_APP_NAME].STORE.form.inputs[c].default : '', a), {}),
  editedItem: window[import.meta.env.VUE_APP_NAME].STORE.form.inputs
    ? getModel(window[import.meta.env.VUE_APP_NAME].STORE.form.inputs, null)
    : {},

  /**
   * Force reload on successful submit
   * @type {Boolean}
   */
  // reloadOnSuccess: window[import.meta.env.VUE_APP_NAME].STORE.form.reloadOnSuccess || false,

  /**
   * Determines if the form should prevent submitting before an input value is pushed into the store
   * @type {Boolean}
   */
  // isSubmitPrevented: false,

  loading: false,
  taggableItems: {}
}

// getters
const getters = {
  defaultItem: state => {
    return getModel(state.inputs)
  }
}

const mutations = {
  [FORM.SET_EDITED_ITEM] (state, item) {
    // state.editedItem = getModel(state.inputs, Object.assign({}, item), this._state.data)
    state.editedItem = Object.assign({}, item)
    // store._modules.root.state
    // commit(MEDIA_LIBRARY.ADD_MEDIAS, )
  },
  [FORM.RESET_EDITED_ITEM] (state) {
    state.editedItem = getModel(state.inputs)
  },
  // ----------- Form fields ----------- //
  [FORM.UPDATE_FORM_FIELD] (state, field) {
    let fieldValue = field.locale ? {} : null
    const fieldIndex = getFieldIndex(state.fields, field)
    // Update existing form field
    if (fieldIndex !== -1) {
      if (field.locale) fieldValue = state.fields[fieldIndex].value || {}
      // remove existing field
      state.fields.splice(fieldIndex, 1)
    }

    if (field.locale) fieldValue[field.locale] = field.value
    else fieldValue = field.value

    state.fields.push({
      name: field.name,
      value: fieldValue
    })
  },
  // ----------- Form errors and Loading ----------- //
  [FORM.UPDATE_FORM_LOADING] (state, loading) {
    state.loading = loading || !state.loading
  },
  [FORM.SET_FORM_ERRORS] (state, errors) {
    state.errors = errors
  },
  [FORM.SET_SERVER_VALID] (state, isValid) {
    state.serverValid = isValid
  },
  [FORM.CLEAR_FORM_ERRORS] (state) {
    state.errors = []
  },

  [FORM.SET_TAGGABLE_ITEMS] (state, { taggable_type, items }) {
    state.taggableItems[taggable_type] = items
  },
  [FORM.ADD_TAGGABLE_ITEM] (state, { taggable_type, item }) {
    state.taggableItems[taggable_type].push(item)
  }
}

const actions = {
  [ACTIONS.SAVE_FORM] ({ commit, state, getters, rootState, dispatch }, { item = null, callback = null, errorCallback = null, plain = false }) {
    commit(FORM.CLEAR_FORM_ERRORS)
    commit(FORM.UPDATE_FORM_LOADING, true)

    // we can now create our submitted data object out of:
    // - our just created fields object,
    // - publication properties
    // - selected medias and browsers
    // - created blocks and repeaters

    // const data = getFormData(rootState)
    const data = plain ? item : getSubmitFormData(state.inputs, item ?? state.editedItem, rootState)

    // const method = rootState.publication.createWithoutModal ? 'post' : 'put'
    let method = 'post'
    let url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.store

    if (Object.prototype.hasOwnProperty.call(data, 'id')) {
      method = 'put'
      url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.update.replace(':id', data.id)
    }

    api[method](url, data, function (response) {
      commit(FORM.UPDATE_FORM_LOADING, false)
      if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
        commit(FORM.SET_SERVER_VALID, false)
        commit(FORM.SET_FORM_ERRORS, response.data.errors)
      } else if (Object.prototype.hasOwnProperty.call(response.data, 'variant') && response.data.variant.toLowerCase() === 'success') {
        commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
        commit(FORM.SET_SERVER_VALID, true)

        if (method === 'post') {
          commit(FORM.RESET_EDITED_ITEM)
        }

        try {
          dispatch(ACTIONS.GET_DATATABLE)
        } catch (error) {

        }
        // if (!data.hasOwnProperty('reload') || data.reload) { dispatch(ACTIONS.GET_DATATABLE) }
      }

      if (callback && typeof callback === 'function') callback(response.data)
    }, function (response) {
      commit(FORM.UPDATE_FORM_LOADING, false)
      if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
        commit(FORM.SET_FORM_ERRORS, response.data.errors)
        commit(FORM.SET_SERVER_VALID, false)
      } else if (Object.prototype.hasOwnProperty.call(response.data, 'exception')) {
        commit(ALERT.SET_ALERT, { message: 'Your submission could not be processed.', variant: 'error' })
      } else {
        dispatch(ACTIONS.HANDLE_ERRORS, response.data)
        commit(ALERT.SET_ALERT, { message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
      }

      if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
    })
  },

  [ACTIONS.HANDLE_ERRORS] ({ commit, state, getters, rootState }, errors) {
    const repeaters = rootState.repeaters
    // Translate the errors to their respective fields.
    Object.keys(errors).forEach((errorKey) => {
      const splitted = errorKey.split('.')

      if (splitted.length >= 4) {
        const type = splitted[0]
        const subType = splitted[1]
        const index = splitted[2]
        const field = splitted[3]

        if (type === 'repeaters') {
          const id = repeaters[subType][index].id
          const newErrorKey = `blocks[${id}][${field}]`
          errors[newErrorKey] = errors[errorKey]
        }
      }
    })

    commit(FORM.SET_FORM_ERRORS, errors)
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}

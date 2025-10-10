import { FORM } from '@/store/mutations'

const state = {
  // fields: window[import.meta.env.VUE_APP_NAME].STORE.form.inputs.forEach(function(el ){
  //     return {
  //       name: el.name,
  //       value: null
  //     };
  // }),
}

// getters
const getters = {

}

const mutations = {
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
}

const actions = {

}

export default {
  state,
  getters,
  actions,
  mutations
}

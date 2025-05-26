// hooks/useFilepond.js

import { reactive, toRefs, computed, ref, inject } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { omit } from 'lodash-es'

export const makeFilepondProps = propsFactory({
  hint: {
    type: String,
    default: null,
  },
  hideDetails: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  min: {
    type: Number,
  },
  rules: {
    type: Array,
    default: () => [],
  },
  noRules: {
    type: Boolean,
    default: false
  },
  hintWeight: {
    type: String,
    default: 'thin',
  },
  maxFiles: {
    type: Number,
    default: 2,
  },
  endPoints: {
    type: Object,
    default: () => ({}),
  },
  class: {
    type: String,
    default: '',
  },
  labelWeight: {
    type: String,
    default: 'regular',
  },
  subtitle: {
    type: String,
    default: null,
  },
  subtitleWeight: {
    type: String,
    default: 'thin',
  },
  acceptedFileTypes: {
    type: String,
    default: '',
  },

  allowImagePreview: {
    type: Boolean,
    default: false
  },
  allowMultiple: {
    type: Boolean,
    default: false
  },
  allowProcess: {
    type: Boolean,
    default: true
  },
  allowRemove: {
    type: Boolean,
    default: true
  },
  allowDrop: {
    type: Boolean,
    default: true
  },
  allowReorder: {
    type: Boolean,
    default: false
  },
  allowReplace: {
    type: Boolean,
    default: false
  },

  dropOnPage: {
    type: Boolean,
    default: false
  },
  dropOnElement: {
    type: Boolean,
    default: true
  },
  dropValidation: {
    type: Boolean,
    default: false
  },


})

export default function useFilepond(props, context) {

  return {}
}

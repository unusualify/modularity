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
})

export default function useFilepond(props, context) {

  return {}
}

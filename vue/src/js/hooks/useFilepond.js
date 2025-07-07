// hooks/useFilepond.js

import { reactive, toRefs, computed, ref, inject, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { omit } from 'lodash-es'

import { useValidation } from '@/hooks'

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
  allowFileSizeValidation: {
    type: Boolean,
    default: true
  },
  maxFileSize: {
    type: String,
    default: '5MB'
  },
  minFileSize: {
    type: String,
    default: '1KB'
  },
  maxTotalFileSize: {
    type: String,
    default: null
  },
  labelMaxFileSize: {
    type: String,
    default: 'Maximum file size is {filesize}'
  },
  labelMaxFileSizeExceeded: {
    type: String,
    default: 'File is too large'
  }
})

export default function useFilepond(props, context) {
  const { requiredRule } = useValidation(props)

  const rawRules = window.__data_get(props.obj, 'schema.rawRules', '') || '';
  const filepondRules = ref(props.rules ?? [])
  const max = ref(props.maxFiles)
  const min = ref(props.min)

  if(props.isEditing ? props.editable === true : props.creatable === true){
    if(!props.noRules && props.min && props.min > 0 && !rawRules.match(/required:array:\d+/)){
      filepondRules.value.push(requiredRule.value('array', props.min))
    }
  }

  if(min.value){
    if(max.value < min.value) {
      max.value = min.value
    }
  }

  if(max.value < 1) {
    max.value = 5
  }

  watch(() => props.rules, (newVal) => {
    filepondRules.value = newVal
  })

  return {
    filepondRules,
    max
  }
}

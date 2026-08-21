// hooks/useFilepond.js

import { reactive, toRefs, computed, ref, inject, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { omit } from 'lodash-es'

import { useValidation } from '@/hooks'
import { dataGet } from '@/utils/helpers'

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

  const rawRules = dataGet(props.obj, 'schema.rawRules', '') || '';
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

  const pendingUploads = ref(0)
  const isUploading = computed(() => pendingUploads.value > 0)

  const startUpload = () => {
    pendingUploads.value += 1
  }

  const finishUpload = () => {
    pendingUploads.value = Math.max(0, pendingUploads.value - 1)
  }

  const commitProcessedInput = (currentInput, file) => {
    return commitProcessedFilepondInput(currentInput, file, props.endPoints, {
      allowMultiple: props.allowMultiple,
    })
  }

  const canRemoveFromModel = (file) => {
    return shouldCommitFilepondRemove(file, {
      isUploading: isUploading.value,
      allowMultiple: props.allowMultiple,
    })
  }

  return {
    filepondRules,
    max,
    pendingUploads,
    isUploading,
    startUpload,
    finishUpload,
    commitProcessedInput,
    canRemoveFromModel,
  }
}

/**
 * Server process result only — never the local FilePond file item.
 */
export function toProcessedFilepondEntry(file, endPoints = {}) {
  const uuid = file?.serverId
  if (!uuid) {
    return null
  }

  return {
    uuid,
    file_name: file.filename,
    source: `${endPoints.load ?? ''}${uuid}`,
  }
}

/**
 * Commit a file to v-model only after FilePond `process` has a server id.
 * Single-file fields replace the previous committed value so replace-upload
 * does not dirty the form with a cleared array mid-request.
 */
export function commitProcessedFilepondInput(currentInput, file, endPoints, { allowMultiple = false } = {}) {
  const entry = toProcessedFilepondEntry(file, endPoints)
  const list = Array.isArray(currentInput) ? currentInput : []

  if (!entry) {
    return list
  }

  if (!allowMultiple) {
    return [entry]
  }

  return list.concat(entry)
}

/**
 * Local / in-flight items were never written to modelValue.
 * While a single-file replacement is uploading, keep the previous committed value.
 */
export function shouldCommitFilepondRemove(file, { isUploading = false, allowMultiple = false } = {}) {
  if (!file?.serverId) {
    return false
  }

  if (isUploading && !allowMultiple) {
    return false
  }

  return true
}

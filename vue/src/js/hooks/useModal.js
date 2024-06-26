// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import htmlClasses from '@/utils/htmlClasses'
import { useI18n } from 'vue-i18n'

const defaultWidths = {
  xs: '320px',
  sm: '480px',
  md: '540px',
  lg: '1080px',
  xl: '1200px'
}

export const makeModalProps = propsFactory({
  modelValue: {
    type: Boolean,
    default: false
  },
  transition: {
    type: String,
    default: 'bottom'
  },
  widthType: {
    type: String,
    default: 'md',
    validator: v => Object.prototype.hasOwnProperty.call(defaultWidths, v)
  },
  systembar: {
    type: Boolean,
    default: false
  },
  fullscreen: {
    type: Boolean,
    default: false
  },
  cancelText: {
    type: String,
    default: ''
  },
  confirmText: {
    type: String,
    default: ''
  },
  descriptionText: {
    type: String,
    default: ''
  }
})

export const makeModalMediaProps = propsFactory({
  modalTitlePrefix: {
    type: String,
    default: function (props) {
      return useI18n().t('media-library.title', 'Media Library')
    }
  },
  btnLabelSingle: {
    type: String,
    default: function () {
      return useI18n().t('media-library.insert', 'Insert')
    }
  },
  btnLabelUpdate: {
    type: String,
    default: function () {
      return useI18n().t('media-library.update', 'Update')
    }
  },
  btnLabelMulti: {
    type: String,
    default: function () {
      return useI18n().t('media-library.insert', 'Insert')
    }
  },
})

// by convention, composable function names start with "use"
export default function useModal (props, context) {
  const { modelValue } = toRefs(props)

  const states = reactive({
    modalClass: htmlClasses.modal,
    width: props.widthType,
    full: props.fullscreen,
    dialog: computed({
      get: () => {
        return modelValue.value
      },
      set: (value, old) => {
        // __log('modalOpened setter')
        methods.emitModelValue(value)
      }
    }),
    togglePersistent: computed(() => props.persistent),
    toggleScrollable: computed(() => props.scrollable),
    modalWidth: computed(() => props.widthType ? defaultWidths[props.widthType] : null)

  })
  const methods = reactive({
    emitModelValue: function (val) {
      context.emit('update:modelValue', val)
    },
    emitOpened: function (val) {
      context.emit('opened', val)
    },
    clickOutside: function (event) {
      context.emit('click:outside', event)
    }
  })

  // const computed =

  // expose managed state as return value
  return {
    // ...toRefs(_props),
    ...toRefs(methods),
    ...toRefs(states)
  }
}

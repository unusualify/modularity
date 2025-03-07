// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed, ref } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import htmlClasses from '@/utils/htmlClasses'
import { useI18n } from 'vue-i18n'

const defaultWidths = {
  xs: '320px',
  sm: '480px',
  md: '720px',
  lg: '1080px',
  xl: '1600px'
}

export const makeModalProps = propsFactory({
  modelValue: {
    type: Boolean,
    default: false
  },
  transition: {
    type: String,
    default: 'bottom',

    values: [
      'dialog-transition',
      'dialog-bottom-transition',
      'dialog-top-transition',
      'fade-transition',
      'scale-transition',
      'slide-x-transition',
      'slide-y-transition',
      'slide-x-reverse-transition',
      'slide-y-reverse-transition',
      'scroll-x-transition',
      'scroll-y-transition',
    ]
  },
  widthType: {
    type: String,
    default: 'md',
    validator: v => Object.prototype.hasOwnProperty.call(defaultWidths, v)
  },
  hasSystembar: {
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
  },
  confirmCallback: {
    type: Function,
  },

  rejectLoading: {
    type: Boolean,
    default: false
  },
  confirmLoading: {
    type: Boolean,
    default: false
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

  const full = ref(props.fullscreen)
  const states = reactive({
    modalClass: htmlClasses.modal,
    width: props.widthType,
    full,
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
    modalWidth: computed(() => props.widthType && !full.value ? defaultWidths[props.widthType] : null)

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

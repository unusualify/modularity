// hooks/utils/useSelect.js
import { ref, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeSelectProps = propsFactory({
  itemValue: {
    type: String,
    default: 'id'
  },
  itemTitle: {
    type: String,
    default: 'name'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  items: {
    type: Array,
    default: () => []
  },
  returnObject: {
    type: Boolean,
    default: false
  },
  objectIdDefiner: {
    type: String,
  },
  convertObject: {
    type: Boolean,
    default: false
  },
  objectModelValues: {
    type: Array,
    default: () => ['*']
  },
  max: {
    type: Number,
    default: null
  }
})

export default function useSelect(props, context) {

  return {

  }
}

// hooks/table/useTableActions.js
import { computed } from 'vue'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types


export const makeTableActionsProps = propsFactory({
  actionsPosition: {
    type: String,
    default: 'top'
  },
  actions: {
    type: Array,
    default: () => []
  }
})

export default function useTableActions(props, context) {

  return {}
}


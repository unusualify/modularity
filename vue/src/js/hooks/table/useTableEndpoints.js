// hooks/table/useTableEndpoints.js
import { computed } from 'vue'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableEndpointsProps = propsFactory({
  endpoints: {
    type: Object,
    default: () => ({})
  },
})

export default function useTableEndpoints(props) {

  const createUrl = computed(() =>
    props.endpoints.create ?? null
  )
  const editUrl = computed(() =>
    props.endpoints.edit ??  null
  )
  const reorderUrl = computed(() =>
    props.endpoints.reorder ?? null
  )
  const indexUrl = computed(() =>
    props.endpoints.index ?? null
  )

  return {
    // refs
    createUrl,
    editUrl,
    reorderUrl,
    indexUrl,

  }
}

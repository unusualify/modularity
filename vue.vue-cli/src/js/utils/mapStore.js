import { computed } from 'vue'
import { useStore } from 'vuex'

const mapGetters = () => {
  const store = useStore()
  return Object.fromEntries(
    Object.keys(store.getters).map(
      getter => [getter, computed(() => store.getters[getter])]
    )
  )
}
const mapState = () => {
  const store = useStore()
  return Object.fromEntries(
    Object.keys(store.getters).map(
      getter => [getter, computed(() => store.getters[getter])]
    )
  )
}

export { mapGetters, mapState }

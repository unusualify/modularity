// useUser.js
import { computed, toRefs, reactive } from 'vue'
import { useStore } from 'vuex'
import { useAuthorization } from '@/hooks'

export default function useUser() {
  const store = useStore()
  const Authorization = useAuthorization()

  const state = reactive({
    isGuest: computed(() => {
      return store.getters.isGuest ?? true
    }),
    isSuperAdmin: computed(() => {
      return Authorization.isSuperAdmin ?? false
    }),
    isClient: computed(() => {
      return Authorization.isClient ?? false
    }),
    timezone: computed(() => {
      return store.state.user.timezone ?? 'Europe/London'
    })
  })

  return {
    ...toRefs(state),
    can: Authorization.can,
    isYou: Authorization.isYou,
    hasRoles: Authorization.hasRoles,
  }
}

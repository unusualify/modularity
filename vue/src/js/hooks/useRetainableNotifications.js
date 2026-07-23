import { computed } from 'vue'
import { useStore } from 'vuex'
import { RETAINABLE_NOTIFICATION } from '@/store/mutations'

export default function useRetainableNotifications () {
  const store = useStore()

  const items = computed(() => store.getters.retainableNotifications ?? [])
  const count = computed(() => store.getters.retainableNotificationCount ?? 0)
  const panelOpen = computed(() => store.getters.retainableNotificationsPanelOpen ?? false)

  const push = (payload) => {
    store.commit(RETAINABLE_NOTIFICATION.PUSH, payload)
  }

  const dismiss = (id) => {
    store.commit(RETAINABLE_NOTIFICATION.DISMISS, id)
  }

  const dismissByGroup = (retainGroup) => {
    store.commit(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, retainGroup)
  }

  const prune = () => {
    store.commit(RETAINABLE_NOTIFICATION.PRUNE)
  }

  const hydrate = (userId) => {
    store.commit(RETAINABLE_NOTIFICATION.HYDRATE, { userId })
  }

  const toggle = (open) => {
    store.commit(RETAINABLE_NOTIFICATION.TOGGLE, open)
  }

  const clear = () => {
    store.commit(RETAINABLE_NOTIFICATION.CLEAR)
  }

  return {
    items,
    count,
    panelOpen,
    push,
    dismiss,
    dismissByGroup,
    prune,
    hydrate,
    toggle,
    clear,
  }
}

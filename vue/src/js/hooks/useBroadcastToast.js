import { computed } from 'vue'
import { useStore } from 'vuex'
import { BROADCAST_TOAST } from '@/store/mutations'

/**
 * Stackable broadcast toasts (max 2 visible; FIFO queue for overflow).
 * Independent of ALERT.SET_ALERT used by forms/API.
 */
export default function useBroadcastToast () {
  const store = useStore()

  const visible = computed(() => store.state.broadcastToast?.visible ?? [])
  const queuedCount = computed(() => store.state.broadcastToast?.queue?.length ?? 0)

  const push = (payload) => {
    store.commit(BROADCAST_TOAST.PUSH, payload)
  }

  const dismiss = (id) => {
    store.commit(BROADCAST_TOAST.DISMISS, id)
  }

  const clear = () => {
    store.commit(BROADCAST_TOAST.CLEAR)
  }

  return {
    visible,
    queuedCount,
    push,
    dismiss,
    clear,
  }
}

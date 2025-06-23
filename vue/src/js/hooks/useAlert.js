// hooks/useAlert.js
import { useStore } from 'vuex'
import { ALERT } from '@/store/mutations'

export default function useAlert() {
  const store = useStore()

  const open = (payload) => {
    store.commit(ALERT.SET_ALERT, payload)
  }

  // const close = () => {
  //   store.commit(ALERT.SET_ALERT, null)
  // }

  return {
    openAlert: open,
  }
}
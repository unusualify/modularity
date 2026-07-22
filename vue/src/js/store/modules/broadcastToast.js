import { BROADCAST_TOAST } from '../mutations'
import {
  BROADCAST_TOAST_DEFAULT_TIMEOUT,
  BROADCAST_TOAST_MAX_VISIBLE,
  dequeueBroadcastToast,
  enqueueBroadcastToast,
} from '@/utils/broadcastToastQueue'

let nextId = 1

const state = () => ({
  visible: [],
  queue: [],
  maxVisible: BROADCAST_TOAST_MAX_VISIBLE,
})

const getters = {
  broadcastToastVisible: (moduleState) => moduleState.visible,
  broadcastToastQueuedCount: (moduleState) => moduleState.queue.length,
}

/**
 * Normalize toast payload: prefer title/description/detail; treat legacy `message` as description.
 *
 * @param {object} payload
 * @returns {{ title: string|null, description: string|null, detail: string|null, redirector: string|null, hasRedirector: boolean, redirectorText: string|null }|null}
 */
function normalizeToastContent (payload) {
  if (!payload || typeof payload !== 'object') {
    return null
  }

  const title = payload.title != null && String(payload.title).trim() !== ''
    ? String(payload.title)
    : null

  let description = payload.description != null && String(payload.description).trim() !== ''
    ? String(payload.description)
    : null

  if (!description && payload.message != null && String(payload.message).trim() !== '') {
    description = String(payload.message)
  }

  // Legacy: message-only payloads used message as the sole text — keep as description.
  // If only title is set, that is fine.
  if (!title && !description) {
    return null
  }

  const detail = payload.detail != null && String(payload.detail).trim() !== ''
    ? String(payload.detail)
    : null

  const redirector = payload.redirector != null && String(payload.redirector).trim() !== ''
    ? String(payload.redirector)
    : null
  const hasRedirector = (payload.hasRedirector === true || (!!redirector && payload.hasRedirector !== false))
    && !!redirector
  const redirectorText = payload.redirectorText != null && String(payload.redirectorText).trim() !== ''
    ? String(payload.redirectorText)
    : null

  return { title, description, detail, redirector, hasRedirector, redirectorText }
}

const mutations = {
  [BROADCAST_TOAST.PUSH] (moduleState, payload) {
    const content = normalizeToastContent(payload)
    if (!content) {
      return
    }

    const toast = {
      id: nextId++,
      title: content.title,
      description: content.description,
      detail: content.detail,
      // Keep message for any older consumers expecting it.
      message: content.description ?? content.title,
      redirector: content.redirector,
      hasRedirector: content.hasRedirector,
      redirectorText: content.redirectorText,
      variant: payload.variant ?? 'info',
      timeout: payload.timeout ?? BROADCAST_TOAST_DEFAULT_TIMEOUT,
      location: payload.location ?? 'top end',
    }

    const next = enqueueBroadcastToast(moduleState, toast, moduleState.maxVisible)
    moduleState.visible = next.visible
    moduleState.queue = next.queue
  },

  [BROADCAST_TOAST.DISMISS] (moduleState, id) {
    const next = dequeueBroadcastToast(moduleState, id, moduleState.maxVisible)
    moduleState.visible = next.visible
    moduleState.queue = next.queue
  },

  [BROADCAST_TOAST.CLEAR] (moduleState) {
    moduleState.visible = []
    moduleState.queue = []
  },
}

export default {
  state,
  getters,
  mutations,
}

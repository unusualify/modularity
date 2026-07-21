/**
 * FIFO queue helpers for stackable broadcast toasts.
 * At most `maxVisible` toasts are shown; overflow waits until a visible toast dismisses.
 */

export const BROADCAST_TOAST_MAX_VISIBLE = 4
export const BROADCAST_TOAST_DEFAULT_TIMEOUT = 5000

/**
 * @param {{ visible: object[], queue: object[] }} state
 * @param {object} toast
 * @param {number} [maxVisible]
 * @returns {{ visible: object[], queue: object[] }}
 */
export function enqueueBroadcastToast (state, toast, maxVisible = BROADCAST_TOAST_MAX_VISIBLE) {
  const visible = [...(state.visible || [])]
  const queue = [...(state.queue || [])]

  if (visible.length < maxVisible) {
    visible.push(toast)
  } else {
    queue.push(toast)
  }

  return { visible, queue }
}

/**
 * Remove a visible toast by id and promote the next queued toast (if any).
 *
 * @param {{ visible: object[], queue: object[] }} state
 * @param {number|string} id
 * @param {number} [maxVisible]
 * @returns {{ visible: object[], queue: object[] }}
 */
export function dequeueBroadcastToast (state, id, maxVisible = BROADCAST_TOAST_MAX_VISIBLE) {
  const previousVisible = state.visible || []
  const visible = previousVisible.filter((toast) => toast.id !== id)

  if (visible.length === previousVisible.length) {
    return {
      visible: [...previousVisible],
      queue: [...(state.queue || [])],
    }
  }

  const queue = [...(state.queue || [])]

  if (queue.length > 0 && visible.length < maxVisible) {
    visible.push(queue.shift())
  }

  return { visible, queue }
}

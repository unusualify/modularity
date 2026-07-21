import { describe, expect, test } from 'vitest'
import {
  BROADCAST_TOAST_MAX_VISIBLE,
  dequeueBroadcastToast,
  enqueueBroadcastToast,
} from '../../src/js/utils/broadcastToastQueue.js'
import { createStore } from 'vuex'
import broadcastToastModule from '../../src/js/store/modules/broadcastToast.js'
import { BROADCAST_TOAST } from '../../src/js/store/mutations/index.js'

function toast (id, message = `msg-${id}`) {
  return { id, message, variant: 'info', timeout: 5000 }
}

describe('broadcastToastQueue', () => {
  test('shows up to maxVisible and queues the rest FIFO', () => {
    let state = { visible: [], queue: [] }

    state = enqueueBroadcastToast(state, toast(1))
    state = enqueueBroadcastToast(state, toast(2))
    state = enqueueBroadcastToast(state, toast(3))
    state = enqueueBroadcastToast(state, toast(4))

    expect(state.visible.map((t) => t.id)).toEqual([1, 2])
    expect(state.queue.map((t) => t.id)).toEqual([3, 4])
    expect(BROADCAST_TOAST_MAX_VISIBLE).toBe(2)
  })

  test('promotes next queued toast when a visible one dismisses', () => {
    let state = {
      visible: [toast(1), toast(2)],
      queue: [toast(3), toast(4)],
    }

    state = dequeueBroadcastToast(state, 1)

    expect(state.visible.map((t) => t.id)).toEqual([2, 3])
    expect(state.queue.map((t) => t.id)).toEqual([4])
  })

  test('ignores dismiss for unknown id', () => {
    const state = {
      visible: [toast(1)],
      queue: [toast(2)],
    }

    const next = dequeueBroadcastToast(state, 99)

    expect(next.visible.map((t) => t.id)).toEqual([1])
    expect(next.queue.map((t) => t.id)).toEqual([2])
  })
})

describe('broadcastToast vuex module', () => {
  function createToastStore () {
    return createStore({
      modules: {
        broadcastToast: broadcastToastModule,
      },
    })
  }

  test('PUSH fills visible then queues overflow; DISMISS promotes FIFO', () => {
    const store = createToastStore()

    store.commit(BROADCAST_TOAST.PUSH, { message: 'a', variant: 'info' })
    store.commit(BROADCAST_TOAST.PUSH, { message: 'b', variant: 'success' })
    store.commit(BROADCAST_TOAST.PUSH, { message: 'c', variant: 'error' })

    expect(store.state.broadcastToast.visible.map((t) => t.description)).toEqual(['a', 'b'])
    expect(store.state.broadcastToast.queue.map((t) => t.description)).toEqual(['c'])

    const firstId = store.state.broadcastToast.visible[0].id
    store.commit(BROADCAST_TOAST.DISMISS, firstId)

    expect(store.state.broadcastToast.visible.map((t) => t.description)).toEqual(['b', 'c'])
    expect(store.state.broadcastToast.queue).toEqual([])
  })

  test('PUSH accepts title, description, and detail', () => {
    const store = createToastStore()

    store.commit(BROADCAST_TOAST.PUSH, {
      title: 'Cache warm',
      description: 'Warmed 3 records.',
      detail: 'Blog:Post',
      variant: 'success',
    })

    const [item] = store.state.broadcastToast.visible
    expect(item.title).toBe('Cache warm')
    expect(item.description).toBe('Warmed 3 records.')
    expect(item.detail).toBe('Blog:Post')
    expect(item.message).toBe('Warmed 3 records.')
    expect(item.variant).toBe('success')
  })

  test('PUSH treats legacy message as description', () => {
    const store = createToastStore()
    store.commit(BROADCAST_TOAST.PUSH, { message: 'Hello', variant: 'info' })

    const [item] = store.state.broadcastToast.visible
    expect(item.title).toBeNull()
    expect(item.description).toBe('Hello')
    expect(item.message).toBe('Hello')
  })

  test('PUSH ignores empty content', () => {
    const store = createToastStore()
    store.commit(BROADCAST_TOAST.PUSH, { message: '', variant: 'info' })
    store.commit(BROADCAST_TOAST.PUSH, { variant: 'info' })
    store.commit(BROADCAST_TOAST.PUSH, { title: '  ', description: '', detail: 'x' })
    expect(store.state.broadcastToast.visible).toEqual([])
    expect(store.state.broadcastToast.queue).toEqual([])
  })
})
